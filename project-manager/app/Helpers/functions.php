<?php
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['_csrf'] ?? '';
        if (empty($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], $token)) {
            die('Token CSRF inválido.');
        }
    }
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function base_url(string $path = ''): string
{
    static $app = null;

    if ($app === null) {
        $app = require __DIR__ . '/../../config/app.php';
    }

    $base = rtrim((string) ($app['app_url'] ?? ''), '/');
    $path = trim($path, '/');

    if ($path === '') {
        return $base;
    }

    return $base . '/' . $path;
}

function asset_url(string $path): string
{
    return base_url('public/' . ltrim($path, '/'));
}

function route_path(string $uriPath, string $basePath): string
{
    $uriPath = parse_url($uriPath, PHP_URL_PATH);
    $uriPath = '/' . ltrim((string) $uriPath, '/');

    $basePath = '/' . trim($basePath, '/');
    $basePath = $basePath === '/' ? '' : $basePath;

    if ($basePath !== '' && strpos($uriPath, $basePath) === 0) {
        $uriPath = substr($uriPath, strlen($basePath));
    }

    $uriPath = '/' . ltrim($uriPath, '/');

    return $uriPath === '/' ? '/' : rtrim($uriPath, '/');
}

function status_badge_class(string $status): string
{
    $status = mb_strtolower($status, 'UTF-8');

    switch ($status) {
        case 'ingresada':
            return 'secondary';

        case 'en revisión':
        case 'en analisis':
        case 'en análisis funcional':
        case 'en evaluación técnica':
            return 'info';

        case 'aprobada':
        case 'planificada':
            return 'primary';

        case 'en desarrollo':
            return 'warning';

        case 'en pruebas':
            return 'dark';

        case 'observada':
        case 'bloqueada':
            return 'danger';

        case 'implementada':
        case 'cerrada':
            return 'success';

        default:
            return 'secondary';
    }
}

function old(string $key, $default = ''): string
{
    return e($_POST[$key] ?? $default);
}

function selected($value, $current): string
{
    return (string) $value === (string) $current ? 'selected' : '';
}

function checked($value, $current): string
{
    return (string) $value === (string) $current ? 'checked' : '';
}

function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}