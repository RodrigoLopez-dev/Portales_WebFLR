<?php

require_once __DIR__ . '/../config/env.php';

load_env(__DIR__ . '/../.env');

$appBaseUrl = rtrim(env_value('APP_BASE_URL', ''), '/');
$appName = trim(env_value('APP_NAME', ''), '/');

if (empty($appBaseUrl)) {
    die('APP_BASE_URL no configurada.');
}

$appUrl = $appName !== '' ? $appBaseUrl . '/' . $appName : $appBaseUrl;

$monto = isset($_GET['monto']) ? $_GET['monto'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$medio_pago = isset($_GET['medio_pago']) ? $_GET['medio_pago'] : '';
$payment_intent_id = isset($_GET['payment_intent_id']) ? $_GET['payment_intent_id'] : '';

if ($monto === '' || $id === '' || $medio_pago === '') {
    echo "<script>alert('Datos de pago incompletos.'); window.location='" . htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') . "';</script>";
    exit;
}

?>
<form name="exito" id="exito" action="<?php echo htmlspecialchars($appUrl . '/gracias.php', ENT_QUOTES, 'UTF-8'); ?>" method="post">
    <input type="hidden" name="monto" value="<?php echo htmlspecialchars($monto, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="payment_intent_id" value="<?php echo htmlspecialchars($payment_intent_id, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="medio_pago" value="<?php echo htmlspecialchars($medio_pago, ENT_QUOTES, 'UTF-8'); ?>">
</form>

<script>
    document.getElementById('exito').submit();
</script>