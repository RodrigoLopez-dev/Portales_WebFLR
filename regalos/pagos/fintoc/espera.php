<?php

require_once __DIR__ . '/../../config/env.php';

load_env(__DIR__ . '/../../.env');

$appBaseUrl = rtrim(env_value('APP_BASE_URL', ''), '/');
$appName = trim(env_value('APP_NAME', ''), '/');

if ($appBaseUrl === '') {
    die('APP_BASE_URL no configurada.');
}

$appUrl = $appName !== '' ? $appBaseUrl . '/' . $appName : $appBaseUrl;

$monto = isset($_GET['monto']) ? $_GET['monto'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$medioPago = isset($_GET['medio_pago']) ? $_GET['medio_pago'] : 'Transferencia(fintoc)';
$paymentIntentId = isset($_GET['payment_intent_id']) ? $_GET['payment_intent_id'] : '';

if ($monto === '' || $id === '') {
    echo "<script>alert('Datos de pago incompletos.'); window.location='" . htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') . "';</script>";
    exit;
}

$successUrl = $appUrl . '/gracias.php';
$failureUrl = $appUrl . '/fallo';

$checkStatusUrl = $appUrl . '/pagos/fintoc/check_status.php?id=' . urlencode($id);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Confirmando pago</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            color: #333;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .box {
            background: #fff;
            max-width: 460px;
            width: 100%;
            padding: 32px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.08);
        }

        .loader {
            width: 42px;
            height: 42px;
            border: 4px solid #ddd;
            border-top-color: #ab0a3f;
            border-radius: 50%;
            margin: 0 auto 20px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        h1 {
            font-size: 22px;
            margin-bottom: 12px;
        }

        p {
            font-size: 15px;
            line-height: 1.5;
        }

        .small {
            font-size: 13px;
            color: #666;
            margin-top: 18px;
        }
    </style>
</head>

<body>
    <div class="box">
        <div class="loader"></div>
        <h1>Estamos confirmando tu pago</h1>
        <p>Esto puede tardar unos segundos. Por favor, no cierres esta ventana.</p>
        <p class="small" id="statusText">Consultando estado del pago...</p>
    </div>

    <form name="successForm" id="successForm" action="<?php echo htmlspecialchars($successUrl, ENT_QUOTES, 'UTF-8'); ?>"
        method="post">
        <input type="hidden" name="monto" value="<?php echo htmlspecialchars($monto, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="payment_intent_id"
            value="<?php echo htmlspecialchars($paymentIntentId, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="medio_pago" value="<?php echo htmlspecialchars($medioPago, ENT_QUOTES, 'UTF-8'); ?>">
    </form>

    <script>
        var checkStatusUrl = <?php echo json_encode($checkStatusUrl); ?>;
        var failureUrl = <?php echo json_encode($failureUrl); ?>;
        var attempts = 0;
        var maxAttempts = 24;

        function checkPaymentStatus() {
            attempts++;

            fetch(checkStatusUrl, {
                method: 'GET',
                credentials: 'same-origin'
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    console.log('Estado Fintoc:', data);

                    if (data.ok && data.paid) {
                        document.getElementById('successForm').submit();
                        return;
                    }

                    if (data.ok && data.failed) {
                        window.location.href = failureUrl;
                        return;
                    }

                    if (attempts >= maxAttempts) {
                        document.getElementById('statusText').innerText =
                            'Tu pago está siendo validado. Si ya fue descontado, recibirás la confirmación pronto.';
                        return;
                    }

                    setTimeout(checkPaymentStatus, 5000);
                })
                .catch(function (error) {
                    console.error('Error consultando estado Fintoc:', error);

                    if (attempts >= maxAttempts) {
                        document.getElementById('statusText').innerText =
                            'No pudimos confirmar el pago en este momento. Intenta revisar más tarde.';
                        return;
                    }

                    setTimeout(checkPaymentStatus, 5000);
                });
        }

        checkPaymentStatus();
    </script>
</body>

</html>