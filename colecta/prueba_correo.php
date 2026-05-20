<!DOCTYPE html>

<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Correo de Agradecimiento</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #af2a40;
            font-size: 24px;
        }

        label {
            font-weight: bold;
        }

        #mensaje {
            font-size: 14px;
        }
    </style>

</head>

<body>
    <div class="container">
        <h1>Prueba de Correo de Agradecimiento</h1>
        <form method="post" action="">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required><br><br>
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required><br><br>
            <label for="monto">Monto de Donación:</label>
            <input type="number" id="monto" name="monto" required><br><br>
            <button type="submit" name="enviarCorreo">Enviar Correo</button>
        </form>
        <?php
        if (isset($_POST['enviarCorreo'])) {
            $nombre = $_POST["nombre"];
            $email = $_POST["email"];
            $monto = $_POST["monto"];
            $cod = '13324';
            $para = $email;
            $titulo = "¡Gracias por tu donación a Fundación Las Rosas!";
            $mensaje = "<p><strong>Estimado/a $nombre,</strong></p>";
            $mensaje .= "<p>Agradecemos tu donación de $" . number_format($monto, 0, ',', '.') . " a Fundación Las Rosas. Tu apoyo es fundamental para nuestra causa.</p>";
            $mensaje .= "<p>Desde la apertura del primer hogar en 1967, nuestra institución ha trabajado por seguir cumpliendo la misión de acoger a las personas mayores más pobres del país, entregándoles amor, cariño y los cuidados necesarios para que vivan su vejez con la dignidad que todos merecemos.</p>";
            $mensaje .= "<p>Fundación Las Rosas se ha transformado en el establecimiento de larga estadía (ELEAM) para adultos mayores más importante del país, con 28 hogares y 2.200 residentes en promedio distribuidos entre la región de Coquimbo y Los Lagos.</p>";
            $mensaje .= "<p><a href='https://fundacionlasrosas.cl/colecta/comprobantepdf?cod=$cod' style='display: inline-block; padding: 10px; background-color: #4CAF50; color: white; text-decoration: none; margin-top: 20px;'>Descargar Comprobante</a></p>";
            $mensaje .= "<p><strong>Canales de Contacto:</strong></p>";
            $mensaje .= "<p>Llama gratis a nuestra Mesa de Ayuda Telefónica, desde fijos o celulares al 800 719 711.</p>";
            $mensaje .= "<p>Escríbenos al mail info@flrosas.cl</p>";
            $mensaje .= "<p>Av. Fermín Vivaceta 590, Independencia, Región Metropolitana, Chile</p>";
            $mensaje .= "<p><a href='http://flr.cl/firma' target='_blank'><img src='http://flr.cl/banner.jpg' width='500' height='90' border='0'></a></p>";
            $cabeceras = "From: Fundación Las Rosas <no-responder@fundacionlasrosas.cl>\r\n";
            $cabeceras .= "Reply-To: no-responder@fundacionlasrosas.cl\r\n";
            $cabeceras .= "X-Mailer: PHP/" . phpversion() . " \r\n";
            $cabeceras .= "MIME-Version: 1.0\r\n";
            $cabeceras .= "Content-type: text/html; charset=utf-8\r\n";

            if (mail($para, $titulo, $mensaje, $cabeceras)) {
                echo "<p>Correo enviado con éxito a $email.</p>";
            } else {
                echo "<p>Ocurrió un error al enviar el correo.</p>";
            }
        }
        ?>
    </div>
</body>

</html>