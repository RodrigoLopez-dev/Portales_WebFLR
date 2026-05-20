<?php

require_once __DIR__ . '/../../config/env.php';
load_env(__DIR__ . '/../../.env');

require_once __DIR__ . '/../../config/database.php';

function get_ws($data, $method, $endpoint)
{
    $curl = curl_init();

    $TbkApiKeyId = env_value('WEBPAY_API_KEY_ID', '');
    $TbkApiKeySecret = env_value('WEBPAY_API_KEY_SECRET', '');
    $baseUrl = env_value('WEBPAY_BASE_URL', '');

    if (!$TbkApiKeyId || !$TbkApiKeySecret || !$baseUrl) {
        error_log('Faltan variables de entorno Webpay');
        return null;
    }

    $url = rtrim($baseUrl, '/') . $endpoint;

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            'Tbk-Api-Key-Id: ' . $TbkApiKeyId,
            'Tbk-Api-Key-Secret: ' . $TbkApiKeySecret,
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ($response === false) {
        error_log('Error CURL Webpay: ' . curl_error($curl));
        curl_close($curl);
        return null;
    }

    curl_close($curl);

    if ($httpCode < 200 || $httpCode >= 300) {
        error_log('Error HTTP Webpay ' . $httpCode . ': ' . $response);
        return null;
    }

    $decoded = json_decode($response);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Respuesta JSON inválida Webpay: ' . json_last_error_msg());
        return null;
    }

    return $decoded;
}

function redirect_to($url)
{
    header('Location: ' . $url);
    exit;
}

$appBaseUrl = rtrim(env_value('APP_BASE_URL', ''), '/');
$appName = trim(env_value('APP_NAME', ''), '/');

$appUrl = $appBaseUrl;

if ($appName !== '') {
    $appUrl .= '/' . $appName;
}

if ($appBaseUrl === '') {
    error_log('APP_BASE_URL no configurada');
    http_response_code(500);
    exit('Configuración incompleta');
}

$action = isset($_GET['ac']) ? $_GET['ac'] : 'init';
$baseurl = $appUrl . '/pagos/webpay/pagos.php';

$conn = db_connect();

if ($conn->connect_error) {
    error_log('Conexión fallida Webpay: ' . $conn->connect_error);
    redirect_to($appUrl . '/fallo');
}

