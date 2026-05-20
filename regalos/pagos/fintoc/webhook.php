<?php

date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/env.php';

load_env(__DIR__ . '/../../.env');

function fintoc_log($message)
{
    error_log('[Fintoc webhook] ' . $message);
}

function respond($code, $message)
{
    http_response_code($code);
    echo $message;
    exit;
}

function get_headers_safe()
{
    return function_exists('getallheaders') ? getallheaders() : array();
}

function get_header_value($headers, $wantedName)
{
    foreach ($headers as $name => $value) {
        if (strtolower($name) === strtolower($wantedName)) {
            return trim($value);
        }
    }

    return '';
}

function validate_webhook_signature($payload)
{
    $secret = env_value('FINTOC_WEBHOOK_SECRET', env_value('FINTOC_WEBHOOK_TOKEN', ''));

    if ($secret === '') {
        fintoc_log('FINTOC_WEBHOOK_SECRET / FINTOC_WEBHOOK_TOKEN no configurado.');
        respond(500, 'Webhook no configurado');
    }

    $headers = get_headers_safe();
    $signatureHeader = get_header_value($headers, 'Fintoc-Signature');

    if ($signatureHeader === '') {
        fintoc_log('Fintoc-Signature no recibido.');
        respond(401, 'Unauthorized');
    }

    $timestamp = '';
    $signature = '';

    $parts = explode(',', $signatureHeader);

    foreach ($parts as $part) {
        $kv = explode('=', trim($part), 2);

        if (count($kv) !== 2) {
            continue;
        }

        if ($kv[0] === 't') {
            $timestamp = $kv[1];
        }

        if ($kv[0] === 'v1') {
            $signature = $kv[1];
        }
    }

    if ($timestamp === '' || $signature === '') {
        fintoc_log('Fintoc-Signature inválido: ' . $signatureHeader);
        respond(401, 'Unauthorized');
    }

    $signedPayload = $timestamp . '.' . $payload;
    $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);

    if (!hash_equals($expectedSignature, $signature)) {
        fintoc_log('Firma Fintoc inválida.');
        respond(401, 'Unauthorized');
    }
}

function fintoc_event_to_status($type, $status)
{
    if ($type === 'payment_intent.succeeded' || $status === 'succeeded') {
        return 1;
    }

    if (
        $type === 'payment_intent.failed' ||
        $type === 'payment_intent.rejected' ||
        $type === 'payment_intent.expired' ||
        $status === 'failed' ||
        $status === 'rejected' ||
        $status === 'expired'
    ) {
        return 3;
    }

    return null;
}

$payload = file_get_contents('php://input');

validate_webhook_signature($payload);
$data = json_decode($payload, true);

if (json_last_error() !== JSON_ERROR_NONE || !$data || !is_array($data)) {
    fintoc_log('JSON inválido: ' . $payload);
    respond(400, 'JSON inválido');
}

$id = isset($data['id']) ? trim($data['id']) : '';
$type = isset($data['type']) ? trim($data['type']) : '';
$createdAtRaw = isset($data['created_at']) ? $data['created_at'] : '';
$created_at = $createdAtRaw ? date('Y-m-d H:i:s', strtotime($createdAtRaw)) : date('Y-m-d H:i:s');

$paymentData = isset($data['data']) && is_array($data['data']) ? $data['data'] : array();
$recipientAccount = isset($paymentData['recipient_account']) && is_array($paymentData['recipient_account']) ? $paymentData['recipient_account'] : array();
$metadata = isset($paymentData['metadata']) && is_array($paymentData['metadata']) ? $paymentData['metadata'] : array();

$payment_id = isset($paymentData['id']) ? trim($paymentData['id']) : '';
$holder_id = isset($recipientAccount['holder_id']) ? trim($recipientAccount['holder_id']) : '';
$account_number = isset($recipientAccount['number']) ? trim($recipientAccount['number']) : '';
$account_type = isset($recipientAccount['type']) ? trim($recipientAccount['type']) : '';
$institution_id = isset($recipientAccount['institution_id']) ? trim($recipientAccount['institution_id']) : '';
$amount = isset($paymentData['amount']) ? (int) $paymentData['amount'] : 0;
$currency = isset($paymentData['currency']) ? trim($paymentData['currency']) : '';
$status = isset($paymentData['status']) ? trim($paymentData['status']) : '';
$reference_id = isset($paymentData['reference_id']) ? trim($paymentData['reference_id']) : '';
$order_id = isset($metadata['order_id']) ? trim((string) $metadata['order_id']) : '';

$portalEvento = isset($metadata['portal']) ? trim((string) $metadata['portal']) : '';
$origen = isset($metadata['origen']) ? trim((string) $metadata['origen']) : '';

if ($portalEvento === '' && $origen !== '') {
    $portalEvento = $origen;
}

