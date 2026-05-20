<?php

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/FintocAPI.php';

load_env(__DIR__ . '/../../.env');

function fintoc_app_url()
{
    $baseUrl = rtrim(env_value('APP_BASE_URL', ''), '/');
    $appName = trim(env_value('APP_NAME', ''), '/');

    if ($baseUrl === '') {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $baseUrl = $scheme . '://' . $host;
    }

    if ($appName !== '') {
        return $baseUrl . '/' . $appName;
    }

    return $baseUrl;
}

function fintoc_normalize_amount($value)
{
    $value = trim((string) $value);
    $value = str_replace(array('$', '.', ',', ' '), '', $value);

    return (int) $value;
}

$appUrl = fintoc_app_url();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $appUrl);
    exit;
}

$amount = isset($_POST['monto']) ? fintoc_normalize_amount($_POST['monto']) : 0;

$orderId = '';

if (isset($_POST['order_id'])) {
    $orderId = trim((string) $_POST['order_id']);
} elseif (isset($_POST['id'])) {
    $orderId = trim((string) $_POST['id']);
} elseif (isset($_POST['cod'])) {
    $orderId = trim((string) $_POST['cod']);
}

$utmSource = isset($_POST['utm_source']) ? trim((string) $_POST['utm_source']) : '';
$utmMedium = isset($_POST['utm_medium']) ? trim((string) $_POST['utm_medium']) : '';
$utmCampaign = isset($_POST['utm_campaign']) ? trim((string) $_POST['utm_campaign']) : '';

if ($amount <= 0 || $orderId === '') {
    error_log('Fintoc pagos.php: monto/orderId inválido. amount=' . $amount . ' orderId=' . $orderId);
    header('Location: ' . $appUrl);
    exit;
}

$fintocAPI = new FintocAPI();
$result = $fintocAPI->generateWidgetToken($amount, $orderId);

if (!is_array($result) || empty($result['widget_token'])) {
    error_log('Fintoc pagos.php: no se pudo generar widget_token. orderId=' . $orderId);
    header('Location: ' . $appUrl);
    exit;
}

require_once __DIR__ . '/payment_widget.php';