<?php

date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/env.php';

load_env(__DIR__ . '/../../.env');

function mach_log($message)
{
    error_log('[MACH webhook] ' . $message);
}

function respond($code, $message)
{
    http_response_code($code);
    echo $message;
    exit;
}

function get_request_headers_safe()
{
    if (function_exists('getallheaders')) {
        return getallheaders();
    }

    $headers = array();

    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) === 'HTTP_') {
            $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
            $headers[$name] = $value;
        }
    }

    if (isset($_SERVER['CONTENT_TYPE'])) {
        $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
    }

    if (isset($_SERVER['CONTENT_LENGTH'])) {
        $headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
    }

    return $headers;
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

function validate_webhook_token()
{
    $expectedToken = env_value('MACH_WEBHOOK_TOKEN', '');

    if ($expectedToken === '') {
        mach_log('MACH_WEBHOOK_TOKEN no configurado.');
        respond(500, 'Webhook no configurado');
    }

    $headers = get_request_headers_safe();

    $authorization = get_header_value($headers, 'Authorization');
    $xWebhookToken = get_header_value($headers, 'x-webhook-token');
    $xApiKey = get_header_value($headers, 'x-api-key');

    $authorizationToken = trim((string) $authorization);

    if (stripos($authorizationToken, 'Bearer ') === 0) {
        $authorizationToken = trim(substr($authorizationToken, 7));
    }

    $validAuthorization = $authorizationToken !== '' && hash_equals($expectedToken, $authorizationToken);
    $validWebhookToken = $xWebhookToken === $expectedToken;
    $validApiKey = $xApiKey === $expectedToken;

    if (!$validAuthorization && !$validWebhookToken && !$validApiKey) {
        mach_log('Token webhook inválido.');

        $debugPayload = file_get_contents('php://input');

        respond(401, 'Unauthorized');
    }
}

function mach_event_to_status($eventName)
{
    if ($eventName === 'business-payment-completed') {
        return 1;
    }

    if (
        $eventName === 'business-payment-expired' ||
        $eventName === 'business-payment-failed' ||
        $eventName === 'business-payment-reversed'
    ) {
        return 3;
    }

    return null;
}

validate_webhook_token();

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

if (json_last_error() !== JSON_ERROR_NONE || !$data || !is_array($data)) {
    mach_log('JSON inválido: ' . $payload);
    respond(400, 'JSON inválido');
}

$eventName = isset($data['event_name']) ? trim($data['event_name']) : '';
$eventResourceId = isset($data['event_resource_id']) ? trim($data['event_resource_id']) : '';

if ($eventName === '' || $eventResourceId === '') {
    mach_log('Parámetros incompletos: ' . $payload);
    respond(400, 'Parámetros incompletos');
}

$newStatus = mach_event_to_status($eventName);

if ($newStatus === null) {
    mach_log('Evento ignorado: ' . $eventName);
    respond(200, 'Evento ignorado');
}

$db = db_connect();

if (!$db || $db->connect_error) {
    mach_log('Conexión BD fallida: ' . ($db ? $db->connect_error : 'db_connect retornó null'));
    respond(500, 'Error interno');
}

$selectStmt = $db->prepare("
    SELECT orden_compra, estado_pago_id
    FROM aporte_mach
    WHERE token = ?
    LIMIT 1
");

if (!$selectStmt) {
    mach_log('Error preparando SELECT aporte_mach: ' . $db->error);
    respond(500, 'Error interno');
}

$selectStmt->bind_param('s', $eventResourceId);

if (!$selectStmt->execute()) {
    mach_log('Error ejecutando SELECT aporte_mach: ' . $selectStmt->error);
    $selectStmt->close();
    respond(500, 'Error interno');
}

$result = $selectStmt->get_result();

if (!$result || $result->num_rows === 0) {
    mach_log('No se encontró orden_compra para token: ' . $eventResourceId);
    $selectStmt->close();
    respond(404, 'No se encontró orden_compra');
}

$row = $result->fetch_assoc();

$ordenCompra = isset($row['orden_compra']) ? trim($row['orden_compra']) : '';

$selectStmt->close();

if ($ordenCompra === '') {
    mach_log('orden_compra vacío para token: ' . $eventResourceId);
    respond(500, 'orden_compra vacío');
}

$updateAporteStmt = $db->prepare("
    UPDATE aporte_mach
    SET estado_pago_id = ?
    WHERE token = ?
    AND estado_pago_id <> ?
");

if (!$updateAporteStmt) {
    mach_log('Error preparando UPDATE aporte_mach: ' . $db->error);
    respond(500, 'Error interno');
}

$updateAporteStmt->bind_param('isi', $newStatus, $eventResourceId, $newStatus);

if (!$updateAporteStmt->execute()) {
    mach_log('Error ejecutando UPDATE aporte_mach: ' . $updateAporteStmt->error);
    $updateAporteStmt->close();
    respond(500, 'Error interno');
}

$updateAporteStmt->close();

$updateDonacionStmt = $db->prepare("
    UPDATE donaciones_online
    SET estado_pago_id = ?
    WHERE id = ?
    AND estado_pago_id <> ?
");

if (!$updateDonacionStmt) {
    mach_log('Error preparando UPDATE donaciones_online: ' . $db->error);
    respond(500, 'Error interno');
}

$updateDonacionStmt->bind_param('isi', $newStatus, $ordenCompra, $newStatus);

if (!$updateDonacionStmt->execute()) {
    mach_log('Error ejecutando UPDATE donaciones_online: ' . $updateDonacionStmt->error);
    $updateDonacionStmt->close();
    respond(500, 'Error interno');
}

$donacionActualizada = $updateDonacionStmt->affected_rows > 0;

$updateDonacionStmt->close();

if ($newStatus === 1 && $donacionActualizada) {
    require_once __DIR__ . '/../../php/enviar_correo.php';

    $correoEnviado = enviarCorreoAgradecimiento($ordenCompra, $ordenCompra, 'Mach');

    if (!$correoEnviado) {
        mach_log('Pago aprobado, pero no se pudo enviar correo. Orden: ' . $ordenCompra);
    }
}

$db->close();

respond(200, 'Webhook procesado correctamente');