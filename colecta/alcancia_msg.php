<!doctype html>

<html lang="en">
<?php
require_once __DIR__ . '/includes/helpers.php';
include('php/fip.php');
require("conexion/configuracion.php");
require_once 'class/class_alcancia.php';

$keys = ['colegio', 'empresa', 'codigo', 'c', 'i', 'comunidad'];
$codigo = '';

foreach ($keys as $key) {
    if (isset($_GET[$key])) {
        $codigo = $_GET[$key];
        break;
    }
}

$trato = 'Alcancía digital <br> ';
$alcancia = new Alcancia($db, $codigo);
$utm_source = $codigo;
$utm_medium = $alcancia->getTipo();
$utm_campaign = "alcancias_digitales";
$miip = get_client_ip();
$ip_array = ip_info($miip, 'location');
$ip_ciudad = $ip_array['city'];
$ip_region = $ip_array['state'];
$ip_pais = $ip_array['country'];
$ip_latitud = $ip_array['latitude'];
$ip_longitud = $ip_array['longitude'];

?>

<head>
    <!-- Required meta tags -->
    <meta name="facebook-domain-verification" content="ljipukhxyz823ib3ee3ukd9446a1op" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Alcancía digital - Colecta Fundación Las Rosas</title>
    <meta property="og:title" content="Colecta Fundación Las Rosas" />
    <meta property="og:description" content="Alcancia digital: <? echo $alcancia->getNombre(); ?>" />
    <meta property="og:url" content="https://www.fundacionlasrosas.cl/colecta/" />
    <meta property="og:image" content="<?php echo $baseUrl; ?>/images/banner-colecta2023.jpg" />
    <meta property="og:image:secure_url"
        content="<?php echo $baseUrl; ?>/images/banner-colecta2023.jpg" />
    <meta property="og:image:width" content="600" />
    <meta property="og:image:height" content="600" />
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/themify-icons.css">
    <link rel="stylesheet" href="css/all.css">
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/nice-select.css">
    <link rel="stylesheet" href="css/slick.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style_donacion.css">
    <link rel="stylesheet" href="css/rrss.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css'>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-113984126-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'UA-113984126-1');
    </script>
    <!-- Meta Pixel Code -->
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
        fbq('init', '303848330602578');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=303848330602578&ev=PageView&noscript=1" /></noscript>
    <!-- End Meta Pixel Code -->
</head>

