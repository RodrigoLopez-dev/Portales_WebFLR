<?php

session_start();

require_once __DIR__ . '/generaqr/key/authorization.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/env.php';

load_env(__DIR__ . '/../../.env');

function build_app_url()
{
    $appBaseUrl = rtrim(env_value('APP_BASE_URL', ''), '/');
    $appName = trim(env_value('APP_NAME', ''), '/');

    if ($appBaseUrl === '') {
        return '';
    }

    if ($appName !== '') {
        return $appBaseUrl . '/' . $appName;
    }

    return $appBaseUrl;
}

function redirect_with_alert($message, $url)
{
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

    echo "<script>alert('{$safeMessage}'); window.location='{$safeUrl}';</script>";
    exit;
}

$appUrl = build_app_url();

if ($appUrl === '') {
    error_log('MACH: APP_BASE_URL no configurada.');
    http_response_code(500);
    exit('Configuración incompleta.');
}

$homeUrl = $appUrl . '/';
$failureUrl = $appUrl . '/fallo';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST)) {
    redirect_with_alert('Ocurrió un error. Inténtelo nuevamente.', $homeUrl);
}

$monto = isset($_POST['monto']) ? (int) $_POST['monto'] : 0;
$orden_compra = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';

if ($monto <= 0 || $orden_compra === '') {
    error_log('MACH: monto u orden_compra inválidos.');
    redirect_with_alert('Datos de pago inválidos.', $homeUrl);
}

$db = db_connect();

if (!$db || $db->connect_error) {
    error_log('MACH: conexión BD fallida: ' . ($db ? $db->connect_error : 'db_connect retornó null'));
    redirect_with_alert('Error al conectar con la base de datos.', $failureUrl);
}

$stmt = $db->prepare("
    INSERT INTO aporte_mach 
    (orden_compra, monto, estado_pago_id)
    VALUES (?, ?, 2)
");

if (!$stmt) {
    error_log('MACH: error preparando INSERT aporte_mach: ' . $db->error);
    redirect_with_alert('Error al registrar el pago.', $failureUrl);
}

$stmt->bind_param('si', $orden_compra, $monto);

if (!$stmt->execute()) {
    error_log('MACH: error ejecutando INSERT aporte_mach: ' . $stmt->error);
    $stmt->close();
    redirect_with_alert('Error al registrar el pago.', $failureUrl);
}

$stmt->close();

$createBusinessPayment = array(
    'payment' => array(
        'amount' => $monto,
        'message' => 'Pago',
        'title' => 'Donacion',
        'metadata' => array(
            'product_id' => 'donacion',
            'customer_id' => $orden_compra
        )
    )
);

$data_string = json_encode($createBusinessPayment);

if ($data_string === false) {
    error_log('MACH: error generando JSON: ' . json_last_error_msg());
    redirect_with_alert('Error al preparar el pago.', $failureUrl);
}

$baseUrl = env_value('MACH_API_BASE_URL', 'https://biz.soymach.com');

$curl = curl_init();

$headers[] = 'Content-Length: ' . strlen($data_string);

curl_setopt_array($curl, array(
    CURLOPT_URL => rtrim($baseUrl, '/') . '/payments',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $data_string,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HEADER => false,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_USERAGENT => 'Mozilla/5.0'
));

$response = curl_exec($curl);

if ($response === false) {
    error_log('MACH: error CURL: ' . curl_error($curl));
    curl_close($curl);
    redirect_with_alert('Error al conectar con MACH.', $failureUrl);
}

$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($httpCode < 200 || $httpCode >= 300) {
    error_log('MACH: HTTP ' . $httpCode . ' respuesta: ' . $response);
    redirect_with_alert('MACH rechazó la solicitud de pago.', $failureUrl);
}

$array = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('MACH: JSON inválido: ' . json_last_error_msg() . ' respuesta: ' . $response);
    redirect_with_alert('Respuesta inválida desde MACH.', $failureUrl);
}

if (!$array || !isset($array['token']) || trim($array['token']) === '') {
    error_log('MACH: respuesta sin token válido: ' . $response);
    redirect_with_alert('MACH no entregó un token válido.', $failureUrl);
}

$token = trim($array['token']);

$updateStmt = $db->prepare("
    UPDATE aporte_mach 
    SET token = ? 
    WHERE orden_compra = ?
");

if (!$updateStmt) {
    error_log('MACH: error preparando UPDATE aporte_mach: ' . $db->error);
    redirect_with_alert('Error al actualizar el pago.', $failureUrl);
}

$updateStmt->bind_param('ss', $token, $orden_compra);

if (!$updateStmt->execute()) {
    error_log('MACH: error ejecutando UPDATE aporte_mach: ' . $updateStmt->error);
    $updateStmt->close();
    redirect_with_alert('Error al actualizar el pago.', $failureUrl);
}

$updateStmt->close();
$db->close();

$_SESSION['token'] = $token;

?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Redirigiendo a MACH</title>
</head>

<body>
    <form name="mach" id="mach" action="generaqr" method="post"></form>

    <script>
        document.getElementById('mach').submit();
    </script>
</body>

</html>