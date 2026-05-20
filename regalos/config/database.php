<?php

require_once __DIR__ . '/env.php';

load_env(__DIR__ . '/../.env');

function db_connect()
{
    $conn = new mysqli(
        env_value('DB_HOST', 'localhost'),
        env_value('DB_USERNAME', ''),
        env_value('DB_PASSWORD', ''),
        env_value('DB_DATABASE', ''),
        env_value('DB_PORT', 3306)
    );

    if ($conn->connect_error) {
        die('Error de conexión: ' . $conn->connect_error);
    }

    $conn->set_charset('utf8');

    return $conn;
}