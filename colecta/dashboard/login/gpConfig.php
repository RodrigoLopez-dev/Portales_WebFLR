<?php
session_start();

require_once __DIR__ . '/../../conexion/configuracion.php';

include_once __DIR__ . '/src/Google_Client.php';
include_once __DIR__ . '/src/contrib/Google_Oauth2Service.php';

$clientId = getenv('GOOGLE_CLIENT_ID');
$clientSecret = getenv('GOOGLE_CLIENT_SECRET');

$appBaseUrl = getenv('APP_BASE_URL');
$appName = getenv('APP_NAME');

if (!$clientId || !$clientSecret) {
    die('Faltan credenciales de Google en .env');
}

if (!$appBaseUrl) {
    die('Falta APP_BASE_URL en .env');
}

if (!$appName) {
    die('Falta APP_NAME en .env');
}

$appUrl = rtrim($appBaseUrl, '/') . '/' . trim($appName, '/');

$redirectURL = $appUrl . '/dashboard/login/';
error_log('GOOGLE REDIRECT URL: ' . $redirectURL);

$gClient = new Google_Client();
$gClient->setApplicationName('Dashboard Colecta FLR');
$gClient->setClientId($clientId);
$gClient->setClientSecret($clientSecret);
$gClient->setRedirectUri($redirectURL);

$google_oauthV2 = new Google_Oauth2Service($gClient);
?>