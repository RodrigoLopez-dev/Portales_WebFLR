<?php

date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/env.php';

load_env(__DIR__ . '/../../.env');

header('Content-Type: application/json; charset=utf-8');

function json_response($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

$orderId = isset($_GET['id']) ? trim($_GET['id']) : '';

if ($orderId === '') {
    json_response(array(
        'ok' => false,
        'message' => 'ID de donación no recibido.'
    ), 400);
}

$db = db_connect();

if (!$db || $db->connect_error) {
    json_response(array(
        'ok' => false,
        'message' => 'Error de conexión BD.'
    ), 500);
}

$stmt = $db->prepare("
    SELECT estado_pago_id
    FROM donaciones_online
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    error_log('[Fintoc check_status] Error preparando SELECT: ' . $db->error);
    $db->close();

    json_response(array(
        'ok' => false,
        'message' => 'Error consultando donación.'
    ), 500);
}

$stmt->bind_param('s', $orderId);

if (!$stmt->execute()) {
    error_log('[Fintoc check_status] Error ejecutando SELECT: ' . $stmt->error);
    $stmt->close();
    $db->close();

    json_response(array(
        'ok' => false,
        'message' => 'Error consultando donación.'
    ), 500);
}

$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $stmt->close();
    $db->close();

    json_response(array(
        'ok' => false,
        'message' => 'Donación no encontrada.'
    ), 404);
}

$row = $result->fetch_assoc();

$stmt->close();
$db->close();

$estadoPagoId = isset($row['estado_pago_id']) ? (int) $row['estado_pago_id'] : 0;

json_response(array(
    'ok' => true,
    'estado_pago_id' => $estadoPagoId,
    'paid' => $estadoPagoId === 1,
    'failed' => $estadoPagoId === 3,
    'pending' => $estadoPagoId === 2
));