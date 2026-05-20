<?php

require_once __DIR__ . '/../config/env.php';

load_env(__DIR__ . '/../.env');


// ===== DB CONFIG =====
$dbHost = env_value('DB_HOST', 'localhost');
$dbUsername = env_value('DB_USER', '');
$dbPassword = env_value('DB_PASS', '');
$dbName = env_value('DB_NAME', '');
$dbCharset = env_value('DB_CHARSET', 'utf8');

$db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

if ($db->connect_error) {
    die("No hay Conexion con la base de datos: " . $db->connect_error);
}

$db->set_charset($dbCharset);