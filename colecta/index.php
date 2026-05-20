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

$amount1 = intval(getPortalConfig($db, 'donation_amount_1', '25000'));
$amount2 = intval(getPortalConfig($db, 'donation_amount_2', '50000'));
$amount3 = intval(getPortalConfig($db, 'donation_amount_3', '75000'));
$amount4 = intval(getPortalConfig($db, 'donation_amount_4', '100000'));

$buttonText1 = getPortalConfig($db, 'donation_text_1', '= 1 mt²');
$buttonText2 = getPortalConfig($db, 'donation_text_2', '= 2 mt²');
$buttonText3 = getPortalConfig($db, 'donation_text_3', '= 3 mt²');
$buttonText4 = getPortalConfig($db, 'donation_text_4', '= 4 mt²');

$buttonTextEnabled1 = getPortalConfig($db, 'donation_text_enabled_1', '1');
$buttonTextEnabled2 = getPortalConfig($db, 'donation_text_enabled_2', '1');
$buttonTextEnabled3 = getPortalConfig($db, 'donation_text_enabled_3', '1');
$buttonTextEnabled4 = getPortalConfig($db, 'donation_text_enabled_4', '1');

$utm_source = isset($_GET["utm_source"]) ? $_GET["utm_source"] : "organico";
$utm_medium = isset($_GET["utm_medium"]) ? $_GET["utm_medium"] : "organico";
$utm_campaign = isset($_GET["utm_campaign"]) ? $_GET["utm_campaign"] : "organico";

$miip = get_client_ip();
$ip_array = ip_info($miip, 'location');

$ip_ciudad = isset($ip_array['city']) ? $ip_array['city'] : '';
$ip_region = isset($ip_array['state']) ? $ip_array['state'] : '';
$ip_pais = isset($ip_array['country']) ? $ip_array['country'] : '';
$ip_latitud = isset($ip_array['latitude']) ? $ip_array['latitude'] : '';
$ip_longitud = isset($ip_array['longitude']) ? $ip_array['longitude'] : '';
?>

<!doctype html>

<html lang="es">

<head>
  <meta name="facebook-domain-verification" content="ljipukhxyz823ib3ee3ukd9446a1op" />
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Fundación Las Rosas - Dona Aquí</title>
  <meta name="Description" CONTENT="La colecta de Fundación Las Rosas.">
  <link rel="icon" href="https://www.fundacionlasrosas.cl/colecta/FLR_cuad.png">
  <meta property="og:title" content="Fundación Las Rosas" />
  <meta property="og:description" content="Colecta Nacional del 3 al 5 de octubre." />
  <meta property="og:url" content="https://www.fundacionlasrosas.cl/colecta/" />
  <meta property="og:image" content="<?php echo $baseUrl; ?>/images/fondo/colecta25.jpg" />
  <meta property="og:image:secure_url" content="<?php echo $baseUrl; ?>/images/fondo/colecta25.jpg" />
  <meta property="og:image:width" content="600" />
  <meta property="og:image:height" content="600" />
  <link rel="stylesheet" href="<?php echo asset('css/bootstrap.min.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/animate.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/owl.carousel.min.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/themify-icons.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/all.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/flaticon.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/magnific-popup.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/nice-select.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/slick.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/style_donacion.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/rrss.css'); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css">
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
  <header class="main_menu home_menu">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-12">
          <nav class="navbar navbar-expand-lg navbar-light">
            <a class="navbar-brand" href="."> <img src="images/logos/FLR_horTRANS.png" alt="logo" style="width:200px">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
              aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="ti-menu"></span>
            </button>
            <div class="collapse navbar-collapse main-menu-item justify-content-end" id="navbarSupportedContent">
              <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                  <a class="nav-link"
                    href="https://widget.forpay.cl/sus/index.php?key=4dc5927dfd7fb4278315e222b081d01ev2">HAZTE AMIGO</a>
                </li>
                <li class="nav-item">
                  <!--
                                <a class="nav-link" href="#sec-monto">DONA AQUÍ</a>
-->
                </li>
              </ul>
            </div>
            <!--
<a href="https://www.fundacionlasrosas.cl/voluntarioscolecta/">
  <img src="images/botones/voluntarios.png" alt="volunatrio" style="margin-right:-20px;">
