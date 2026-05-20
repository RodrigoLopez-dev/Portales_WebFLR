<?php

require_once __DIR__ . '/helpers.php';

handle_preflight();
rate_limit('list_candles');

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(array('ok' => false, 'error' => 'Method not allowed'), 405);
}

try {
    $pdo = db();

    $stmt = $pdo->query("
        SELECT id, initials, public_date, created_at, expires_at
        FROM candles
        WHERE expires_at > NOW()
        ORDER BY created_at DESC
        LIMIT 500
    ");

    $rows = $stmt->fetchAll();

    $candles = array();

    foreach ($rows as $row) {
        $candles[] = array(
            'id' => isset($row['id']) ? (string)$row['id'] : '',
            'initials' => isset($row['initials']) ? clean_string($row['initials'], 10) : '??',
            'publicDate' => isset($row['public_date']) ? (string)$row['public_date'] : '',
            'createdAt' => !empty($row['created_at']) ? date('c', strtotime($row['created_at'])) : null,
            'expiresAt' => !empty($row['expires_at']) ? date('c', strtotime($row['expires_at'])) : null
        );
    }

    json_response(array(
        'ok' => true,
        'candles' => $candles
    ), 200);

} catch (Exception $e) {
    log_event('ERROR', 'list_candles', $e->getMessage());
    json_response(array('ok' => false, 'error' => 'Server error'), 500);
}