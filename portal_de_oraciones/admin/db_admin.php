<?php
require_once __DIR__ . '/../api/bootstrap.php';

function admin_env_required($key)
{
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }

    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }

    throw new Exception('Falta variable de entorno: ' . $key);
}

function admin_db()
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dbHost = admin_env_required('DB_HOST');
    $dbName = admin_env_required('DB_NAME');
    $dbUser = admin_env_required('DB_USER');
    $dbPass = admin_env_required('DB_PASS');

    $dsn = 'mysql:host=' . $dbHost . ';dbname=' . $dbName . ';charset=utf8mb4';

    $pdo = new PDO($dsn, $dbUser, $dbPass, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ));

    return $pdo;
}