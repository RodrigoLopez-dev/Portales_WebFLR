<?php
// api/db.php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/config.php';

function db()
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ));

        return $pdo;
    } catch (PDOException $e) {
        error_log('[DB] Connection failed: ' . $e->getMessage());
        throw new Exception('Database connection error.');
    }
}