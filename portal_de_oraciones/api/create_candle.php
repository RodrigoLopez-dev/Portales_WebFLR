<?php

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../lib/mail_flr.php';
require_once __DIR__ . '/db.php';

function get_runtime_setting($pdo, $key, $defaultValue)
{
  $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = :key LIMIT 1");
  $stmt->execute(array(':key' => $key));
  $row = $stmt->fetch();

  if ($row && isset($row['value']) && $row['value'] !== '') {
    return (int) $row['value'];
  }

  return (int) $defaultValue;
}

handle_preflight();
rate_limit('create_candle');

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_response(array('ok' => false, 'error' => 'Method not allowed'), 405);
}

$body = json_input();

$name = clean_string(isset($body['name']) ? $body['name'] : '', 120);
$email = clean_string(isset($body['email']) ? $body['email'] : '', 200);
$phone = clean_string(isset($body['phone']) ? $body['phone'] : '', 50);
$request = clean_string(isset($body['request']) ? $body['request'] : '', 5000);

if ($name === '' || $email === '' || $request === '') {
  json_response(array('ok' => false, 'error' => 'Missing required fields'), 400);
}

if (str_len_safe($name) < 2) {
  json_response(array('ok' => false, 'error' => 'Invalid name'), 400);
}

if (!validate_email($email)) {
  json_response(array('ok' => false, 'error' => 'Invalid email'), 400);
}

if ($phone !== '' && !preg_match('/^[0-9+\-\s()]{6,50}$/', $phone)) {
  json_response(array('ok' => false, 'error' => 'Invalid phone'), 400);
}

$id = uuid_v4();
$owner_token = secure_token(32);
$owner_token_hash = hash('sha256', $owner_token);
$share_token = secure_token(32);
$share_token_hash = hash('sha256', $share_token);

$created = new DateTime('now');
$publicInitials = initials($name);
$publicDate = $created->format('d/m/Y');

