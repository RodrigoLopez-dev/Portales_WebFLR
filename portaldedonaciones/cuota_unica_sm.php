<?php
function post_value($key, $default = '')
{
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : $default;
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$id           = post_value('id');
$rut          = post_value('rut');
$nombre       = post_value('nombre');
$email        = post_value('email');
$monto        = post_value('monto');
$utm_source   = post_value('utm_source');
$utm_medium   = post_value('utm_medium');
$utm_campaign = post_value('utm_campaign');

if ($id === '') {
    $id = 'vacio';
}

if ($monto !== '' && !ctype_digit($monto)) {
    $monto = '';
}
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

    <!-- Meta Pixel Code -->
    <script>
        !function (f, b, e, v, n, t, s) {
            if (f.fbq) return;
            n = f.fbq = function () {
                n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
            };
            if (!f._fbq) f._fbq = n;
            n.push = n;
            n.loaded = true;
            n.version = '2.0';
            n.queue = [];
            t = b.createElement(e);
            t.async = true;
            t.src = v;
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s);
        }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

        fbq('init', '3773397849539419');
        fbq('track', 'PageView');
    </script>
    <noscript>
        <img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=3773397849539419&ev=PageView&noscript=1" />
    </noscript>
    <!-- End Meta Pixel Code -->

    <script src="https://code.jquery.com/jquery-3.4.1.min.js" type="text/javascript"></script>
    <script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="assets/js/jquery.bootstrap.js" type="text/javascript"></script>
    <script src="assets/js/material-bootstrap-wizard.js"></script>
    <script src="assets/js/jquery.validate.min.js"></script>
</head>

<body>
    <div class="image-container set-full-height">
        <div class="container">
            <div class="row">
                <div class="col-sm-9 col-sm-offset-2">
                    <div class="wizard-container">
                        <div class="card wizard-card" data-color="flr" id="wizard">
                            <div class="wizard-header" style="margin-top:-100px;">
                                <img src="imagenes/invierno/HEADER_INVIERNO.jpg" width="100%"
                                    style="border-radius: 10px 10px 0 0;" alt="Fundación Las Rosas">
                            </div>

                            <div class="tab-content">
                                <form id="formVuele" name="formVuele" action="php/guarda_cuota_unica.php"
                                    method="post" onsubmit="document.getElementById('enviar').disabled = true;">
                                    <div class="row">
                                        <div class="col-sm-7 col-sm-offset-2">

                                            <input name="id" id="id" type="hidden" value="<?php echo e($id); ?>">
                                            <input name="rut" id="rut" type="hidden" value="<?php echo e($rut); ?>">

                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="material-icons">perm_identity</i>
                                                </span>
                                                <div class="form-group label-floating">
                                                    <input name="nombre" id="nombre" type="text"
                                                        value="<?php echo e($nombre); ?>" class="form-control" readonly>
                                                </div>
                                            </div>

                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="material-icons">attach_money</i>
                                                </span>
                                                <div class="form-group label-floating">
                                                    <label class="control-label">Monto de tu donación</label>
                                                    <input id="monto" name="monto" type="number"
                                                        value="<?php echo e($monto); ?>" class="form-control"
                                                        min="1" step="1" autofocus required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-sm-offset-1">
                                            <div style="float: right;">
                                                <button type="submit" class="btn btn-flr" name="enviar" id="enviar">
                                                    Acepto <i class="material-icons">touch_app</i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="info-text">
                                        *Al hacer clic en acepto, cargaremos a tu cuenta el monto a donar.
                                        <br>El cargo se hará efectivo <b>POR ÚNICA VEZ</b>.
                                    </div>

                                    <input id="utm_source" name="utm_source" type="hidden" value="<?php echo e($utm_source); ?>">
                                    <input id="utm_medium" name="utm_medium" type="hidden" value="<?php echo e($utm_medium); ?>">
                                    <input id="utm_campaign" name="utm_campaign" type="hidden" value="<?php echo e($utm_campaign); ?>">
                                    <input id="email" name="email" type="hidden" value="<?php echo e($email); ?>">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <div style="display: flex; justify-content: center;">
                <a class="btn-second" href="tel:800719711">800 719 711 <br> Opción 1<br> Mesa de ayuda</a>
                <a class="btn-fourth" href="tel:800719711">Llámanos <br> Opción 1
                    <i class="material-icons">touch_app</i>
                </a>
            </div>
            <div class="container text-center">
                Gracias <i class="fa fa-heart heart"></i>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#monto').on('focus input', function () {
                $('#enviar').prop('disabled', false);
            });
        });
    </script>
</body>

</html>