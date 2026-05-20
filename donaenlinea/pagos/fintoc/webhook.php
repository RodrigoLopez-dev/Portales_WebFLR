<?php

date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/../../config/database.php';

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

if (!$data || !is_array($data)) {
    error_log('Webhook Fintoc inválido: JSON no válido');
    http_response_code(400);
    exit;
}

$id = isset($data['id']) ? $data['id'] : '';
$type = isset($data['type']) ? $data['type'] : '';
$createdAtRaw = isset($data['created_at']) ? $data['created_at'] : '';
$created_at = $createdAtRaw ? date('Y-m-d H:i:s', strtotime($createdAtRaw)) : date('Y-m-d H:i:s');

$paymentData = isset($data['data']) && is_array($data['data']) ? $data['data'] : array();
$recipientAccount = isset($paymentData['recipient_account']) && is_array($paymentData['recipient_account']) ? $paymentData['recipient_account'] : array();
$metadata = isset($paymentData['metadata']) && is_array($paymentData['metadata']) ? $paymentData['metadata'] : array();

$payment_id = isset($paymentData['id']) ? $paymentData['id'] : '';
$holder_id = isset($recipientAccount['holder_id']) ? $recipientAccount['holder_id'] : '';
$account_number = isset($recipientAccount['number']) ? $recipientAccount['number'] : '';
$account_type = isset($recipientAccount['type']) ? $recipientAccount['type'] : '';
$institution_id = isset($recipientAccount['institution_id']) ? $recipientAccount['institution_id'] : '';
$amount = isset($paymentData['amount']) ? (int)$paymentData['amount'] : 0;
$currency = isset($paymentData['currency']) ? $paymentData['currency'] : '';
$status = isset($paymentData['status']) ? $paymentData['status'] : '';
$reference_id = isset($paymentData['reference_id']) ? $paymentData['reference_id'] : '';
$order_id = isset($metadata['order_id']) ? $metadata['order_id'] : '';
$origen = isset($metadata['origen']) ? $metadata['origen'] : '';

$db = db_connect();

$sql = "
    INSERT INTO aporte_fintoc 
    (
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
";

$stmt = $db->prepare($sql);

if (!$stmt) {
    error_log('Error preparando INSERT aporte_fintoc: ' . $db->error);
    http_response_code(500);
    exit;
}

$stmt->bind_param(
    'ssssssssissss',
    $id,
    $type,
    $created_at,
    $payment_id,
    $holder_id,
    $account_number,
    $account_type,
    $institution_id,
    $amount,
    $currency,
    $status,
    $reference_id,
    $order_id
);

if (!$stmt->execute()) {
    error_log('Error ejecutando INSERT aporte_fintoc: ' . $stmt->error);
    http_response_code(500);
    $stmt->close();
    exit;
}

$stmt->close();

if ($status === 'succeeded' && $origen === 'portal' && !empty($order_id)) {

    $updateSql = "
        UPDATE donaciones_online 
        SET estado_pago_id = 1 
        WHERE id = ?
    ";

    $updateStmt = $db->prepare($updateSql);

    if ($updateStmt) {
        $updateStmt->bind_param('s', $order_id);

        if (!$updateStmt->execute()) {
            error_log('Error al actualizar donaciones_online desde webhook Fintoc: ' . $updateStmt->error);
        }

        $updateStmt->close();
    } else {
        error_log('Error preparando UPDATE donaciones_online: ' . $db->error);
    }

    require_once __DIR__ . '/../../php/enviar_correo.php';

    $correoEnviado = enviarCorreoAgradecimiento($order_id, $payment_id, 'Fintoc');
}

http_response_code(200);
echo 'OK';

if (isset($db)) {
    $db->close();
}