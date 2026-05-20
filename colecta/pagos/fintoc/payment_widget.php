<?php

require_once __DIR__ . '/../../config/env.php';

load_env(__DIR__ . '/../../.env');

function fintoc_widget_app_url()
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

$appUrl = fintoc_widget_app_url();

$fintocPublicKey = env_value('FINTOC_PUBLIC_KEY', '');

if (trim($fintocPublicKey) === '') {
    error_log('Fintoc payment_widget.php: FINTOC_PUBLIC_KEY no configurada.');
    die('Error de configuración de Fintoc.');
}

$webhookPath = env_value('FINTOC_WEBHOOK_PATH', '/pagos/fintoc/webhook.php');
$successPath = env_value('FINTOC_SUCCESS_PATH', '/pagos/fintoc/exito.php');

$fintocWebhookUrl = $appUrl . '/' . ltrim($webhookPath, '/');

$widgetToken = isset($result['widget_token']) ? $result['widget_token'] : '';
$amountSafe = isset($amount) ? (int) $amount : 0;
$orderIdSafe = isset($orderId) ? trim((string) $orderId) : '';
$utmSourceSafe = isset($utmSource) ? trim((string) $utmSource) : '';
$utmMediumSafe = isset($utmMedium) ? trim((string) $utmMedium) : '';
$utmCampaignSafe = isset($utmCampaign) ? trim((string) $utmCampaign) : '';

$successUrl = $appUrl . '/' . ltrim($successPath, '/')
    . '?monto=' . urlencode($amountSafe)
    . '&id=' . urlencode($orderIdSafe)
    . '&medio_pago=' . urlencode('Transferencia(fintoc)');

$exitUrl = $appUrl
    . '?utm_source=' . urlencode($utmSourceSafe)
    . '&utm_medium=' . urlencode($utmMediumSafe)
    . '&utm_campaign=' . urlencode($utmCampaignSafe);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fundación Las Rosas - Dona Aquí</title>
    <meta name="description" content="La colecta de Fundación Las Rosas.">
    <script src="https://js.fintoc.com/v1/"></script>
</head>

<body>
    <div id="fintoc-container"></div>

    <script>
        var options = {
            holderType: 'individual',
            product: 'payments',
            publicKey: '<?php echo htmlspecialchars($fintocPublicKey, ENT_QUOTES, 'UTF-8'); ?>',
            webhookUrl: '<?php echo htmlspecialchars($fintocWebhookUrl, ENT_QUOTES, 'UTF-8'); ?>',
            country: 'cl',
            widgetToken: '<?php echo htmlspecialchars($widgetToken, ENT_QUOTES, 'UTF-8'); ?>',

            onSuccess: function () {
                window.location.href = '<?php echo htmlspecialchars($successUrl, ENT_QUOTES, 'UTF-8'); ?>';
            },

            onExit: function () {
                window.location.href = '<?php echo htmlspecialchars($exitUrl, ENT_QUOTES, 'UTF-8'); ?>';
            }
        };

        window.onload = function () {
            var widget = Fintoc.create(options);
            widget.open();
        };
    </script>
</body>
</html>