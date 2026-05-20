<?php

date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/../../config/env.php';

load_env(__DIR__ . '/../../.env');

require_once __DIR__ . '/../../conexion/configuracion.php';

function fintoc_get_header_value($name)
{
    $headers = function_exists('getallheaders') ? getallheaders() : array();

    foreach ($headers as $key => $value) {
        if (strtolower($key) === strtolower($name)) {
            return trim((string) $value);
        }
    }

    $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

    if (isset($_SERVER[$serverKey])) {
        return trim((string) $_SERVER[$serverKey]);
    }

    return '';
}

function fintoc_parse_signature_header($signatureHeader)
{
    $result = array();

    $parts = explode(',', $signatureHeader);

    foreach ($parts as $part) {
        $pair = explode('=', trim($part), 2);

        if (count($pair) === 2) {
            $result[trim($pair[0])] = trim($pair[1]);
        }
    }

    return $result;
}

function fintoc_validate_signature($payload, $signatureHeader, $secret)
{
    $secret = trim((string) $secret);
    $signatureHeader = trim((string) $signatureHeader);

    if ($secret === '' || $signatureHeader === '') {
        return false;
    }

    $signatureParts = fintoc_parse_signature_header($signatureHeader);

    if (
        !isset($signatureParts['t']) ||
        !isset($signatureParts['v1']) ||
        trim($signatureParts['t']) === '' ||
        trim($signatureParts['v1']) === ''
    ) {
        return false;
    }

    $timestamp = trim($signatureParts['t']);
    $receivedSignature = trim($signatureParts['v1']);

    $signedPayload = $timestamp . '.' . $payload;
    $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);

    return hash_equals($expectedSignature, $receivedSignature);
}

$payload = file_get_contents('php://input');

$webhookSecret = env_value('FINTOC_WEBHOOK_SECRET', env_value('FINTOC_WEBHOOK_TOKEN', ''));
$signatureHeader = fintoc_get_header_value('Fintoc-Signature');

if (trim($webhookSecret) !== '') {
    if (!fintoc_validate_signature($payload, $signatureHeader, $webhookSecret)) {
        error_log('Fintoc webhook: firma inválida o ausente.');
        http_response_code(401);
        exit;
    }
} else {
    error_log('Fintoc webhook: validación de firma omitida porque FINTOC_WEBHOOK_SECRET no está configurado.');
}
$data = json_decode($payload, true);

if (!is_array($data)) {
    error_log('Fintoc webhook inválido: ' . $payload);
    http_response_code(400);
    exit;
}

$id = isset($data['id']) ? $data['id'] : '';
$type = isset($data['type']) ? $data['type'] : '';
$createdAtRaw = isset($data['created_at']) ? $data['created_at'] : '';
$createdAt = $createdAtRaw ? date('Y-m-d H:i:s', strtotime($createdAtRaw)) : date('Y-m-d H:i:s');

$paymentId = isset($data['data']['id']) ? $data['data']['id'] : '';
$holderId = isset($data['data']['recipient_account']['holder_id']) ? $data['data']['recipient_account']['holder_id'] : '';
$accountNumber = isset($data['data']['recipient_account']['number']) ? $data['data']['recipient_account']['number'] : '';
$accountType = isset($data['data']['recipient_account']['type']) ? $data['data']['recipient_account']['type'] : '';
$institutionId = isset($data['data']['recipient_account']['institution_id']) ? $data['data']['recipient_account']['institution_id'] : '';

$amount = isset($data['data']['amount']) ? (float) $data['data']['amount'] : 0;
$currency = isset($data['data']['currency']) ? $data['data']['currency'] : '';
$status = isset($data['data']['status']) ? $data['data']['status'] : '';
$referenceId = isset($data['data']['reference_id']) ? $data['data']['reference_id'] : '';
$orderId = isset($data['data']['metadata']['order_id']) ? trim((string) $data['data']['metadata']['order_id']) : '';
$currentPortal = trim(env_value('APP_NAME', ''), '/');
$eventPortal = isset($data['data']['metadata']['portal'])
    ? trim((string) $data['data']['metadata']['portal'])
    : '';

if ($currentPortal !== '') {
    if ($eventPortal === '') {
        error_log('Fintoc webhook ignorado: evento sin metadata.portal. order_id: ' . $orderId);
        http_response_code(200);
        echo 'IGNORED';
        exit;
    }

    if (!hash_equals($currentPortal, $eventPortal)) {
        error_log(
            'Fintoc webhook ignorado: portal no corresponde. actual=' .
            $currentPortal .
            ' evento=' .
            $eventPortal .
            ' order_id=' .
            $orderId
        );

        http_response_code(200);
        echo 'IGNORED';
        exit;
    }
}

if ($id === '' || $paymentId === '' || $orderId === '') {
    error_log('Fintoc webhook incompleto: ' . $payload);
    http_response_code(400);
    exit;
}

$stmt = $db->prepare("
    INSERT INTO donaciones_fintoc (
        id,
        type,
        created_at,
        payment_id,
        holder_id,
        account_number,
        account_type,
        institution_id,
        amount,
        currency,
        status,
        reference_id,
        order_id
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        type = VALUES(type),
        created_at = VALUES(created_at),
        payment_id = VALUES(payment_id),
        holder_id = VALUES(holder_id),
        account_number = VALUES(account_number),
        account_type = VALUES(account_type),
        institution_id = VALUES(institution_id),
        amount = VALUES(amount),
        currency = VALUES(currency),
        status = VALUES(status),
        reference_id = VALUES(reference_id),
        order_id = VALUES(order_id)
");

if (!$stmt) {
    error_log('ERROR prepare donaciones_fintoc: ' . $db->error);
    http_response_code(500);
    exit;
}

$stmt->bind_param(
    'ssssssssdssss',
    $id,
    $type,
    $createdAt,
    $paymentId,
    $holderId,
    $accountNumber,
    $accountType,
    $institutionId,
    $amount,
    $currency,
    $status,
    $referenceId,
    $orderId
);

if (!$stmt->execute()) {
    error_log('ERROR insert/update donaciones_fintoc: ' . $stmt->error);
    $stmt->close();
    http_response_code(500);
    exit;
}

$stmt->close();

$estadoPago = null;

if ($status === 'succeeded') {
    $estadoPago = 1;
} elseif ($status === 'failed' || $status === 'rejected' || $status === 'expired') {
    $estadoPago = 3;
}

if ($estadoPago !== null) {
    $orderIdForUpdate = (int) $orderId;

    $stmtUpdate = $db->prepare("
        UPDATE donaciones
        SET estado_id = ?
        WHERE id = ?
    ");

    if ($stmtUpdate) {
        $stmtUpdate->bind_param('ii', $estadoPago, $orderIdForUpdate);

        if (!$stmtUpdate->execute()) {
            error_log('ERROR update donaciones Fintoc: ' . $stmtUpdate->error);
        }

        if ($stmtUpdate->affected_rows === 0) {
            error_log('Fintoc webhook: no se actualizó donación. order_id: ' . $orderId . ' status: ' . $status);
        }

        $stmtUpdate->close();
    } else {
        error_log('ERROR prepare update donaciones Fintoc: ' . $db->error);
    }
}

http_response_code(200);
echo 'OK';
exit;