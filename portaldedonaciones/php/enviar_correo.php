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
        $conn->close();
        return false;
    }

    $stmt->bind_param('s', $order_id);

    if (!$stmt->execute()) {
        error_log('Error ejecutando consulta correo agradecimiento: ' . $stmt->error);
        $stmt->close();
        $conn->close();
        return false;
    }

    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        error_log('No se encontró donación para enviar correo. ID: ' . $order_id);
        $stmt->close();
        $conn->close();
        return false;
    }

    $row = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    $email = isset($row['email']) ? trim($row['email']) : '';
    $nombre = isset($row['nombre']) ? trim($row['nombre']) : '';
    $monto = isset($row['monto']) ? (int) $row['monto'] : 0;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log('Email inválido para correo agradecimiento: ' . $email);
        return false;
    }

    $para = $email;
    $asunto = 'Tu donacion ha sido recibida con éxito';

    $nombreSafe = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
    $emailSafe = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $orderIdSafe = htmlspecialchars($order_id, ENT_QUOTES, 'UTF-8');
    $medioPagoSafe = htmlspecialchars($medio_pago, ENT_QUOTES, 'UTF-8');
    $codigoSafe = htmlspecialchars($codigo_autorizacion, ENT_QUOTES, 'UTF-8');
    $montoSafe = number_format($monto, 0, ',', '.');

    $bannerGraciasUrl = 'https://www.fundacionlasrosas.cl/portaldedonaciones/bannerGracias.png';
    $firmaUrl = 'https://www.flr.cl/firma';
    $bannerFirmaUrl = 'https://www.flr.cl/banner.jpg';

    ob_start();
    require __DIR__ . '/templates/correo_gracias.php';
    $mensaje = ob_get_clean();

    $from = 'Fundación Las Rosas <no-responder@fundacionlasrosas.cl>';

    $header = 'From: ' . $from . "\r\n";
    $header .= 'Reply-To: amigos@flrosas.cl' . "\r\n";
    $header .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
    $header .= 'MIME-Version: 1.0' . "\r\n";
    $header .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";

    if (mail($para, $asunto, $mensaje, $header)) {
        return true;
    }

    error_log('Falló el envío de correo a: ' . $email);
    return false;
}