</a>
-->
          </nav>
        </div>
      </div>
    </div>
  </header>
  <br><br>
  <section class="blog_part ">
    <div class="container" style="margin-top:70px;">
      <div class="row align-items-center justify-content-center">
        <div class="col-lg-6">
          <div class="banner_text text-center">
            <div class="banner_text_iner" style="margin-top:-20px;">
              <table width="400" border="0">
                <!-- <tr>
                                    <td></td>
                                    <td valign="center"><h4 align="left" style="color:#af0a3d;margin-top:30px;">Llevamos $<?php echo number_format($donaciones->getTotal(), 0, ',', '.'); ?> </h4></td>
                                  </tr>-->
              </table>
              <div style="margin-left:auto;margin-right:auto;">
                <a href="#sec-monto">
                  <img src="<?php echo htmlspecialchars($indexImage, ENT_QUOTES, 'UTF-8'); ?>"
                    style="width:400px; border-radius: 10px;">
                </a>
              </div>
            </div>
          </div>
        </div>
        <div id="sec-monto" class="mueve-foto"></div>
        <div class="col-lg-5">
          <div class="single_event">
            <div class="banner_text text-center">
              <form id="formDonacion" name="formDonacion" method="post">
                <br>
                <h4 style="color:white;">Selecciona el monto que deseas donar:</h4>
                <section class="donation-options">
                  <div class="donation-option">
                    <input type="radio" id="control_01" name="select" value="<?php echo $amount1; ?>"
                      onclick="f_check()">
                    <label for="control_01" class="donation-card">
                      <span class="donation-price">
                        <small>$</small><?php echo number_format($amount1, 0, ',', '.'); ?>
                      </span>

                      <?php if ($buttonTextEnabled1 == '1' && trim($buttonText1) !== ''): ?>
                        <br>
                        <span class="donation-meters">
                          <?php echo htmlspecialchars($buttonText1, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                      <?php endif; ?>
                    </label>
                  </div>

                  <div class="donation-option">
                    <input type="radio" id="control_02" name="select" value="<?php echo $amount2; ?>"
                      onclick="f_check()">
                    <label for="control_02" class="donation-card">
                      <span class="donation-price">
                        <small>$</small><?php echo number_format($amount2, 0, ',', '.'); ?>
                      </span>

                      <?php if ($buttonTextEnabled2 == '1' && trim($buttonText2) !== ''): ?>
                        <br>
                        <span class="donation-meters">
                          <?php echo htmlspecialchars($buttonText2, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                      <?php endif; ?>
                    </label>
                  </div>

                  <div class="donation-option">
                    <input type="radio" id="control_03" name="select" value="<?php echo $amount3; ?>"
                      onclick="f_check()">
                    <label for="control_03" class="donation-card">
                      <span <span class="donation-price">
                        <small>$</small><?php echo number_format($amount3, 0, ',', '.'); ?>
                      </span>

                      <?php if ($buttonTextEnabled3 == '1' && trim($buttonText3) !== ''): ?>
                        <br>
                        <span class="donation-meters">
                          <?php echo htmlspecialchars($buttonText3, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                      <?php endif; ?>
                    </label>
                  </div>

                  <div class="donation-option">
                    <input type="radio" id="control_04" name="select" value="<?php echo $amount4; ?>"
                      onclick="f_check()">
                    <label for="control_04" class="donation-card donation-card-wide">
                      <span class="donation-price">
                        <small>$</small><?php echo number_format($amount4, 0, ',', '.'); ?>
                      </span>

                      <?php if ($buttonTextEnabled4 == '1' && trim($buttonText4) !== ''): ?>
                        <br>
                        <span class="donation-meters">
                          <?php echo htmlspecialchars($buttonText4, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                      <?php endif; ?>
                    </label>
                  </div>

                  <div class="donation-option">
                    <input type="radio" id="control_05" name="select" value="" onclick="f_check()">
                    <label for="control_05" class="donation-card donation-card-wide donation-card-other">
                      <span class="donation-other-title">Otro Monto</span>
                      <input type="text" value="" class="form-control" id="txt_otro" name="txt_otro">
                    </label>
                  </div>
                </section>

                <h4 style="color:white;">Métodos de donación:</h4>
                <input type="hidden" value="" class="form-control" id="donacion" name="donacion">
                <input id="id_institucion" name="id_institucion" type="hidden" value="0008" />
                <section>
                  <div onClick="submitForm('php/donacion_fintoc')">
                    <img src="images/botones/boton_fintoc_t.png" class="imagenes_boton">
                  </div>
                  <div
                    onClick="location.href='https://widget.forpay.cl/sus/index.php?key=4dc5927dfd7fb4278315e222b081d01ev2'">
                    <img src="images/botones/boton_donacion_mensual.jpg" class="imagenes_boton">
                  </div>
                </section>
                <section>
                  <!--  <div onClick="submitForm('php/donacion_bco_estado')">
                              <img src="images/botones/boton_bco_estado.png" class="imagenes_boton">
                            </div>-->
                  <div onClick="location.href='https://www.paypal.com/donate/?hosted_button_id=TEQSQGYCV48F8'">
                    <img src="images/botones/boton_paypal.png" class="imagenes_boton">
                  </div>
                  <div onClick="submitForm('php/donacion_mach')">
                    <img src="images/botones/boton_mach.png" class="imagenes_boton">
                  </div>
                  <div onClick="submitForm('php/donacion_webpay')">
                    <img src="images/botones/boton_webpay_n.png" class="imagenes_boton">
                  </div>
                  <!--  <div data-toggle="modal" data-target="#myModal2">
                            <img src="images/botones/boton_transferencia.png" class="imagenes_boton">
                          </div>-->
                  <input id="ip_transaccion" name="ip_transaccion" type="hidden"
                    value="<?php echo htmlspecialchars($miip, ENT_QUOTES, 'UTF-8'); ?>">
                  <input id="ip_ciudad" name="ip_ciudad" type="hidden"
                    value="<?php echo htmlspecialchars($ip_ciudad, ENT_QUOTES, 'UTF-8'); ?>">
                  <input id="ip_region" name="ip_region" type="hidden"
                    value="<?php echo htmlspecialchars($ip_region, ENT_QUOTES, 'UTF-8'); ?>">
                  <input id="ip_pais" name="ip_pais" type="hidden"
                    value="<?php echo htmlspecialchars($ip_pais, ENT_QUOTES, 'UTF-8'); ?>">
                  <input id="ip_latitud" name="ip_latitud" type="hidden"
                    value="<?php echo htmlspecialchars($ip_latitud, ENT_QUOTES, 'UTF-8'); ?>">
                  <input id="ip_longitud" name="ip_longitud" type="hidden"
                    value="<?php echo htmlspecialchars($ip_longitud, ENT_QUOTES, 'UTF-8'); ?>">
                  <input id="utm_source" name="utm_source" type="hidden"
                    value="<?php echo htmlspecialchars($utm_source, ENT_QUOTES, 'UTF-8'); ?>">
                  <input id="utm_medium" name="utm_medium" type="hidden"
                    value="<?php echo htmlspecialchars($utm_medium, ENT_QUOTES, 'UTF-8'); ?>">
                  <input id="utm_campaign" name="utm_campaign" type="hidden"
                    value="<?php echo htmlspecialchars($utm_campaign, ENT_QUOTES, 'UTF-8'); ?>">
              </form>
            </div>
          </div>
        </div>
        <div id="sec-dona" class="mueve"></div>
      </div>
    </div>
  </section>
  <!-- <section class="video_seccion" style="margin-top:100px;">
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
  <!-- Modal -->
  <?php include('modal_medios_pago.php'); ?>
  <?php include('footer_menu.php'); ?>

  <script src="<?php echo asset('js/jquery-1.12.1.min.js'); ?>"></script>
  <script src="<?php echo asset('js/popper.min.js'); ?>"></script>
  <script src="<?php echo asset('js/bootstrap.min.js'); ?>"></script>
  <script src="<?php echo asset('js/jquery.magnific-popup.js'); ?>"></script>
  <script src="<?php echo asset('js/swiper.min.js'); ?>"></script>
  <script src="<?php echo asset('js/wow.min.js'); ?>"></script>
  <script src="<?php echo asset('js/jquery.smooth-scroll.min.js'); ?>"></script>
  <script src="<?php echo asset('js/masonry.pkgd.js'); ?>"></script>
  <script src="<?php echo asset('js/owl.carousel.min.js'); ?>"></script>
  <script src="<?php echo asset('js/jquery.nice-select.min.js'); ?>"></script>
  <script src="<?php echo asset('js/slick.min.js'); ?>"></script>
  <script src="<?php echo asset('js/jquery.counterup.min.js'); ?>"></script>
  <script src="<?php echo asset('js/waypoints.min.js'); ?>"></script>
  <script src="<?php echo asset('js/countdown.jquery.min.js'); ?>"></script>
  <script src="<?php echo asset('js/jquery.ajaxchimp.min.js'); ?>"></script>
  <script src="<?php echo asset('js/jquery.form.js'); ?>"></script>
  <script src="<?php echo asset('js/jquery.validate.min.js'); ?>"></script>
  <script src="<?php echo asset('js/mail-script.js'); ?>"></script>
  <script src="<?php echo asset('js/contact.js'); ?>"></script>
  <script src="<?php echo asset('js/custom.js'); ?>"></script>
  <script src="<?php echo asset('js/funciones/control_check.js'); ?>"></script>
</body>

</html>