<?php
if (!empty($_POST)) {
    include "../conexion/configuracion.php";

    $appUrl = rtrim(getenv('APP_URL'), '/');
    if ($appUrl === '') {
        $appUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/colecta';
    }

    $nombre = isset($_POST["nombre"]) ? ucwords(mb_strtolower($_POST["nombre"], 'UTF-8')) : '';
    $rut = isset($_POST["rut"]) ? $_POST["rut"] : '';
    $email = isset($_POST["email"]) ? $_POST["email"] : '';
    $telefono = isset($_POST["telefono"]) ? $_POST["telefono"] : '';
    $cod = isset($_POST["cod"]) ? $_POST["cod"] : '';
    $monto = isset($_POST["monto"]) ? (int) $_POST["monto"] : 0;

    if ($cod === '' || $email === '') {
        print "<script>alert(\"Faltan datos para registrar la donación.\");window.location='" . $appUrl . "';</script>";
        exit;
    }

    $stmt = $db->prepare("
        UPDATE donaciones
        SET nombre = ?, rut = ?, email = ?, telefono = ?
        WHERE id = ?
    ");

    if (!$stmt) {
        error_log("ERROR prepare donaciones: " . $db->error);
        print "<script>alert(\"Ocurrió un error. Intenta nuevamente.\");window.location='" . $appUrl . "';</script>";
        exit;
    }

    $stmt->bind_param("ssssi", $nombre, $rut, $email, $telefono, $cod);
    $insertDonacion = $stmt->execute();
    $stmt->close();

    $stmtMach = $db->prepare("
        UPDATE donaciones_mach
        SET estado_pago_id = 1
        WHERE orden_compra = ?
    ");

    if ($stmtMach) {
        $stmtMach->bind_param("i", $cod);
        $stmtMach->execute();
        $stmtMach->close();
    } else {
        error_log("ERROR prepare donaciones_mach: " . $db->error);
    }

    if ($insertDonacion) {
        $para = $email;
        $titulo = "Tu abrazo ya protege el Hogar Nuestra Señora del Rosario";

        $baseUrl = $appUrl;
        $montoFormateado = '$' . number_format($monto, 0, ',', '.');

        $mensaje = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</title>
        </head>
        <body style="margin:0; padding:0; background:#f4f4f4; font-family:Arial, Helvetica, sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f4;">
                <tr>
                    <td align="center">
                        <table width="700" cellpadding="0" cellspacing="0" border="0" style="width:700px; max-width:700px; background:#ffffff;">
                            <tr>
                                <td>
                                    <img src="' . $baseUrl . '/images/HEADER700x452.jpg" width="700" alt="Tu aporte es el abrazo que nos cubre" style="display:block; width:700px; max-width:100%; border:0;">
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:32px 42px; color:#333333; font-size:16px; line-height:1.55;">
                                    <p style="margin:0 0 18px 0;"><strong>Estimado/a ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . ',</strong></p>
                                    <p style="margin:0 0 18px 0;">
                                        Te agradecemos profundamente tu donación de <strong>' . $montoFormateado . '</strong>
                                        para el proyecto de techumbre del Hogar Nuestra Señora del Rosario.
                                    </p>
                                    <p style="margin:0 0 18px 0;">
                                        En Fundación Las Rosas sabemos que un techo es mucho más que estructura;
                                        es la seguridad de dormir tranquilos, es el calor que se guarda y, sobre todo,
                                        es la certeza de estar protegidos. Hoy, tu aporte es el abrazo que nos cubre.
                                    </p>
                                    <p style="margin:0 0 18px 0;">
                                        Tu compromiso se transforma hoy en bienestar directo para cada una de las
                                        Personas Mayores que acogemos en este Hogar.
                                    </p>
                                    <p style="margin:0;">Con gratitud,<br><strong>Fundación Las Rosas</strong></p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="' . $baseUrl . '/images/FOOTER-700x90.jpg" width="700" alt="Fundación Las Rosas" style="display:block; width:700px; max-width:100%; border:0;">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';

        $cabeceras = "From: Fundación Las Rosas <no-responder@fundacionlasrosas.cl>\r\n";
        $cabeceras .= "Reply-To: no-responder@fundacionlasrosas.cl\r\n";
        $cabeceras .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $cabeceras .= "MIME-Version: 1.0\r\n";
        $cabeceras .= "Content-type: text/html; charset=utf-8\r\n";

        mail($para, $titulo, $mensaje, $cabeceras);

        print "<script>window.location='" . $appUrl . "';</script>";
        exit;
    }

    print "<script>alert(\"Ocurrió un error. Intenta nuevamente.\");window.location='" . $appUrl . "';</script>";
    exit;
}

print "<script>alert(\"Ocurrió un error. Intenta nuevamente.\");window.location='../';</script>";
exit;
?>