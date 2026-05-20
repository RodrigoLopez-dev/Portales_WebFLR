<?php
// api/config.php

function env_cfg($key, $default = null) {
    $value = isset($_ENV[$key]) ? $_ENV[$key] : getenv($key);

    if ($value === false || $value === null || $value === '') {
        if ($default !== null) {
            return $default;
        }
        throw new Exception('Falta variable de entorno: ' . $key);
    }

    return $value;
}

function env_cfg_int($key, $default) {
    $value = isset($_ENV[$key]) ? $_ENV[$key] : getenv($key);

    if ($value === false || $value === null || $value === '') {
        return (int)$default;
    }

    return (int)$value;
}

function env_cfg_bool($key, $default) {
    $value = isset($_ENV[$key]) ? $_ENV[$key] : getenv($key);

    if ($value === false || $value === null || $value === '') {
        return (bool)$default;
    }

    $value = strtolower(trim((string)$value));
    return in_array($value, array('1', 'true', 'yes', 'on'), true);
}

function is_local_env() {
    $env = strtolower(trim(env_cfg('APP_ENV', 'local')));
    return in_array($env, array('local', 'dev', 'development'), true);
}

define('APP_ENV', env_cfg('APP_ENV', 'local'));
define('APP_DEBUG', env_cfg_bool('APP_DEBUG', is_local_env()));

define('DB_HOST', env_cfg('DB_HOST', 'localhost'));
define('DB_NAME', env_cfg('DB_NAME', 'portal_de_oraciones'));
define('DB_USER', env_cfg('DB_USER', 'root'));
define('DB_PASS', env_cfg('DB_PASS', ''));
define('DB_CHARSET', env_cfg('DB_CHARSET', 'utf8mb4'));

define('APP_URL', env_cfg('APP_URL'));

define('CORS_ORIGIN', env_cfg(
    'CORS_ORIGIN',
    APP_ENV === 'local' ? '*' : ''
));

define('RATE_LIMIT_PER_MIN', env_cfg_int('RATE_LIMIT_PER_MIN', 20));
define('CANDLE_HOURS', env_cfg_int('CANDLE_HOURS', 48));

define('ENC_KEY_B64', env_cfg('ENC_KEY_B64'));
define('ENC_METHOD', env_cfg('ENC_METHOD', 'AES-256-CBC'));

define('SMTP_HOST', env_cfg('SMTP_HOST', 'mail.flrosas.cl'));
define('SMTP_PORT', env_cfg_int('SMTP_PORT', 587));
define('SMTP_USER', env_cfg('SMTP_USER'));
define('SMTP_PASS', env_cfg('SMTP_PASS'));
define('MAIL_FROM_EMAIL', env_cfg('MAIL_FROM_EMAIL', SMTP_USER));
define('MAIL_FROM_NAME', env_cfg('MAIL_FROM_NAME', 'Portal de Oraciones'));
define('MAIL_ADMIN_TO', env_cfg('MAIL_ADMIN_TO'));
