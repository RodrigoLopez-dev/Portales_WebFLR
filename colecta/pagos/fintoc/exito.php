<?php

require_once __DIR__ . '/../../config/env.php';

load_env(__DIR__ . '/../../.env');

function fintoc_exito_app_url()
{
    $baseUrl = rtrim(env_value('APP_BASE_URL', ''), '/');
    $appName = trim(env_value('APP_NAME', ''), '/');

    if ($baseUrl === '') {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $baseUrl = $scheme . '://' . $host;
    }

    if ($appName !== '') {
        return $baseUrl . '/' . $appName;
    }

    return $baseUrl;
}

$appUrl = fintoc_exito_app_url();

$monto = isset($_GET['monto']) ? (int) $_GET['monto'] : 0;
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$medioPago = isset($_GET['medio_pago']) ? $_GET['medio_pago'] : 'Transferencia(fintoc)';

$graciasUrl = $appUrl . '/gracias';

?>
<form name="exito" id="exito" action="<?php echo htmlspecialchars($graciasUrl, ENT_QUOTES, 'UTF-8'); ?>" method="post">
    <input type="hidden" name="monto" value="<?php echo htmlspecialchars($monto, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="medio_pago" value="<?php echo htmlspecialchars($medioPago, ENT_QUOTES, 'UTF-8'); ?>">
</form>
<script>
    document.getElementById('exito').submit();
</script>