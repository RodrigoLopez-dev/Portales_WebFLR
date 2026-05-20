<?php
require_once __DIR__ . '/FintocAPI.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$amount = isset($_POST['monto']) ? (int) $_POST['monto'] : 0;
$order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';

if ($amount <= 0 || $order_id === '') {
    http_response_code(400);
    exit('Datos de pago inválidos');
}

try {
    $fintocAPI = new FintocAPI();
    $result = $fintocAPI->generateWidgetToken($amount, $order_id);

    require_once __DIR__ . '/payment_widget.php';
} catch (Exception $e) {
    http_response_code(500);

    if (function_exists('env_value') && env_value('APP_DEBUG', 'false') === 'true') {
        exit('Error al iniciar pago Fintoc: ' . $e->getMessage());
    }

    exit('No fue posible iniciar el pago en este momento.');
}
?>