switch ($action) {
    case 'init':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Método no permitido');
        }

        $amount = isset($_POST['monto']) ? (int) $_POST['monto'] : 0;
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';

        if ($amount <= 0 || $order_id === '') {
            http_response_code(400);
            exit('Datos de pago inválidos');
        }

        $data = json_encode(array(
            'buy_order' => $order_id,
            'session_id' => $order_id,
            'amount' => $amount,
            'return_url' => $baseurl . '?ac=e'
        ));

        $response = get_ws(
            $data,
            'POST',
            '/rswebpaytransaction/api/webpay/v1.0/transactions'
        );

        if (!$response || !isset($response->url, $response->token)) {
            error_log('Webpay no entregó URL/token válido: ' . print_r($response, true));
            redirect_to($appUrl . '/fallo');
        }

        $url_tbk = $response->url;
        $token = $response->token;
        $submit = 'Continuar';

        break;

    case 'e':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log('Retorno Webpay con método inválido');
            redirect_to($appUrl . '/fallo');
        }

        if (!isset($_POST['token_ws']) || trim($_POST['token_ws']) === '') {
            error_log('Retorno Webpay sin token_ws: ' . print_r($_POST, true));
            redirect_to($appUrl . '/fallo');
        }

        $token = trim($_POST['token_ws']);

        $response = get_ws(
            '',
            'PUT',
            '/rswebpaytransaction/api/webpay/v1.0/transactions/' . urlencode($token)
        );

        if (!$response || !isset($response->status)) {
            error_log('Webpay commit sin respuesta válida: ' . print_r($response, true));
            redirect_to($appUrl . '/fallo');
        }

        if ($response->status === 'AUTHORIZED') {
            $vci = isset($response->vci) && $response->vci !== ''
                ? $response->vci
                : (isset($response->payment_type_code) ? $response->payment_type_code : 'WEBPAY');

            $response_code = isset($response->response_code) ? $response->response_code : '';
            $buy_order = isset($response->buy_order) ? $response->buy_order : '';
            $session_id = isset($response->session_id) ? $response->session_id : '';
            $authorization_code = isset($response->authorization_code) ? $response->authorization_code : '';
            $amount = isset($response->amount) ? $response->amount : '';
            $card_number = isset($response->card_detail->card_number)
                ? substr($response->card_detail->card_number, 0, 200)
                : '';
            $account_number = $card_number;
            $fecha_expiracion = '';
            $accounting_date = isset($response->accounting_date) ? $response->accounting_date : '';
            $transaction_date = isset($response->transaction_date) ? $response->transaction_date : '';
            $hora_transaccion = '';
            $id_transaccion = $session_id;
            $tipo_pago = isset($response->payment_type_code) ? $response->payment_type_code : '';
            $numero_cuotas = isset($response->installments_number) ? $response->installments_number : '';
            $mac = '';
            $monto_cuota = isset($response->installments_amount) ? $response->installments_amount : '';
            $tasa_interes_max = '';
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

            if ($buy_order === '') {
                error_log('Webpay autorizado sin buy_order: ' . print_r($response, true));
                redirect_to($appUrl . '/fallo');
            }

            $sql = "INSERT INTO aporte_webpay (
                        tbk_tipo_transaccion,
                        tbk_respuesta,
                        tbk_orden_compra,
                        tbk_id_sesion,
                        tbk_codigo_autorizacion,
                        tbk_monto,
                        tbk_numero_tarjeta,
                        tbk_numero_final_tarjeta,
                        tbk_fecha_expiracion,
                        tbk_fecha_contable,
                        tbk_fecha_transaccion,
                        tbk_hora_transaccion,
                        tbk_id_transaccion,
                        tbk_tipo_pago,
                        tbk_numero_cuotas,
                        tbk_mac,
                        tbk_monto_cuota,
                        tbk_tasa_interes_max,
                        tbk_ip,
                        token
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                error_log('Error preparando INSERT aporte_webpay: ' . $conn->error);
                redirect_to($appUrl . '/fallo');
            }

            $stmt->bind_param(
                'ssssssssssssssssssss',
                $vci,
                $response_code,
                $buy_order,
                $session_id,
                $authorization_code,
                $amount,
                $card_number,
                $account_number,
                $fecha_expiracion,
                $accounting_date,
                $transaction_date,
                $hora_transaccion,
                $id_transaccion,
                $tipo_pago,
                $numero_cuotas,
                $mac,
                $monto_cuota,
                $tasa_interes_max,
                $ip,
                $token
            );

            if (!$stmt->execute()) {
                error_log('Error al guardar aporte_webpay: ' . $stmt->error);
                $stmt->close();
                redirect_to($appUrl . '/fallo');
            }

            $stmt->close();

            $sql2 = "UPDATE donaciones_online
                     SET estado_pago_id = 1
                     WHERE id = ?
                     AND estado_pago_id <> 1";

            $stmt2 = $conn->prepare($sql2);

            if (!$stmt2) {
                error_log('Error preparando UPDATE donaciones_online: ' . $conn->error);
                redirect_to($appUrl . '/fallo');
            }

            $stmt2->bind_param('s', $buy_order);

            if (!$stmt2->execute()) {
                error_log('Error actualizando donaciones_online: ' . $stmt2->error);
                $stmt2->close();
                redirect_to($appUrl . '/fallo');
            }

            if ($stmt2->affected_rows > 0) {
                require_once __DIR__ . '/../../php/enviar_correo.php';
                enviarCorreoAgradecimiento($buy_order, $authorization_code, 'WebPay');
            }

            $stmt2->close();
            $conn->close();

            redirect_to(
                $appUrl .
                '/pagos/exito.php?id=' . urlencode($buy_order) .
                '&monto=' . urlencode($amount) .
                '&medio_pago=' . urlencode('WebPay')
            );
        }

        $buyOrderError = isset($response->buy_order) ? $response->buy_order : '';

        if ($buyOrderError !== '') {
            $stmt3 = $conn->prepare("UPDATE donaciones_online SET estado_pago_id = 3 WHERE id = ?");

            if ($stmt3) {
                $stmt3->bind_param('s', $buyOrderError);
                $stmt3->execute();
                $stmt3->close();
            } else {
                error_log('Error preparando UPDATE fallo Webpay: ' . $conn->error);
            }
        }

        $conn->close();
        redirect_to($appUrl . '/fallo');

    default:
        error_log('Acción Webpay no válida: ' . $action);
        redirect_to($appUrl . '/fallo');
}

?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Redirigiendo a Webpay</title>
</head>

<body>
    <?php if (isset($url_tbk, $token) && $url_tbk !== '' && $token !== '') { ?>
        <form name="brouterForm" id="brouterForm" method="POST"
            action="<?php echo htmlspecialchars($url_tbk, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="token_ws" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="submit" value="<?php echo htmlspecialchars($submit, ENT_QUOTES, 'UTF-8'); ?>" hidden>
        </form>

        <script>
            document.getElementById('brouterForm').submit();
        </script>
    <?php } ?>
</body>

</html>