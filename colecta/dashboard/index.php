<?php
require_once __DIR__ . '/../conexion/configuracion.php';

$appBaseUrl = getenv('APP_BASE_URL');
$appName = getenv('APP_NAME');

if (!$appBaseUrl) {
    die('Falta APP_BASE_URL en .env');
}

if (!$appName) {
    die('Falta APP_NAME en .env');
}

$appUrl = rtrim($appBaseUrl, '/') . '/' . trim($appName, '/');

header('Location: ' . $appUrl . '/dashboard/login/');
exit;