<body>
    <!--::header part start::-->
    <?php include('header_menu.php'); ?>

    <!-- Header part start-->

    <header class="main_menu home_menu">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <nav class="navbar navbar-expand-lg navbar-light">
                        <a class="navbar-brand" href=".">
                            <img src="images/logos/FLR_horTRANS.png" alt="logo" style="width:200px">
                        </a>
                        <button class="navbar-toggler" type="button" data-toggle="collapse"
                            data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                            aria-expanded="false" aria-label="Toggle navigation">
                            <span class="ti-menu"></span>
                        </button>
                        <div class="collapse navbar-collapse main-menu-item justify-content-end"
                            id="navbarSupportedContent">
                            <ul class="navbar-nav align-items-center">
                                <li class="nav-item">
                                    <a class="nav-link"
                                        href="https://widget.forpay.cl/sus/index.php?key=4dc5927dfd7fb4278315e222b081d01ev2">HAZTE
                                        AMIGO</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#sec-monto">DONA AQUÍ</a>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    breadcrumb start

    <section class="blog_part ">
        <div class="container" style="margin-top:90px;">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-5">
                    <div class="banner_text text-center">
                        <div class="banner_text_iner">
                            <div style="max-width:400px; margin-left:auto;margin-right:auto;">
                                <table width="100%" border="0">
                                    <tr>
                                        <td rowspan="2">
                                            <a href="https://api.whatsapp.com/send?text=Te%20invito%20a%20ayudar%20a%20más%20de%202.000%20adultos%20mayores%20👴🏻👵🏻%20en%20*La%20Colecta%20de%20Fundación%20Las%20Rosas*%20apóyanos%20donando%20en%20el%20siguiente%20link:%20%20https%3A//fundacionlasrosas.cl/colecta/alcancia?c=<?php echo $codigo; ?> "
                                                style="color:#22355D;font-size: 10px;">
                                                <img src='imagen/iconos/whatsapp-circle.png' style="width:40px;">
                                                Compartir alcancía
                                            </a>
                                        </td>
                                        <td valign="center"><br>
                                            <h4 style="color:#22355D;font-size: 14px;">
                                                <?php echo $trato . " " . $alcancia->getNombre(); ?>
                                            </h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td valign="top">
                                            <h4 style="color:#22355D;font-size: 15px;">Total recaudado :
                                                $<?php echo number_format($alcancia->getTotal(), 0, ',', '.'); ?></h4>
                                        </td>
                                    </tr>
                                </table>
                                <style>
                                    /*body {*/
                                    /*    font-family: 'Arial', sans-serif;*/
                                    /*    background-color: #f8f8f8;*/
                                    /*    text-align: center;*/
                                    /*    margin: 0;*/
                                    /*    padding: 0;*/
                                    /*}*/
                                    /*header {*/
                                    /*    background-color: #ee3e57;*/
                                    /*    padding: 20px;*/
                                    /*    color: #fff;*/
                                    /*}*/
                                    /*h1 {*/
                                    /*    margin-bottom: 20px;*/
                                    /*}*/
                                    p {
                                        font-size: 18px;
                                        line-height: 1.6;
                                        color: #333;
                                    }

                                    .container1 {
                                        width: 800px;
                                        margin: 20px auto;
                                        background-color: #fff;
                                        padding: 20px;
                                        border-radius: 8px;
                                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                                    }

                                    .btn {
                                        display: inline-block;
                                        padding: 10px 20px;
                                        background-color: #7d0d1d;
                                        color: #fff;
                                        text-decoration: none;
                                        border-radius: 5px;
                                        font-weight: bold;
                                    }

                                    @media screen and (max-width: 600px) {
                                        .container1 {
                                            max-width: 100%;
                                        }
                                    }
                                </style>
                                <div class="container1">
                                    <p>Querido/a <?php echo $trato . " " . $alcancia->getNombre(); ?></p>
                                    <p>Queremos expresar nuestro más sincero agradecimiento por tu valioso aporte a
                                        nuestra Campaña de Colecta de Fondos de Fundación Las Rosas. Tu generosidad nos
                                        ayuda a llevar adelante nuestra misión de hacer del mundo un lugar mejor.</p>
                                    <p>Gracias a personas como tú, podemos continuar brindando apoyo y mejorando la
                                        calidad de vida de aquellos que más lo necesitan. Tu solidaridad nos inspira a
                                        seguir trabajando con dedicación y compromiso.</p>
                                    <p>¡Gracias por ser parte de este noble esfuerzo!</p>
                                    <p>Con gratitud,</p>
                                    <p>Fundación Las Rosas</p><br>
                                    <a href="https://www.fundacionlasrosas.cl/" class="btn">Visita nuestro sitio web</a>
                                    <br>
                                    <br>
                                </div>
                                <!--<a href="#sec-monto"><img src="imagen/colecta2023.jpg" style="width:400px;  border-radius: 10px;"></a>-->
                            </div>
                        </div>
                    </div>
                </div>
                <div id="sec-monto" class="mueve-foto"></div>
                <div class="col-lg-5" style="margin-top:-20px;">
                    <div class="single_event">
                        <div class="banner_text text-center">
                            <!--<form id="formDonacion" name="formDonacion" method="post" >-->
                            <!--    <br><br>-->
                            <!--    <h4 style="color:#22355D;">Selecciona el monto que deseas donar:</h4>-->
                            <!--<section>-->
                            <!--    <div>-->
                            <!--        <input type="radio" id="control_01" name="select" value="5000" onclick="f_check()">-->
                            <!--        <label for="control_01">-->
                            <!--            <div class="titulos">$5.000</div>-->
                            <!--        </label>-->
                            <!--    </div>-->
                            <!--    <div>-->
                            <!--        <input type="radio" id="control_02" name="select" value="10000" onclick="f_check()">-->
                            <!--        <label for="control_02">-->
                            <!--            <div class="titulos">$10.000  </div>-->
                            <!--        </label>-->
                            <!--    </div>-->
                            <!--    <div>-->
                            <!--        <input type="radio" id="control_03" name="select" value="30000" onclick="f_check()">-->
                            <!--        <label for="control_03">-->
                            <!--            <div class="titulos">$30.000  </div>-->
                            <!--        </label>-->
                            <!--    </div>-->
                            <!--    <div>-->
                            <!--        <input type="radio" id="control_04" name="select" value="50000" onclick="f_check()" >-->
                            <!--        <label for="control_04">-->
                            <!--            <div class="titulos">$50.000  </div>-->
                            <!--        </label>-->
                            <!--    </div>-->
                            <!--    <div>-->
                            <!--        <input type="radio" id="control_05" name="select" value="" onclick="f_check()">-->
                            <!--        <label for="control_05">-->
                            <!--            <div class="titulos">Otro monto  </div>-->
                            <!--            <input type="text" value="" class="form-control" id="txt_otro" name="txt_otro">-->
                            <!--        </label>-->
                            <!--    </div>-->
                            <!--</section>-->
                            <!--  <br>-->
                            <!--  <h4 style="color:#22355D;">Metodos de donación :</h4>-->
                            <!--  <input type="text" value="" class="form-control" id="donacion" name="donacion" hidden  >-->
                            <!--  <input id="id_institucion" name="id_institucion" type="hidden" value="0008" />-->
                            <!--  <section>-->
                            <!--      <div onClick="submitForm('php/donacion_webpay')">-->
                            <!--          <img src="images/botones/boton_webpay_n.png" class="imagenes_boton">-->
                            <!--      </div>-->
                            <!--      <div onClick="submitForm('php/donacion_mach')">-->
                            <!--          <img src="images/botones/boton_mach.png" class="imagenes_boton">-->
                            <!--      </div>-->
                            <!--      <div onClick="submitForm('php/donacion_fintoc')">-->
                            <!--  <img src="images/botones/boton_fintoc_t.png" class="imagenes_boton">-->
                            <!--</div>-->
                            <!--     <div data-toggle="modal" data-target="#myModal2">-->
                            <!--          <img src="images/botones/boton_transferencia.png" class="imagenes_boton">-->
                            <!--      </div> -->
                            <!--      <input id="ip_transaccion" name="ip_transaccion" type="text" value="<?php echo $miip ?>" hidden>-->
                            <!--      <input id="ip_ciudad" name="ip_ciudad" type="text" value="<?php echo $ip_ciudad ?>" hidden>-->
                            <!--      <input id="ip_region" name="ip_region" type="text" value="<?php echo $ip_region ?>" hidden>-->
                            <!--      <input id="ip_pais" name="ip_pais" type="text" value="<?php echo $ip_pais ?>" hidden>-->
                            <!--      <input id="ip_latitud" name="ip_latitud" type="text" value="<?php echo $ip_latitud ?>" hidden>-->
                            <!--      <input id="ip_longitud" name="ip_longitud" type="text" value="<?php echo $ip_longitud ?>" hidden>-->
                            <!--      <input id="utm_source" name="utm_source" type="text" value="<?php echo $utm_source ?>" hidden>-->
                            <!--      <input id="utm_medium" name="utm_medium" type="text" value="<?php echo $utm_medium ?>" hidden>-->
                            <!--      <input id="utm_campaign" name="utm_campaign" type="text" value="<?php echo $utm_campaign ?>" hidden>-->
                            <!--  </form>-->
    </section>
    </div>
    </div>
    <div style="font-size:14px;">
        <br>
        <br>Tu aporte es muy valioso para miles de adultos mayores.
        <br><br>
    </div>
    </div>
    <div id="sec-dona" class="mueve"></div>
    </div>
    </div>
    </section>
    <!--<section class="video_seccion" style="margin-top:100px;">
          <div class="container">
              <div class="row align-items-center justify-content-center">
                  <div>
                        <div class="banner_video" >
                          <div class="banner_video_iner">
                              <div class="extends_video">
                                  <a id="play-video_1" class="video-play-button popup-youtube"
                                      href="https://www.youtube.com/watch?v=SI5iCBIFW5s">
                                      <img src="images/youtube/preview_videos_flr.jpg" alt="" style="border-radius: 15px;">
                                  </a>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </section>-->

    <?php include('modal_medios_pago.php'); ?>
    <?php include('footer_menu.php'); ?>
    <!--::footer_part end::-->
    <!-- jquery plugins here-->
    <script src="<?php echo asset('js/core/jquery-1.12.1.min.js'); ?>"></script>
    <script src="<?php echo asset('js/core/bootstrap.min.js'); ?>"></script>
    <script src="js/jquery.magnific-popup.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/jquery.smooth-scroll.min.js"></script>
    <script src="<?php echo asset('js/core/masonry.pkgd.js'); ?>"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.nice-select.min.js"></script>
    <script src="js/slick.min.js"></script>
    <script src="js/jquery.counterup.min.js"></script>
    <script src="js/waypoints.min.js"></script>
    <script src="js/countdown.jquery.min.js"></script>
    <script src="js/jquery.ajaxchimp.min.js"></script>
    <script src="js/jquery.form.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <script src="js/mail-script.js"></script>
    <script src="js/contact.js"></script>
    <script src="<?php echo asset('js/core/custom.js'); ?>"></script>
    <script src="<?php echo asset('js/core/control_check.js'); ?>"></script>
</body>

</html>