<?php
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\RequestController;

require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Helpers/env.php';

load_env(__DIR__ . '/../.env');

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

$app = require __DIR__ . '/../config/app.php';
date_default_timezone_set($app['timezone']);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? null) == 443);

    session_name($app['session_name']);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$routes = require __DIR__ . '/../config/routes.php';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$route = route_path($path, $app['base_path']);

if (isset($routes[$method][$route])) {
    [$controllerName, $action] = $routes[$method][$route];
    $controllerClass = 'App\\Controllers\\' . $controllerName;
    $controller = new $controllerClass();
    $controller->$action();
    exit;
}

http_response_code(404);
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Ruta no encontrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="alert alert-danger">
        <h1 class="h4 mb-2">404 - Ruta no encontrada</h1>
        <p class="mb-0">La ruta solicitada no existe en el sistema.</p>
    </div>
</div>
</body>
</html>
