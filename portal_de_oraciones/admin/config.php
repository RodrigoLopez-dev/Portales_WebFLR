<?php
require_once __DIR__ . '/../api/bootstrap.php';

function admin_env($key, $default = '')
{
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }

    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }

    return $default;
}

define('ADMIN_GOOGLE_CLIENT_ID', admin_env('GOOGLE_CLIENT_ID', ''));