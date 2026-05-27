<?php

$appBaseUrl = rtrim(env_value('APP_BASE_URL', ''), '/');
$appName = trim(env_value('APP_NAME', 'project-manager'), '/');

$appPath = $appName !== '' ? '/' . $appName : '';
$appUrl = $appBaseUrl . $appPath;

return [
    'app_name' => $appName,
    'base_url' => $appBaseUrl,
    'base_path' => $appPath,
    'app_url' => $appUrl,
    'timezone' => env_value('APP_TIMEZONE', 'America/Santiago'),
    'session_name' => env_value('APP_SESSION_NAME', 'gp_session'),
    'environment' => env_value('APP_ENV', 'production'),
    'debug' => filter_var(env_value('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
];