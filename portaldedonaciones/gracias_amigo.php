<?php

require_once __DIR__ . '/enigma.php';
require_once __DIR__ . '/config/database.php';

date_default_timezone_set('America/Santiago');

$eni = new Enigma();
$conn = db_connect();

$nombre = '';

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (!empty($_GET)) {
    $eni->decode_get2($_SERVER['REQUEST_URI']);

    $cod = isset($_GET['id']) ? trim((string) $_GET['id']) : '';

    if ($cod !== '' && ctype_digit($cod)) {
        $id = (int) $cod;

        $stmt = $conn->prepare('SELECT nombre FROM cuotas_unicas WHERE id = ? LIMIT 1');

        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($nombreDb);

            if ($stmt->fetch()) {
                $nombre = $nombreDb;
            }

            $stmt->close();
        } else {
            error_log('Error preparando SELECT cuotas_unicas en gracias_amigo.php: ' . $conn->error);
        }
    }
}

$conn->close();
?>
<!doctype html>
<html lang="es">

<head>
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Last-Modified" content="0">
    <meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="theme-color" content="#af0a3d" />

    <title>Fundación Las Rosas</title>

    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />

    <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png" />
    <link rel="icon" type="image/png" href="assets/img/favicon.ico" />

    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />

    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/material-bootstrap-wizard.css" rel="stylesheet" />
    <link href="assets/css/estilos.css" rel="stylesheet" />

    <style>
        #div_iframe {
            display: flex;
            justify-content: center;
        }
    </style>

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-113984126-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());
        gtag('config', 'UA-113984126-1');
    </script>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js" type="text/javascript"></script>
    <script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="assets/js/jquery.bootstrap.js" type="text/javascript"></script>
    <script src="assets/js/material-bootstrap-wizard.js"></script>
    <script src="assets/js/jquery.validate.min.js"></script>
</head>

<body>
    <div class="image-container set-full-height" style="background-image: url('assets/img/portal.jpg')">
        <a href="https://www.fundacionlasrosas.cl">
            <div class="logo-container">
                <img src="assets/img/logofinal01.png" width="150" alt="Fundación Las Rosas">
            </div>
        </a>

        <div class="container">
            <div class="row">
                <div class="col-sm-8 col-sm-offset-2">
                    <div class="wizard-container">
                        <div class="card wizard-card" data-color="flr" id="wizard">
                            <div class="wizard-header">
                                <h3 class="wizard-title">¡Gracias!</h3>
                                <h5>
                                    Por tu ayuda <i class="fa fa-heart heart" style="color:#FF0000;"></i>
                                </h5>
                            </div>

                            <div class="tab-content">
                                <div class="info-text">
                                    <?php if ($nombre !== ''): ?>
                                        <h5><?php echo e($nombre); ?></h5>
                                    <?php endif; ?>

                                    <p>
                                        Tus datos fueron recepcionados correctamente, dentro de los próximos días recibirás un correo de confirmación.
                                        <br>Muchas gracias.
                                    </p>

                                    <p>
                                        ¿Tienes alguna consulta? Llama al 800 719 711 opción 1 y te atenderemos.
                                    </p>

                                    <p>
                                        <strong>Horario de atención:</strong><br>
                                        Lunes a jueves de 9:00 a 18:00 hrs.<br>
                                        Viernes de 9:00 a 17:00 hrs.
                                    </p>

                                    <p><strong>Hemos recepcionado su solicitud.</strong></p>

                                    <h4>
                                        <a href="https://www.fundacionlasrosas.cl/portaldedonaciones">
                                            &gt; Volver al Portal de Donaciones
                                        </a>
                                    </h4>

                                    <h4>
                                        <a href="https://www.fundacionlasrosas.cl">
                                            &gt; Continuar navegando en fundacionlasrosas.cl
                                        </a>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div> <!-- wizard container -->
                </div>
            </div>
        </div>

        <div class="footer">
            <div style="display: flex; justify-content: center;">
                <a class="btn-second" href="tel:800719711">800 719 711 <br> Opción 1 <br> Mesa de ayuda</a>
                <a class="btn-fourth" href="tel:800719711">Llámanos <i class="material-icons">touch_app</i></a>
            </div>
            <div class="container text-center">
                Los cuidamos para siempre <i class="fa fa-heart heart"></i>
            </div>
        </div>
    </div>
</body>

</html>