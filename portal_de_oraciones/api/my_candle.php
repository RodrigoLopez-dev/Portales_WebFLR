<?php

require_once __DIR__ . '/helpers.php';

handle_preflight();
rate_limit('my_candle');

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(array('ok' => false, 'error' => 'Method not allowed'), 405);
}

$body = json_input();

$id = clean_string(isset($body['id']) ? $body['id'] : '', 80);
/* $token = clean_string(isset($body['owner_token']) ? $body['owner_token'] : '', 300); */
$owner_token = clean_string(isset($body['owner_token']) ? $body['owner_token'] : '', 300);
$share_token = clean_string(isset($body['share_token']) ? $body['share_token'] : '', 300);

if ($id === '' || ($owner_token === '' && $share_token === '')) {
    json_response(array('ok' => false, 'error' => 'Invalid request'), 400);
}

if (!preg_match('/^[a-f0-9\-]{36}$/i', $id)) {
    json_response(array('ok' => false, 'error' => 'Invalid request'), 400);
}

if ($owner_token !== '' && !preg_match('/^[A-Fa-f0-9]{32,128}$/', $owner_token)) {
    json_response(array('ok' => false, 'error' => 'Invalid request'), 400);
}

if ($share_token !== '' && !preg_match('/^[A-Fa-f0-9]{32,128}$/', $share_token)) {
    json_response(array('ok' => false, 'error' => 'Invalid request'), 400);
}

$owner_token_hash = $owner_token !== '' ? hash('sha256', $owner_token) : '';
$share_token_hash = $share_token !== '' ? hash('sha256', $share_token) : '';

try {
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT *
        FROM candles
        WHERE id = :id
          AND expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute(array(':id' => $id));
    $c = $stmt->fetch();

    if (!$c) {
        json_response(array('ok' => false, 'error' => 'Not found'), 404);
    }

    $is_owner = (
        $owner_token_hash !== '' &&
        isset($c['owner_token_hash']) &&
        hash_equals((string)$c['owner_token_hash'], $owner_token_hash)
    );

    $is_shared = (
        $share_token_hash !== '' &&
        isset($c['share_token_hash']) &&
        hash_equals((string)$c['share_token_hash'], $share_token_hash)
    );

    if (!$is_owner && !$is_shared) {
        json_response(array('ok' => false, 'error' => 'Not found'), 404);
    }

    $request = decrypt_field($c['request_enc']);

    if ($request === null) {
        log_event('ERROR', 'my_candle', 'decrypt request failed id=' . $id);

        json_response(array(
            'ok' => false,
            'error' => 'Could not read candle data'
        ), 500);
    }

    $baseCandle = array(
        'id' => isset($c['id']) ? (string)$c['id'] : '',
        'initials' => isset($c['initials']) ? clean_string($c['initials'], 10) : '??',
        'publicDate' => isset($c['public_date']) ? (string)$c['public_date'] : '',
        'createdAt' => !empty($c['created_at']) ? date('c', strtotime($c['created_at'])) : null,
        'expiresAt' => !empty($c['expires_at']) ? date('c', strtotime($c['expires_at'])) : null
    );

    if ($is_owner) {
        $name = decrypt_field($c['name_enc']);
        $email = decrypt_field($c['email_enc']);
        $phone = !empty($c['phone_enc']) ? decrypt_field($c['phone_enc']) : '';

        if ($name === null || $email === null) {
            log_event('ERROR', 'my_candle', 'decrypt owner fields failed id=' . $id);

            json_response(array(
                'ok' => false,
                'error' => 'Could not read candle data'
            ), 500);
        }

        $baseCandle['private'] = array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone !== null ? $phone : '',
            'request' => $request
        );

        json_response(array(
            'ok' => true,
            'type' => 'owner',
            'candle' => $baseCandle
        ), 200);
    }

    $baseCandle['shared'] = array(
        'request' => $request
    );

    json_response(array(
        'ok' => true,
        'type' => 'shared',
        'candle' => $baseCandle
    ), 200);

} catch (Exception $e) {
    log_event('ERROR', 'my_candle', $e->getMessage());
    json_response(array('ok' => false, 'error' => 'Server error'), 500);
}