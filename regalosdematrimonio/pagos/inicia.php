<?php

require_once __DIR__ . '/../config/env.php';
load_env(__DIR__ . '/../.env');

if (env_value('APP_DEBUG', 'false') === 'true') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
}

require_once __DIR__ . '/../php/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$payment = isset($_POST['payment']) ? trim($_POST['payment']) : '';
$monto = isset($_POST['monto']) ? (int) $_POST['monto'] : 0;

if ($monto <= 0 || !in_array($payment, array('1', '2', '3'), true)) {
    http_response_code(400);
    exit('Datos de pago inválidos');
}

$data = array(
    'rut' => isset($_POST['rut']) ? trim($_POST['rut']) : '',
    'nombre' => isset($_POST['nombre']) ? trim($_POST['nombre']) : '',
    'telefono' => isset($_POST['telefono']) ? trim($_POST['telefono']) : '',
    'email' => isset($_POST['email']) ? trim($_POST['email']) : '',
    'apellido' => isset($_POST['apellido']) ? trim($_POST['apellido']) : '',
    'ip_transaccion' => isset($_POST['ip_transaccion']) ? trim($_POST['ip_transaccion']) : '',
    'ip_ciudad' => isset($_POST['ip_ciudad']) ? trim($_POST['ip_ciudad']) : '',
    'ip_region' => isset($_POST['ip_region']) ? trim($_POST['ip_region']) : '',
    'ip_pais' => isset($_POST['ip_pais']) ? trim($_POST['ip_pais']) : '',
    'ip_latitud' => isset($_POST['ip_latitud']) ? trim($_POST['ip_latitud']) : '',
    'ip_longitud' => isset($_POST['ip_longitud']) ? trim($_POST['ip_longitud']) : '',
    'utm_source' => isset($_POST['utm_source']) ? trim($_POST['utm_source']) : '',
    'utm_medium' => isset($_POST['utm_medium']) ? trim($_POST['utm_medium']) : '',
    'utm_campaign' => isset($_POST['utm_campaign']) ? trim($_POST['utm_campaign']) : '',
    'monto' => $monto,
    'medio_pago_id' => $payment,
    'estado_pago_id' => 2
);

$order_id = guardarDonacion($data);

if (!$order_id) {
    http_response_code(500);
    exit('No fue posible registrar la donación');
}

$nextPages = array(
    '1' => 'webpay/pagos.php',
    '2' => 'fintoc/pagos.php',
    '3' => 'mach/pagos.php'
);

$next_page = $nextPages[$data['medio_pago_id']];

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

echo '<form name="pagos" id="pagos" action="' . h($next_page) . '" method="post">
    <input type="hidden" name="monto" value="' . h($data['monto']) . '">
    <input type="hidden" name="nombre" value="' . h($data['nombre']) . '">
    <input type="hidden" name="email" value="' . h($data['email']) . '">
    <input type="hidden" name="order_id" value="' . h($order_id) . '">
</form>
<script>document.getElementById("pagos").submit();</script>';
?>