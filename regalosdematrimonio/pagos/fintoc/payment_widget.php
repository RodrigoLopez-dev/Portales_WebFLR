<?php
require_once __DIR__ . '/../../config/env.php';

load_env(__DIR__ . '/../../.env');

$fintocPublicKey = env_value('FINTOC_PUBLIC_KEY', '');

$appBaseUrl = rtrim(env_value('APP_BASE_URL', ''), '/');
$appName = trim(env_value('APP_NAME', ''), '/');

if (empty($fintocPublicKey)) {
    die('FINTOC_PUBLIC_KEY no configurada.');
}

if (empty($appBaseUrl)) {
    die('APP_BASE_URL no configurada.');
}

if (empty($appName)) {
    die('APP_NAME no configurada.');
}

if (!isset($result['widget_token'], $amount, $order_id)) {
    die('Datos incompletos para iniciar Fintoc.');
}

$appUrl = $appBaseUrl . '/' . $appName;

$fintocWebhookUrl = $appUrl . '/pagos/fintoc/webhook.php';

$paymentIntentId = isset($result['id']) ? $result['id'] : '';

$successUrl = $appUrl . '/pagos/fintoc/espera.php?monto=' . urlencode($amount)
    . '&id=' . urlencode($order_id)
    . '&payment_intent_id=' . urlencode($paymentIntentId)
    . '&medio_pago=' . urlencode('Transferencia(fintoc)');

$exitUrl = $appUrl . '/';
$errorUrl = $appUrl . '/fallo';

error_log('FINTOC WEBHOOK URL: ' . $fintocWebhookUrl);
error_log('FINTOC SUCCESS URL: ' . $successUrl);
?>

<!DOCTYPE html>

<html>

<head>
    <title>Mi página de pagos</title>
    <script src="https://js.fintoc.com/v1/"></script>
</head>

<body>
    <div id="fintoc-container"></div>

    <script>
        const successUrl = <?php echo json_encode($successUrl); ?>;
        const exitUrl = <?php echo json_encode($exitUrl); ?>;
        const errorUrl = <?php echo json_encode($errorUrl); ?>;

        const options = {
            holderType: 'individual',
            product: 'payments',
            publicKey: <?php echo json_encode($fintocPublicKey); ?>,
            webhookUrl: <?php echo json_encode($fintocWebhookUrl); ?>,
            country: 'cl',
            widgetToken: <?php echo json_encode($result['widget_token']); ?>,

            onSuccess: function () {
                console.log('¡Conexión exitosa!');
                window.location.href = successUrl;
            },

            onExit: function () {
                console.log('Widget cerrado por el usuario');
                window.location.href = exitUrl;
            },

            onEvent: function (event) {
                console.log('Evento ocurrido: ', event);

                if (event === 'payment_created') {
                    console.log('Pago creado. Esperando confirmación por webhook.');
                }

                if (event === 'payment_error') {
                    setTimeout(function () {
                        window.location.href = errorUrl;
                    }, 3000);
                }
            }
        };

        window.onload = function () {
            const widget = Fintoc.create(options);
            widget.open();
        };
    </script>
</body>

</html>