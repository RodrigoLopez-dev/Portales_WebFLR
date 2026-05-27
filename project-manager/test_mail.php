<?php
declare(strict_types=1);

/**
 * test_mail.php
 *
 * Prueba independiente de envío SMTP para Project Manager.
 *
 * Uso recomendado:
 * 1) Copiar este archivo en la raíz del proyecto, junto a /config y /phpmailer.
 * 2) Editar $testTo con el correo de destino.
 * 3) Si el servidor NO tiene variable SMTP_PASS, colocar temporalmente la clave en $overrideSmtpPass.
 * 4) Abrir en navegador: https://tudominio.cl/test_mail.php?to=jfabia@flrosas.cl
 * 5) Eliminar este archivo al terminar la prueba.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

$startedAt = date('Y-m-d H:i:s');

// === CONFIGURACIÓN DE PRUEBA ===
// Puedes pasar destino por URL: test_mail.php?to=jfabia@flrosas.cl
$testTo = $_GET['to'] ?? 'jfabia@flrosas.cl';

// Opcional: si SMTP_PASS no está disponible como variable de entorno,
// coloca temporalmente aquí la clave real para aislar el problema.
// IMPORTANTE: borrar la clave y eliminar este archivo después de probar.
$overrideSmtpPass = 'gVTIJ=@Ni^}S';

// Activa debug SMTP detallado en pantalla y error_log.
$debugSmtp = true;

// === CARGA DE CONFIGURACIÓN DEL PROYECTO ===
$baseDir = __DIR__;
$configFile = $baseDir . '/config/mail.php';
$mailLibFile = $baseDir . '/lib/mail_flr.php';

header('Content-Type: text/html; charset=UTF-8');

echo '<!doctype html><html lang="es"><head><meta charset="UTF-8"><title>Test Mail Project Manager</title>';
echo '<style>body{font-family:Arial,sans-serif;margin:30px;line-height:1.4} pre{background:#f4f4f4;padding:12px;border-radius:6px;overflow:auto}.ok{color:#087a20}.bad{color:#b00020}.warn{color:#9a6700}</style>';
echo '</head><body>';
echo '<h1>Test de correo SMTP - Project Manager</h1>';
echo '<p><strong>Inicio:</strong> ' . htmlspecialchars($startedAt) . '</p>';

if (!file_exists($configFile)) {
    echo '<p class="bad"><strong>Error:</strong> No existe config/mail.php. Debes copiar este archivo en la raíz del proyecto.</p>';
    echo '<p>Ruta buscada: <code>' . htmlspecialchars($configFile) . '</code></p></body></html>';
    exit;
}

if (!file_exists($mailLibFile)) {
    echo '<p class="bad"><strong>Error:</strong> No existe lib/mail_flr.php. Debes copiar este archivo en la raíz del proyecto.</p>';
    echo '<p>Ruta buscada: <code>' . htmlspecialchars($mailLibFile) . '</code></p></body></html>';
    exit;
}

$mailConfig = require $configFile;

if (!is_array($mailConfig)) {
    echo '<p class="bad"><strong>Error:</strong> config/mail.php no retornó un array válido.</p></body></html>';
    exit;
}

// Sobrescritura controlada de clave SMTP para pruebas.
if ($overrideSmtpPass !== '') {
    $mailConfig['smtp_pass'] = $overrideSmtpPass;
}

// Normaliza configuración esperada.
$mailConfig['smtp_host'] = $mailConfig['smtp_host'] ?? 'mail.flrosas.cl';
$mailConfig['smtp_port'] = (int)($mailConfig['smtp_port'] ?? 587);
$mailConfig['smtp_user'] = $mailConfig['smtp_user'] ?? 'noresponder@flrosas.cl';
$mailConfig['smtp_pass'] = $mailConfig['smtp_pass'] ?? '';
$mailConfig['from_email'] = $mailConfig['from_email'] ?? $mailConfig['smtp_user'];
$mailConfig['from_name'] = $mailConfig['from_name'] ?? 'Gestor de Proyectos';
$mailConfig['reply_to'] = $mailConfig['reply_to'] ?? 'info@flrosas.cl';
$mailConfig['reply_name'] = $mailConfig['reply_name'] ?? 'Gestor de Proyectos';

$enabled = (bool)($mailConfig['enabled'] ?? false);

// En este test se permite enviar aunque enabled esté false, pero se informa claramente.
echo '<h2>Configuración detectada</h2>';
echo '<pre>';
echo 'enabled       : ' . ($enabled ? 'true' : 'false') . "\n";
echo 'smtp_host    : ' . htmlspecialchars((string)$mailConfig['smtp_host']) . "\n";
echo 'smtp_port    : ' . htmlspecialchars((string)$mailConfig['smtp_port']) . "\n";
echo 'smtp_user    : ' . htmlspecialchars((string)$mailConfig['smtp_user']) . "\n";
echo 'smtp_pass    : ' . (($mailConfig['smtp_pass'] !== '') ? '[DEFINIDA]' : '[VACÍA]') . "\n";
echo 'from_email   : ' . htmlspecialchars((string)$mailConfig['from_email']) . "\n";
echo 'from_name    : ' . htmlspecialchars((string)$mailConfig['from_name']) . "\n";
echo 'reply_to     : ' . htmlspecialchars((string)$mailConfig['reply_to']) . "\n";
echo 'bcc          : ' . htmlspecialchars((string)($mailConfig['bcc'] ?? '')) . "\n";
echo 'destino test : ' . htmlspecialchars((string)$testTo) . "\n";
echo '</pre>';

if (!$enabled) {
    echo '<p class="warn"><strong>Advertencia:</strong> mail.enabled está en false. El proyecto no enviará correos desde NotificationService mientras siga así. Este test intentará enviar igualmente para validar SMTP.</p>';
}

if (!filter_var($testTo, FILTER_VALIDATE_EMAIL)) {
    echo '<p class="bad"><strong>Error:</strong> El correo destino no es válido: ' . htmlspecialchars((string)$testTo) . '</p></body></html>';
    exit;
}

if ($mailConfig['smtp_pass'] === '') {
    echo '<p class="bad"><strong>Error:</strong> SMTP_PASS está vacío. Define la variable de entorno SMTP_PASS o coloca temporalmente la clave en <code>$overrideSmtpPass</code>.</p>';
    echo '</body></html>';
    exit;
}

require_once $mailLibFile;

if (!function_exists('flr_send_mail')) {
    echo '<p class="bad"><strong>Error:</strong> No existe la función flr_send_mail() después de cargar lib/mail_flr.php.</p></body></html>';
    exit;
}

// Para obtener debug SMTP detallado sin modificar lib/mail_flr.php, se hace una prueba directa con PHPMailer.
use PHPMailer\PHPMailer\PHPMailer;

$phpmailerBase = $baseDir . '/phpmailer/src';
require_once $phpmailerBase . '/Exception.php';
require_once $phpmailerBase . '/PHPMailer.php';
require_once $phpmailerBase . '/SMTP.php';

$subject = 'Prueba SMTP Project Manager - ' . date('Y-m-d H:i:s');
$htmlBody = '
    <h2>Prueba de correo Project Manager</h2>
    <p>Este correo fue generado desde <strong>test_mail.php</strong>.</p>
    <p><strong>Fecha servidor:</strong> ' . htmlspecialchars(date('Y-m-d H:i:s')) . '</p>
    <p><strong>Servidor:</strong> ' . htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'CLI') . '</p>
';

$debugLines = [];

try {
    $mail = new PHPMailer(true);

    $mail->SMTPDebug = $debugSmtp ? 2 : 0;
    $mail->Debugoutput = static function (string $str, int $level) use (&$debugLines): void {
        $line = '[' . $level . '] ' . trim($str);
        $debugLines[] = $line;
        error_log('TEST_MAIL_SMTP ' . $line);
    };

    $mail->isSMTP();
    $mail->Host = (string)$mailConfig['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = (string)$mailConfig['smtp_user'];
    $mail->Password = (string)$mailConfig['smtp_pass'];
    $mail->Port = (int)$mailConfig['smtp_port'];
    $mail->SMTPSecure = ((int)$mailConfig['smtp_port'] === 465)
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;

    $mail->CharSet = 'UTF-8';
    $mail->setFrom((string)$mailConfig['from_email'], (string)$mailConfig['from_name']);
    $mail->addReplyTo((string)$mailConfig['reply_to'], (string)$mailConfig['reply_name']);
    $mail->addAddress((string)$testTo);

    if (!empty($mailConfig['bcc']) && filter_var((string)$mailConfig['bcc'], FILTER_VALIDATE_EMAIL)) {
        $mail->addBCC((string)$mailConfig['bcc']);
    }

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $htmlBody;
    $mail->AltBody = strip_tags($htmlBody);

    $sent = $mail->send();

    if ($sent) {
        echo '<h2 class="ok">Resultado: correo enviado correctamente</h2>';
        echo '<p>Destino: <strong>' . htmlspecialchars((string)$testTo) . '</strong></p>';
        echo '<p>Asunto: <strong>' . htmlspecialchars($subject) . '</strong></p>';
        error_log('TEST_MAIL ok=1 to=' . $testTo);
    } else {
        echo '<h2 class="bad">Resultado: PHPMailer no confirmó el envío</h2>';
        error_log('TEST_MAIL ok=0 no_exception to=' . $testTo);
    }
} catch (Throwable $e) {
    echo '<h2 class="bad">Resultado: error al enviar correo</h2>';
    echo '<p><strong>Mensaje:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    error_log('TEST_MAIL ok=0 err=' . $e->getMessage());
}

echo '<h2>Debug SMTP</h2>';
if ($debugLines) {
    echo '<pre>' . htmlspecialchars(implode("\n", $debugLines)) . '</pre>';
} else {
    echo '<p>No hubo salida de debug SMTP.</p>';
}

echo '<hr>';
echo '<p class="warn"><strong>Seguridad:</strong> elimina <code>test_mail.php</code> del servidor cuando termines la prueba.</p>';
echo '</body></html>';
