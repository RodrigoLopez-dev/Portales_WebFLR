<?php

require_once __DIR__ . '/helpers.php';

handle_preflight();
rate_limit('count_candles');

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(array('ok' => false, 'error' => 'Method not allowed'), 405);
}

try {
    $pdo = db();

    $stmt = $pdo->query("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN expires_at > NOW() THEN 1 ELSE 0 END) AS active
    FROM candles
");

    $row = $stmt->fetch();

    json_response(array(
        'ok' => true,
        'count' => (int) (isset($row['active']) ? $row['active'] : 0), // mantenemos compatibilidad
        'total' => (int) (isset($row['total']) ? $row['total'] : 0),
    ), 200);

} catch (Exception $e) {
    log_event('ERROR', 'count_candles', $e->getMessage());
    json_response(array('ok' => false, 'error' => 'Server error'), 500);
}