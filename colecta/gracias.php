<?php
require_once __DIR__ . '/includes/helpers.php';
include('php/fip.php');
require("conexion/configuracion.php");
require_once 'class/class_donacion.php';

$donaciones = new Donaciones($db);
function getPortalConfig($db, $key, $default)
{
    $value = null;

    $stmt = $db->prepare("SELECT config_value FROM portal_config WHERE config_key = ? LIMIT 1");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $stmt->bind_result($value);

    if ($stmt->fetch()) {
        $stmt->close();
        return $value;
    }

    $stmt->close();
    return $default;
}

$indexImage = getPortalConfig($db, 'index_image', 'images/imgMayo.jpg');

if ($_POST) {
    if (isset($_POST["monto"])) {
        $monto = $_POST["monto"];
    } else {
        $monto = "error";
    }
    if (isset($_POST["id"])) {
        $cod = $_POST["id"];
    } else {
        $cod = "error";
    }
}
?>

<!doctype html>

<html lang="es">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Fundación Las Rosas - Gracias</title>
    <meta name="Description" CONTENT="La colecta de Fundación Las Rosas.">
    <link rel="icon" href="https://www.fundacionlasrosas.cl/colecta/FLR_cuad.png">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/bootstrap.min.css'); ?>">
    <!-- animate CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/animate.css'); ?>">
    <!-- owl carousel CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/owl.carousel.min.css'); ?>">
    <!-- themify CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/themify-icons.css'); ?>">
    <!-- font awesome CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/all.css'); ?>">
    <!-- flaticon CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/flaticon.css'); ?>">
    <!-- magnific popup CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/magnific-popup.css'); ?>">
    <!-- nice select CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/nice-select.css'); ?>">
    <!-- swiper CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/slick.css'); ?>">
    <!-- style CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/rrss.css">
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-172008020-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'UA-172008020-1');
    </script>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-V4SY41BEQC"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-V4SY41BEQC');
    </script>
    <!-- Facebook Pixel Code -->
    <script>
        !function (f, b, e, v, n, t, s) {
            if (f.fbq) return; n = f.fbq = function () {
                n.callMethod ?
                    n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0';
            n.queue = []; t = b.createElement(e); t.async = !0;
            t.src = v; s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        }(window, document, 'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '527815275258788');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=527815275258788&ev=PageView&noscript=1" /></noscript>
    <!-- End Facebook Pixel Code -->
    <script>
        // Función para validar un RUT chileno
        function validarRut(rut) {
            rut = rut.replace(/\./g, ""); // Eliminar puntos
            rut = rut.replace(/-/g, ""); // Eliminar guiones
            var dv = rut.charAt(rut.length - 1).toUpperCase(); // Obtener el dígito verificador
            var rutSinDv = rut.substr(0, rut.length - 1); // Obtener el RUT sin el dígito verificador
            var suma = 0;
            var multiplo = 2;
            // Recorrer el RUT de derecha a izquierda y calcular la suma ponderada
            for (var i = rutSinDv.length - 1; i >= 0; i--) {
                suma += parseInt(rutSinDv.charAt(i)) * multiplo;
                multiplo = multiplo === 7 ? 2 : multiplo + 1;
            }

            // Calcular el dígito verificador esperado
            var dvEsperado = 11 - (suma % 11);
            // Comparar el dígito verificador ingresado con el esperado
            if (dvEsperado === 11) dvEsperado = 0;
            if (dvEsperado === 10) dvEsperado = "K";
            if (dvEsperado.toString() === dv) {
                return true; // RUT válido
            } else {
                return false; // RUT inválido
            }
        }

        function validateForm() {
            var nombre = document.getElementById("nombre").value;
            var rut = document.getElementById("rut").value;
            var email = document.getElementById("email").value;
            var telefono = document.getElementById("telefono").value;

            if (nombre === "" || email === "" || telefono === "" || rut === "") {
                alert("Por favor, completa todos los campos antes de enviar.");
                return false; // Evita que se envíe el formulario si faltan campos
            }
            if (!validarRut(rut)) {
                alert("El RUT ingresado no es válido.");
                return false; // Evita que se envíe el formulario si el RUT es inválido
            }
            // Si todos los campos están completos y el RUT es válido, muestra el mensaje de alerta
            showAlert();
            return true;
        }

        function showAlert() {
            var email = document.getElementById("email").value;
            alert("Los datos fueron enviados con éxito. Recibirás un correo en la dirección: " + email);
        }

        // Función para formatear el RUT al salir de la caja de texto
        function formatearRut() {
            var rut = document.getElementById("rut").value;
            var rutFormateado = "";
            rut = rut.replace(/\./g, ""); // Eliminar puntos
            rut = rut.replace(/-/g, ""); // Eliminar guiones
            if (rut.length > 1) {
                rutFormateado += rut.slice(0, -1).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.") + "-" + rut.slice(-1);
                document.getElementById("rut").value = rutFormateado;
            }
        }
    </script>
</head>

<body>
    <!--::header part start::-->
    <?php include('header_menu.php'); ?>
    <!-- Header part end-->
    <!-- breadcrumb start-->
    <section class="blog_part ">
        <div class="container" style="margin-top:20px;">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-5">
                    <div class="banner_text text-center">
                        <section>
                            <div class="container">
                                <div class="row align-items-center justify-content-center">
                                    <div><br>
                                        <img src="<?php echo htmlspecialchars($indexImage, ENT_QUOTES, 'UTF-8'); ?>"
                                            style="width:300px;  border-radius: 10px;"><br><br>
                                        <h4 class="info-text">¡Muchas gracias por tu aporte! <i
                                                class="fa fa-heart heart" style="color:#FF0000" ;></i></h4>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <?php
                        echo '<br><h4>Detallamos tu donación :</h4>
                        <p>Código donación: ' . $cod . ' <br>
                        Monto de donación: $' . number_format($monto, 0, ',', '.') .
                            '</p><br>';
                        ?>
                    </div>
                </div>
                <div class="col-lg-5" style="margin-top:10px;margin-left:20px;">
                    <div class="single_event">
                        <div class="banner_text">
                            <h3>Llena tus datos para enviarte el comprobante de donación</h3><br>
                            <form style="font-family: 'Gotham Rounded';" id="formDonacion" name="formDonacion"
                                method="post" action="php/datos_donacion" onsubmit="return validateForm();">
                                <div class="form-group">
                                    <label for="nombre">Nombre y apellido</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre"
                                        placeholder="Ingresa tu nombre" required>
                                </div>
                                <div class="form-group">
                                    <label for="nombre">Rut</label>
                                    <input type="text" class="form-control" id="rut" name="rut"
                                        placeholder="Ingresa tu RUT" required onblur="formatearRut()">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        aria-describedby="emailHelp" placeholder="Ingresa tu email" required>
                                </div>
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="number" class="form-control" id="telefono" name="telefono"
                                        placeholder="Ingresa tu teléfono 9 dígitos" required min="100000000"
                                        max="999999999">
                                </div>
                                <input id="cod" name="cod" type="text" value="<?php echo $cod ?>" hidden>
                                <input id="monto" name="monto" type="text" value="<?php echo $monto ?>" hidden>
                                <button type="submit" class="btn btn-primary">Enviar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section>
        <br><br>
    </section>
    <!-- breadcrumb start-->
    <!--::footer_part start::-->
    <?php include('footer_menu.php'); ?>
    <!--::footer_part end::-->
    <script src="<?php echo asset('js/jquery-1.12.1.min.js'); ?>"></script>
    <!-- popper js -->
    <script src="<?php echo asset('js/popper.min.js'); ?>"></script>
    <!-- bootstrap js -->
    <script src="<?php echo asset('js/bootstrap.min.js'); ?>"></script>
    <!-- easing js -->
    <script src="<?php echo asset('js/jquery.magnific-popup.js'); ?>"></script>
    <!-- swiper js -->
    <script src="<?php echo asset('js/swiper.min.js'); ?>"></script>
    <script src="<?php echo asset('js/wow.min.js'); ?>"></script>
    <script src="<?php echo asset('js/jquery.smooth-scroll.min.js'); ?>"></script>
    <!-- masonry js -->
    <script src="<?php echo asset('js/masonry.pkgd.js'); ?>"></script>
    <!-- carousel js -->
    <script src="<?php echo asset('js/owl.carousel.min.js'); ?>"></script>
    <script src="<?php echo asset('js/jquery.nice-select.min.js'); ?>"></script>
    <script src="<?php echo asset('js/slick.min.js'); ?>"></script>
    <script src="<?php echo asset('js/jquery.counterup.min.js'); ?>"></script>
    <script src="<?php echo asset('js/waypoints.min.js'); ?>"></script>
    <script src="<?php echo asset('js/countdown.jquery.min.js'); ?>"></script>
    <!-- contact js -->
    <script src="<?php echo asset('js/jquery.ajaxchimp.min.js'); ?>"></script>
    <script src="<?php echo asset('js/jquery.form.js'); ?>"></script>
    <script src="<?php echo asset('js/jquery.validate.min.js'); ?>"></script>
    <script src="<?php echo asset('js/mail-script.js'); ?>"></script>
    <script src="<?php echo asset('js/contact.js'); ?>"></script>
    <!-- custom js -->
    <script src="<?php echo asset('js/custom.js'); ?>"></script>
</body>

</html>