try {
  $pdo = db();

  $candleHours = get_runtime_setting($pdo, 'candle_hours', CANDLE_HOURS);

  $submitFingerprint = sha1(
    strtolower(trim($email)) . '|' .
    trim($name) . '|' .
    trim($request) . '|' .
    client_ip()
  );

  $dupStmt = $pdo->prepare("
        SELECT id
        FROM candles
        WHERE submit_fingerprint = :fp
          AND created_at >= DATE_SUB(NOW(), INTERVAL 20 SECOND)
        LIMIT 1
    ");
  $dupStmt->execute(array(
    ':fp' => $submitFingerprint
  ));
  $existing = $dupStmt->fetch();

  if ($existing && isset($existing['id']) && $existing['id'] !== '') {
    log_event('WARN', 'create_candle', 'duplicate blocked existing_id=' . $existing['id']);

    json_response(array(
      'ok' => false,
      'error' => 'La solicitud ya fue enviada o se está procesando. Espera unos segundos.'
    ), 409);
  }

  $expires = clone $created;
  $expires->modify('+' . $candleHours . ' hours');

  $stmt = $pdo->prepare("
        INSERT INTO candles
        (id, initials, public_date, created_at, expires_at,
         email_enc, phone_enc, request_enc, name_enc,
         owner_token_hash, share_token_hash, ip, user_agent, submit_fingerprint)
        VALUES
        (:id, :initials, :public_date, :created_at, :expires_at,
         :email_enc, :phone_enc, :request_enc, :name_enc,
         :owner_token_hash, :share_token_hash, :ip, :ua, :submit_fingerprint)
    ");

  $stmt->execute(array(
    ':id' => $id,
    ':initials' => $publicInitials,
    ':public_date' => $publicDate,
    ':created_at' => $created->format('Y-m-d H:i:s'),
    ':expires_at' => $expires->format('Y-m-d H:i:s'),
    ':email_enc' => encrypt_field($email),
    ':phone_enc' => $phone !== '' ? encrypt_field($phone) : null,
    ':request_enc' => encrypt_field($request),
    ':name_enc' => encrypt_field($name),
    ':owner_token_hash' => $owner_token_hash,
    ':share_token_hash' => $share_token_hash,
    ':ip' => client_ip(),
    ':ua' => substr(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '', 0, 255),
    ':submit_fingerprint' => $submitFingerprint
  ));

  log_event('INFO', 'create_candle', 'created id=' . $id . ' candle_hours=' . $candleHours);

  $mail_sent = false;

  try {
    $admin_html =
      '<h3>Nueva vela encendida</h3>' .
      '<p><b>Nombre:</b> ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</p>' .
      '<p><b>Email:</b> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>' .
      '<p><b>Teléfono:</b> ' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '</p>' .
      '<p><b>Petición:</b><br>' . nl2br(htmlspecialchars($request, ENT_QUOTES, 'UTF-8')) . '</p>' .
      '<p><b>ID:</b> ' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '</p>' .
      '<p><b>Fecha:</b> ' . htmlspecialchars($publicDate, ENT_QUOTES, 'UTF-8') . '</p>' .
      '<p><b>Duración:</b> ' . (int) $candleHours . ' horas</p>';

    $admin_mail_sent = flr_send_mail(array(
      'to' => MAIL_ADMIN_TO,
      'subject' => 'Nueva vela encendida',
      'html' => $admin_html,
      'alt' => 'Nueva vela encendida',
      'smtp_host' => SMTP_HOST,
      'smtp_port' => SMTP_PORT,
      'smtp_user' => SMTP_USER,
      'smtp_pass' => SMTP_PASS,
      'from_email' => MAIL_FROM_EMAIL,
      'from_name' => MAIL_FROM_NAME,
      'reply_to' => $email,
      'reply_name' => $name
    ));

    log_event('INFO', 'mail_admin', $admin_mail_sent ? 'ok' : 'fail');

    $private_url = APP_URL . '/velas.php?candle=' . urlencode($id) . '&owner_token=' . urlencode($owner_token);

    $user_html =
      "<div style='font-family:Arial,sans-serif; line-height:1.6; color:#333;'>" .
      "<h2 style='color:#af0a3d;'>Tu vela fue encendida 🙏</h2>" .
      "<p>Hola <b>" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "</b>,</p>" .
      "<p>Tu oración ha sido recibida correctamente.</p>" .
      "<p><b>Fecha:</b> " . htmlspecialchars($publicDate, ENT_QUOTES, 'UTF-8') . "</p>" .
      "<p><b>Duración:</b> " . (int) $candleHours . " horas</p>" .
      "<p style='margin-top:18px;'>Puedes ver el detalle completo de tu solicitud en el siguiente enlace:</p>" .
      "<p style='margin:18px 0;'>" .
      "<a href='" . htmlspecialchars($private_url, ENT_QUOTES, 'UTF-8') . "' " .
      "style='display:inline-block; padding:12px 18px; background:#c89b3c; color:#111; text-decoration:none; border-radius:8px; font-weight:bold;'>" .
      "Ver mi vela" .
      "</a>" .
      "</p>" .
      "<p style='font-size:13px; color:#666; word-break:break-all;'>" .
      "Si el botón no funciona, copia y pega este enlace en tu navegador:<br>" .
      htmlspecialchars($private_url, ENT_QUOTES, 'UTF-8') .
      "</p>" .
      "<br><p>Gracias por confiar en Fundación Las Rosas.</p>" .
      "</div>";

    $mail_sent = flr_send_mail(array(
      'to' => $email,
      'subject' => 'Tu vela fue encendida',
      'html' => $user_html,
      'alt' => 'Tu vela fue encendida',
      'smtp_host' => SMTP_HOST,
      'smtp_port' => SMTP_PORT,
      'smtp_user' => SMTP_USER,
      'smtp_pass' => SMTP_PASS,
      'from_email' => MAIL_FROM_EMAIL,
      'from_name' => 'Fundación Las Rosas',
      'reply_to' => 'info@flrosas.cl'
    ));

    log_event('INFO', 'mail_user', $mail_sent ? 'ok' : 'fail');
  } catch (Exception $eMail) {
    log_event('ERROR', 'mail', $eMail->getMessage());
  }

  json_response(array(
    'ok' => true,
    'candle' => array(
      'id' => $id,
      'initials' => $publicInitials,
      'publicDate' => $publicDate,
      'createdAt' => $created->format(DateTime::ATOM),
      'expiresAt' => $expires->format(DateTime::ATOM)
    ),
    'owner_token' => $owner_token,
    'share_token' => $share_token,
    'mail_sent' => $mail_sent,
    'mail_message' => $mail_sent
      ? 'Se envió el correo correctamente.'
      : 'La vela fue creada, pero el correo no se pudo enviar.'
  ), 200);

} catch (Exception $e) {
  log_event('ERROR', 'create_candle', $e->getMessage());

  json_response(array(
    'ok' => false,
    'error' => 'Internal server error'
  ), 500);
}