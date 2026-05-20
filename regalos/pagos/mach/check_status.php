<?php

session_start();
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

function build_app_url()
{
    $appBaseUrl = rtrim(env_value('APP_BASE_URL', ''), '/');
    $appName = trim(env_value('APP_NAME', ''), '/');

    if ($appBaseUrl === '') {
        return '';
    }

    return $appName !== '' ? $appBaseUrl . '/' . $appName : $appBaseUrl;
}

if (empty($_SESSION['token'])) {
    json_response(array(
        'ok' => false,
        'paid' => false,
        'message' => 'Token MACH no encontrado.'
    ), 400);
}

$token = trim($_SESSION['token']);
$appUrl = build_app_url();

if ($appUrl === '') {
    json_response(array(
        'ok' => false,
        'paid' => false,
        'message' => 'APP_BASE_URL no configurada.'
    ), 500);
}

$db = db_connect();

if (!$db || $db->connect_error) {
    json_response(array(
        'ok' => false,
        'paid' => false,
        'message' => 'Error de conexión BD.'
    ), 500);
}

$stmt = $db->prepare("
    SELECT 
        am.orden_compra,
        am.monto,
        am.estado_pago_id AS estado_mach,
        do.estado_pago_id AS estado_donacion
    FROM aporte_mach am
    LEFT JOIN donaciones_online do ON do.id = am.orden_compra
    WHERE am.token = ?
    LIMIT 1
");

if (!$stmt) {
    error_log('[MACH check_status] Error preparando SELECT: ' . $db->error);
    $db->close();

    json_response(array(
        'ok' => false,
        'paid' => false,
        'message' => 'Error consultando pago.'
    ), 500);
}

$stmt->bind_param('s', $token);

if (!$stmt->execute()) {
    error_log('[MACH check_status] Error ejecutando SELECT: ' . $stmt->error);
    $stmt->close();
    $db->close();

    json_response(array(
        'ok' => false,
        'paid' => false,
        'message' => 'Error consultando pago.'
    ), 500);
}

$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $stmt->close();
    $db->close();

    json_response(array(
        'ok' => false,
        'paid' => false,
        'message' => 'Pago MACH no encontrado.'
    ), 404);
}

$row = $result->fetch_assoc();

$stmt->close();
$db->close();

$ordenCompra = isset($row['orden_compra']) ? trim($row['orden_compra']) : '';
$monto = isset($row['monto']) ? (int) $row['monto'] : 0;
$estadoMach = isset($row['estado_mach']) ? (int) $row['estado_mach'] : 0;
$estadoDonacion = isset($row['estado_donacion']) ? (int) $row['estado_donacion'] : 0;

$paid = ($estadoMach === 1 || $estadoDonacion === 1);

if (!$paid) {
    json_response(array(
        'ok' => true,
        'paid' => false,
        'estado_mach' => $estadoMach,
        'estado_donacion' => $estadoDonacion
    ));
}

json_response(array(
    'ok' => true,
    'paid' => true,
    'estado_mach' => $estadoMach,
    'estado_donacion' => $estadoDonacion,
    'redirect_url' => $appUrl . '/pagos/exito.php?id=' . urlencode($ordenCompra) .
        '&monto=' . urlencode($monto) .
        '&medio_pago=' . urlencode('Mach')
));