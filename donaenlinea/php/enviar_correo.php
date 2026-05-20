<?php

date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/../config/database.php';

function enviarCorreoAgradecimiento($order_id, $codigo_autorizacion, $medio_pago)
{
    $conn = db_connect();

    $sql = "
        SELECT email, nombre, monto
        FROM donaciones_online
        WHERE id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log('Error preparando consulta correo agradecimiento: ' . $conn->error);
        return false;
    }

    $stmt->bind_param('s', $order_id);

    if (!$stmt->execute()) {
        error_log('Error ejecutando consulta correo agradecimiento: ' . $stmt->error);
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        error_log('No se encontró donación para enviar correo. ID: ' . $order_id);
        $stmt->close();
        return false;
    }

    $row = $result->fetch_assoc();

    $stmt->close();

    $email = isset($row['email']) ? $row['email'] : '';
    $nombre = isset($row['nombre']) ? $row['nombre'] : '';
    $monto = isset($row['monto']) ? $row['monto'] : 0;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log('Email inválido para correo agradecimiento: ' . $email);
        return false;
    }

    $mail = 'Fundación Las Rosas <no-responder@fundacionlasrosas.cl>';
    $para = $email;
    $asunto = 'Gracias por tu aporte en Fundación Las Rosas';

    $nombreSafe = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
    $emailSafe = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $orderIdSafe = htmlspecialchars($order_id, ENT_QUOTES, 'UTF-8');
    $medioPagoSafe = htmlspecialchars($medio_pago, ENT_QUOTES, 'UTF-8');
    $codigoSafe = htmlspecialchars($codigo_autorizacion, ENT_QUOTES, 'UTF-8');
    $montoSafe = number_format((int)$monto, 0, ',', '.');

    $header = 'From: ' . $mail . "\r\n";
    $header .= 'Reply-To: amigos@flrosas.cl' . "\r\n";
    $header .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
    $header .= 'MIME-Version: 1.0' . "\r\n";
    $header .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";

    $mensaje = '<html><body>';
    $mensaje .= '<img src="https://www.fundacionlasrosas.cl/portaldedonaciones/bannerGracias.jpg" width="602" height="195" border="0">';
    $mensaje .= '<br><br>' . $nombreSafe . ' ¡Muchas gracias por tu aporte!<br><br>';

    $mensaje .= 'Detallamos tu donación:<br><br>';
    $mensaje .= 'Código donación: ' . $orderIdSafe . '<br>';
    $mensaje .= 'Medio de pago: ' . $medioPagoSafe . '<br>';
    $mensaje .= 'Código de autorización: ' . $codigoSafe . '<br>';
    $mensaje .= 'Monto de donación: $' . $montoSafe . '<br>';
    $mensaje .= 'Email: ' . $emailSafe;

    $mensaje .= '<br><br><br>';
    $mensaje .= 'Si tienes alguna consulta que hacernos, llama gratis al 800 719 711 o envíanos un email a amigos@flrosas.cl ';
    $mensaje .= 'y con gusto te atenderemos.<br><br>';
    $mensaje .= '<b>¿Aún no eres Amigo de Fundación Las Rosas?</b><br>';
    $mensaje .= 'Llama al 800 719 711, opción 1 y conviértete en Amigo de los que más lo necesitan, ';
    $mensaje .= 'sé Amigo de una Persona Mayor.';

    $mensaje .= "<br><br><a href='https://www.flr.cl/firma' target='_blank'>";
    $mensaje .= "<img src='https://www.flr.cl/banner.jpg' width='500' height='90' border='0'>";
    $mensaje .= "</a><br>";

    $mensaje .= '</body></html>';

    if (mail($para, $asunto, $mensaje, $header)) {
        return true;
    }

    error_log('Falló el envío de correo a: ' . $email);
    return false;
}