<?php

require_once __DIR__ . '/../config/env.php';

load_env(__DIR__ . '/../.env');

function app_url()
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

function base_url($path = '')
{
    $base = rtrim(app_url(), '/');

    if ($path === '') {
        return $base;
    }

    return $base . '/' . ltrim($path, '/');
}

function redirect($path = '')
{
    header('Location: ' . base_url($path));
    exit;
}

function asset($path)
{
    return base_url($path);
}