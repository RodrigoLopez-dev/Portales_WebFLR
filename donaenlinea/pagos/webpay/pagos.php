<?php

function get_ws($data, $method, $endpoint)
{
    $curl = curl_init();

    $TbkApiKeyId = getenv('WEBPAY_API_KEY_ID');
    $TbkApiKeySecret = getenv('WEBPAY_API_KEY_SECRET');
    $baseUrl = getenv('WEBPAY_BASE_URL');

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

    if ($response === false) {
        error_log('Error CURL Webpay: ' . curl_error($curl));
        curl_close($curl);
        return null;
    }

    curl_close($curl);

    return json_decode($response);
}

require_once('../../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = isset($_POST['monto']) ? $_POST['monto'] : '';
    $order_id = isset($_POST['order_id']) ? $_POST['order_id'] : '';
}

// Conexión a la base de datos
$conn = db_connect();

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$baseurl = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
//$url="https://webpay3gint.transbank.cl/";//Testing
$url = "https://webpay3g.transbank.cl/";//Live
$action = isset($_GET["ac"]) ? $_GET["ac"] : 'init';
$message = null;
$post_array = false;

switch ($action) {
    case "init":
        $message .= 'init';
        $buy_order = $order_id;
        $session_id = $order_id;
        $amount = $amount;
        $return_url = $baseurl . "?ac=e";
        $data = '{
                "buy_order": "' . $buy_order . '",
                "session_id": "' . $session_id . '",
                "amount": ' . $amount . ',
                "return_url": "' . $return_url . '"
                }';
        $method = 'POST';
        $endpoint = '/rswebpaytransaction/api/webpay/v1.0/transactions';
        $response = get_ws($data, $method, $endpoint);
        $message .= "<pre>";
        $message .= print_r($response, TRUE);
        $message .= "</pre>";
        $url_tbk = $response->url;
        $token = $response->token;
        $submit = 'Continuar!';

        break;

    case "e":
        $message .= "<pre>" . print_r($_POST, TRUE) . "</pre>";
        if (!isset($_POST["token_ws"])) {
            header("Location: ../../");
            //  echo 'Intentar nuevamente';
            break;
        }

        $token = filter_input(INPUT_POST, 'token_ws');
        $request = array(
            "token" => filter_input(INPUT_POST, 'token_ws')
        );
        $data = '';
        $method = 'PUT';
        $endpoint = '/rswebpaytransaction/api/webpay/v1.0/transactions/' . $token;
        $response = get_ws('', $method, $endpoint);
        $message .= "<pre>";
        $message .= print_r($response, TRUE);
        $message .= "</pre>";

        if ($response->status == 'AUTHORIZED') {
            $vci = isset($response->vci) ? $response->vci : '';
            if (empty($vci)) {
                // Si viene vacío, usar código de pago o nombre genérico
                $vci = isset($response->payment_type_code) ? $response->payment_type_code : 'WEBPAY';
            }
            // $vci = $response->vci;
            $response_code = $response->response_code;
            $buy_order = $response->buy_order;
            $session_id = $response->session_id;
            $authorization_code = $response->authorization_code;
            $amount = $response->amount;
            $card_number = substr($response->card_detail->card_number, 0, 200);
            $account_number = substr($response->card_detail->card_number, 0, 200); // Cambia esto según tus necesidades
            $fecha_expiracion = ''; // Ajusta esto según tus necesidades
            $accounting_date = $response->accounting_date;
            $transaction_date = $response->transaction_date;
            $hora_transaccion = ''; // Ajusta esto según tus necesidades
            $id_transaccion = $response->session_id; // Cambia esto según tus necesidades
            $tipo_pago = $response->payment_type_code; // Cambia esto según tus necesidades
            $numero_cuotas = ''; // Ajusta esto según tus necesidades
            $mac = ''; // Ajusta esto según tus necesidades
            $monto_cuota = ''; // Ajusta esto según tus necesidades
            $tasa_interes_max = ''; // Ajusta esto según tus necesidades
            $ip = $_SERVER['REMOTE_ADDR'];
            $token = filter_input(INPUT_POST, 'token_ws');

            // $token = ''; // Ajusta esto según tus necesidades
            // Consulta de inserción en la tabla aporte_webpay
            $sql = "INSERT INTO aporte_webpay (tbk_tipo_transaccion, tbk_respuesta, tbk_orden_compra, tbk_id_sesion, tbk_codigo_autorizacion, tbk_monto, tbk_numero_tarjeta, tbk_numero_final_tarjeta, tbk_fecha_expiracion, tbk_fecha_contable, tbk_fecha_transaccion, tbk_hora_transaccion, tbk_id_transaccion, tbk_tipo_pago, tbk_numero_cuotas, tbk_mac, tbk_monto_cuota, tbk_tasa_interes_max, tbk_ip, token)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                echo "Error en la preparación de la consulta: " . $conn->error;
            }

            $stmt->bind_param(
                "ssssssssssssssssssss",
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

            // Ejecutar la consulta
            if ($stmt->execute()) {
                // Éxito al insertar los datos en aporte_webpay
                // Consulta de actualización en la tabla donaciones_online
                $sql2 = "UPDATE donaciones_online SET estado_pago_id = 1 WHERE id = ?";
                // Preparar la consulta de actualización
                $stmt2 = $conn->prepare($sql2);
                // Enlazar el parámetro
                $stmt2->bind_param("s", $buy_order);
                // Ejecutar la consulta de actualización
                if ($stmt2->execute()) {
                    // Éxito al actualizar el estado en donaciones_online
                } else {
                    // Error al actualizar el estado en donaciones_online
                    error_log("Error al actualizar el estado en la tabla donaciones_online: " . $stmt2->error);
                }
            } else {
                // Error al insertar datos en aporte_webpay
                echo "Error al guardar los datos en aporte_webpay: " . $stmt->error;
            }
            // Cerrar los prepared statements y la conexión
            $stmt->close();
            $stmt2->close();
            $conn->close();
            require '../../php/enviar_correo.php'; // Incluye la hoja con la función
            $correoEnviado = enviarCorreoAgradecimiento($buy_order, $authorization_code, "WebPay"); // Ajusta los valores
            header("Location: ../exito.php?id=" . $buy_order . "&monto=" . $amount . "&medio_pago=WebPay");
            //  echo '<br><br>Gracias por su donación<br><br>';
            break;
        } else {
            $buyOrderError = isset($response->buy_order) ? $response->buy_order : '';

            $stmt3 = $conn->prepare("UPDATE donaciones_online SET estado_pago_id = 3 WHERE id = ?");

            if ($stmt3) {
                $stmt3->bind_param("s", $buyOrderError);
                $stmt3->execute();
                $stmt3->close();
            }
            header("Location: ../../fallo");
            //echo '<br><br>Hubo un problema con su tarjeta<br><br>';
            break;
        }
        break;
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Webpay Plus Mall">
    <title>Pagos</title>
</head>

<body>
    <p><?php
    if (!isset($url_tbk)) {
        echo '';
    } else {
        // echo $message; ?></p>
        <?php if (strlen($url_tbk)) { ?>
            <form name="brouterForm" id="brouterForm" method="POST" action="<?= $url_tbk ?>" style="display:block;">
                <input type="hidden" name="token_ws" value="<?= $token ?>" />
                <input type="submit" value="<?= $submit ?>" hidden />
            </form>
            <script>
                document.getElementById('brouterForm').submit();
            </script>
        <?php }
    } ?>
</body>

</html>