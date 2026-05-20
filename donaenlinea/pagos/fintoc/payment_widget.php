<?php

require_once __DIR__ . '/../../config/env.php';

load_env(__DIR__ . '/../../.env');

$fintocPublicKey = env_value('FINTOC_PUBLIC_KEY', '');
$fintocWebhookUrl = env_value('FINTOC_WEBHOOK_URL', '');

if (empty($fintocPublicKey)) {
    die('FINTOC_PUBLIC_KEY no configurada.');
}

if (empty($fintocWebhookUrl)) {
    die('FINTOC_WEBHOOK_URL no configurada.');
}

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
        // Configura las opciones del widget
        const options = {
            holderType: 'individual',
            product: 'payments',

            publicKey: <?php echo json_encode($fintocPublicKey); ?>,

            webhookUrl: <?php echo json_encode($fintocWebhookUrl); ?>,

            country: 'cl',

            widgetToken: '<?php echo $result['widget_token']; ?>',

            onSuccess: function () {
                console.log('¡Conexión exitosa!');
                window.location.href = '../exito.php?monto=<?php echo $amount ?>&id=<?php echo $order_id ?>&medio_pago=Transferencia(fintoc)';
            },

            onExit: function () {
                console.log('Widget cerrado por el usuario');
                window.location.href = '../../';
            },

            onEvent: function (event) {
                console.log('Evento ocurrido: ', event);

                if (event == 'payment_created') {

                    setTimeout(function () {

                        window.location.href = '../exito.php?monto=<?php echo $amount ?>&id=<?php echo $order_id ?>&medio_pago=Transferencia(fintoc)';

                    }, 3000);

                }

                if (event == 'payment_error') {

                    setTimeout(function () {

                        window.location.href = '../../fallo';

                    }, 3000);

                }
            }
        };

        // Inicializa y abre el widget cuando la página se carga
        window.onload = function () {

            const widget = Fintoc.create(options);

            widget.open();
        }
    </script>
</body>

</html>