$portalActual = trim(env_value('APP_NAME', ''), '/');

if ($portalActual !== '') {
    if ($portalEvento === '') {
        fintoc_log('Evento ignorado: metadata.portal vacío. order_id=' . $order_id . ' evento=' . $id);
        respond(200, 'Evento ignorado');
    }

    if (!hash_equals($portalActual, $portalEvento)) {
        fintoc_log(
            'Evento ignorado: portal no corresponde. actual=' .
            $portalActual .
            ' evento=' .
            $portalEvento .
            ' order_id=' .
            $order_id .
            ' fintoc_event_id=' .
            $id
        );

        respond(200, 'Evento ignorado');
    }
}

if ($id === '' || $type === '') {
    fintoc_log('Evento incompleto: ' . $payload);
    respond(400, 'Evento incompleto');
}

$newStatus = fintoc_event_to_status($type, $status);

if ($newStatus === null) {
    fintoc_log('Evento ignorado: ' . $type . ' status: ' . $status);
    respond(200, 'Evento ignorado');
}

$db = db_connect();

if (!$db || $db->connect_error) {
    fintoc_log('Conexión BD fallida: ' . ($db ? $db->connect_error : 'db_connect retornó null'));
    respond(500, 'Error interno');
}

$sql = "
    INSERT INTO aporte_fintoc 
    (
        id,
        type,
        created_at,
        payment_id,
        holder_id,
        account_number,
        account_type,
        institution_id,
        amount,
        currency,
        status,
        reference_id,
        order_id
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        type = VALUES(type),
        created_at = VALUES(created_at),
        payment_id = VALUES(payment_id),
        holder_id = VALUES(holder_id),
        account_number = VALUES(account_number),
        account_type = VALUES(account_type),
        institution_id = VALUES(institution_id),
        amount = VALUES(amount),
        currency = VALUES(currency),
        status = VALUES(status),
        reference_id = VALUES(reference_id),
        order_id = VALUES(order_id)
";

$stmt = $db->prepare($sql);

if (!$stmt) {
    fintoc_log('Error preparando INSERT aporte_fintoc: ' . $db->error);
    $db->close();
    respond(500, 'Error interno');
}

$stmt->bind_param(
    'ssssssssissss',
    $id,
    $type,
    $created_at,
    $payment_id,
    $holder_id,
    $account_number,
    $account_type,
    $institution_id,
    $amount,
    $currency,
    $status,
    $reference_id,
    $order_id
);

if (!$stmt->execute()) {
    fintoc_log('Error ejecutando INSERT aporte_fintoc: ' . $stmt->error);
    $stmt->close();
    $db->close();
    respond(500, 'Error interno');
}

$stmt->close();

if ($order_id === '') {
    fintoc_log('Webhook sin order_id. Evento: ' . $id . ' type: ' . $type);
    $db->close();
    respond(200, 'Evento registrado sin order_id');
}

$updateStmt = $db->prepare("
    UPDATE donaciones_online
    SET estado_pago_id = ?
    WHERE id = ?
    AND estado_pago_id <> ?
");

if (!$updateStmt) {
    fintoc_log('Error preparando UPDATE donaciones_online: ' . $db->error);
    $db->close();
    respond(500, 'Error interno');
}

fintoc_log('ANTES UPDATE donaciones_online | order_id=' . $order_id . ' | newStatus=' . $newStatus);

$updateStmt->bind_param('isi', $newStatus, $order_id, $newStatus);


if (!$updateStmt->execute()) {
    fintoc_log('Error actualizando donaciones_online: ' . $updateStmt->error);
    $updateStmt->close();
    $db->close();
    respond(500, 'Error interno');
}

fintoc_log('DESPUES UPDATE donaciones_online | affected_rows=' . $updateStmt->affected_rows . ' | error=' . $updateStmt->error);

fintoc_log(
    'UPDATE donaciones_online ejecutado. order_id: '
    . $order_id
    . ' newStatus: '
    . $newStatus
    . ' affected_rows: '
    . $updateStmt->affected_rows
);

$donacionActualizada = $updateStmt->affected_rows > 0;

$updateStmt->close();

if ($newStatus === 1 && $donacionActualizada) {
    require_once __DIR__ . '/../../php/enviar_correo.php';

    $correoEnviado = enviarCorreoAgradecimiento($order_id, $payment_id, 'Fintoc');

    if (!$correoEnviado) {
        fintoc_log('Pago aprobado, pero no se pudo enviar correo. Orden: ' . $order_id);
    }
}

if (!$donacionActualizada) {
    fintoc_log('Webhook procesado sin cambio de estado. order_id: ' . $order_id . ' status: ' . $status . ' origen: ' . $origen);
}

$db->close();

respond(200, 'OK');