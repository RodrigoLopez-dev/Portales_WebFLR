<?php
// api/helpers.php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (!function_exists('random_bytes')) {
    function random_bytes($length)
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $strong = false;
            $bytes = openssl_random_pseudo_bytes($length, $strong);

            if ($bytes !== false && $strong === true) {
                return $bytes;
            }
        }

        throw new Exception('No hay generador seguro disponible');
    }
}

if (!function_exists('hash_equals')) {
    function hash_equals($known_string, $user_string)
    {
        if (!is_string($known_string) || !is_string($user_string)) {
            return false;
        }

        $known_len = strlen($known_string);
        $user_len = strlen($user_string);

        if ($known_len !== $user_len) {
            return false;
        }

        $result = 0;
        for ($i = 0; $i < $known_len; $i++) {
            $result |= ord($known_string[$i]) ^ ord($user_string[$i]);
        }

        return $result === 0;
    }
}

function str_len_safe($value)
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function str_sub_safe($value, $start, $length)
{
    return function_exists('mb_substr')
        ? mb_substr($value, $start, $length, 'UTF-8')
        : substr($value, $start, $length);
}

function str_upper_safe($value)
{
    return function_exists('mb_strtoupper')
        ? mb_strtoupper($value, 'UTF-8')
        : strtoupper($value);
}

function current_origin()
{
    return isset($_SERVER['HTTP_ORIGIN']) ? trim($_SERVER['HTTP_ORIGIN']) : '';
}

function is_allowed_origin($origin)
{
    if ($origin === '') {
        return false;
    }

    if (CORS_ORIGIN === '*') {
        return true;
    }

    return $origin === CORS_ORIGIN;
}

function send_cors_headers()
{
    $origin = current_origin();

    if (CORS_ORIGIN === '*') {
        header('Access-Control-Allow-Origin: *');
    } elseif ($origin !== '' && is_allowed_origin($origin)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
    }
}

function json_response($data, $status)
{
    http_response_code((int) $status);
    header('Content-Type: application/json; charset=utf-8');
    send_cors_headers();
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function handle_preflight()
{
    send_cors_headers();
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 600');

    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function json_input()
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        json_response(array('ok' => false, 'error' => 'Invalid JSON'), 400);
    }

    return $data;
}

function clean_string($value, $maxLen)
{
    $value = is_string($value) ? trim($value) : '';
    $value = strip_tags($value);
    $value = preg_replace('/\s+/u', ' ', $value);

    if ($value === null) {
        $value = '';
    }

    return str_sub_safe($value, 0, (int) $maxLen);
}

function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function initials($name)
{
    $name = clean_string($name, 120);
    if ($name === '') {
        return '??';
    }

    $parts = preg_split('/\s+/u', $name);
    if (!is_array($parts)) {
        $parts = array();
    }

    $first = isset($parts[0]) ? str_sub_safe($parts[0], 0, 1) : '';
    $second = isset($parts[1]) ? str_sub_safe($parts[1], 0, 1) : '';

    $result = str_upper_safe($first . $second);
    return $result !== '' ? $result : '??';
}

function uuid_v4()
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function secure_token($bytes)
{
    return bin2hex(random_bytes((int) $bytes));
}

function client_ip()
{
    return isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'], 0, 45) : 'unknown';
}

function rate_limit($key)
{
    $ip = client_ip();
    $bucket = date('YmdHi');
    $file = sys_get_temp_dir() . '/flr_rl_' . md5($key . '|' . $ip . '|' . $bucket);

    $count = file_exists($file) ? (int) @file_get_contents($file) : 0;
    $count++;

    @file_put_contents($file, (string) $count, LOCK_EX);

    if ($count > RATE_LIMIT_PER_MIN) {
        json_response(array('ok' => false, 'error' => 'Too many requests'), 429);
    }
}

function enc_key()
{
    $raw = base64_decode(ENC_KEY_B64, true);

    if ($raw === false || strlen($raw) !== 32) {
        throw new Exception('ENC_KEY_B64 invalida');
    }

    return $raw;
}

function encrypt_field($plain)
{
    if ($plain === null || $plain === '') {
        return null;
    }

    $key = enc_key();
    $ivLen = openssl_cipher_iv_length(ENC_METHOD);

    if ($ivLen === false || $ivLen <= 0) {
        throw new Exception('Encryption method not supported');
    }

    $iv = random_bytes($ivLen);
    $cipher = openssl_encrypt($plain, ENC_METHOD, $key, OPENSSL_RAW_DATA, $iv);

    if ($cipher === false) {
        throw new Exception('Encryption failed');
    }

    $mac = hash_hmac('sha256', $iv . $cipher, $key, true);

    return base64_encode($iv . $mac . $cipher);
}

function decrypt_field($encoded)
{
    if ($encoded === null || $encoded === '') {
        return null;
    }

    $raw = base64_decode($encoded, true);
    if ($raw === false) {
        return null;
    }

    $key = enc_key();
    $ivLen = openssl_cipher_iv_length(ENC_METHOD);
    $macLen = 32;

    if ($ivLen === false || $ivLen <= 0) {
        return null;
    }

    if (strlen($raw) <= ($ivLen + $macLen)) {
        return null;
    }

    $iv = substr($raw, 0, $ivLen);
    $mac = substr($raw, $ivLen, $macLen);
    $cipher = substr($raw, $ivLen + $macLen);

    $calcMac = hash_hmac('sha256', $iv . $cipher, $key, true);
    if (!hash_equals($mac, $calcMac)) {
        return null;
    }

    $plain = openssl_decrypt($cipher, ENC_METHOD, $key, OPENSSL_RAW_DATA, $iv);

    return $plain === false ? null : $plain;
}

function log_event($level, $context, $message)
{
    try {
        $pdo = db();

        $stmt = $pdo->prepare("
            INSERT INTO logs (created_at, level, endpoint, ip, message)
            VALUES (NOW(), :level, :endpoint, :ip, :message)
        ");

        $stmt->execute(array(
            ':level' => clean_string($level, 20),
            ':endpoint' => clean_string($context, 100),
            ':ip' => client_ip(),
            ':message' => clean_string($message, 1000),
        ));
    } catch (Exception $e) {
        json_response(array(
            'ok' => false,
            'error' => $e->getMessage()
        ), 500);
    }
}