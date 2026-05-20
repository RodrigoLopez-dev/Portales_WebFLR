<!DOCTYPE html>

<?php 

include('php/fip.php');

?>

<html>



<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Donaciones</title>



    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

    <link href="https://fonts.googleapis.com/css?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css2/magnific-popup.css">

    <link rel="stylesheet" href="css2/animate.css">

    <!--<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css'>-->

    <script src="js/funciones.js"></script>

    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css'>

    <!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">-->







    <?php include('css/styles.html'); ?>

    <!-- Google Tag Manager -->

    <script>(function (w, d, s, l, i) {

            w[l] = w[l] || []; w[l].push({

                'gtm.start':

                    new Date().getTime(), event: 'gtm.js'

            }); var f = d.getElementsByTagName(s)[0],

                j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =

                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);

        })(window, document, 'script', 'dataLayer', 'GTM-KHMCJBPH');</script>

    <!-- End Google Tag Manager -->



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

        fbq('init', '3773397849539419');

        fbq('track', 'PageView');

    </script>

    <noscript><img height="1" width="1" style="display:none"

            src="https://www.facebook.com/tr?id=3773397849539419&ev=PageView&noscript=1" /></noscript>

    <!-- End Meta Pixel Code -->

</head>

<?php

if(isset($_GET["utm_source"])){$utm_source=$_GET["utm_source"];}else{$utm_source="Matrimonio";}
if(isset($_GET["utm_medium"])){$utm_medium=$_GET["utm_medium"];}else{$utm_medium="organico";}
if(isset($_GET["utm_campaign"])){$utm_campaign=$_GET["utm_campaign"];}else{$utm_campaign="Matrimonio";}

$miip=get_client_ip();

$ip_array=ip_info($miip,'location');



//print_r($ip_array);



// $ip_ciudad = is_array($ip_array) ? ($ip_array['city'] ?? '') : '';

// $ip_region = is_array($ip_array) ? ($ip_array['state'] ?? '') : '';

// $ip_pais = is_array($ip_array) ? ($ip_array['country'] ?? '') : '';

// $ip_latitud = is_array($ip_array) ? ($ip_array['latitude'] ?? '') : '';

// $ip_longitud = is_array($ip_array) ? ($ip_array['longitude'] ?? '') : '';


$ip_ciudad   = (is_array($ip_array) && isset($ip_array['city'])) ? $ip_array['city'] : '';
$ip_region   = (is_array($ip_array) && isset($ip_array['state'])) ? $ip_array['state'] : '';
$ip_pais     = (is_array($ip_array) && isset($ip_array['country'])) ? $ip_array['country'] : '';
$ip_latitud  = (is_array($ip_array) && isset($ip_array['latitude'])) ? $ip_array['latitude'] : '';
$ip_longitud = (is_array($ip_array) && isset($ip_array['longitude'])) ? $ip_array['longitude'] : '';


?>



<body id="fondo-invierno">

    <!-- Google Tag Manager (noscript) -->

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KHMCJBPH" height="0" width="0"

            style="display:none;visibility:hidden"></iframe></noscript>

    <!-- End Google Tag Manager (noscript) -->



    <nav class="menu-container">

        <div class="logo">

            <img src="imagenes/logos/FLR_horTRANS.png" alt="Logo">

        </div>

        <button id="menuToggle" class="menu-toggle">&#9776;</button> <!-- Botón hamburguesa -->

        <ul class="menu-options">

            <li><b><a href="https://fundacionlasrosas.cl" class="titulos-inicio">Inicio</a></b></li>

            <li><b><a href="https://widget.forpay.cl/sus/index.php?key=4dc5927dfd7fb4278315e222b081d01ev2"

                        class="titulos-inicio">Hazte Amigo</a></b></li>

            <li>

                <b><a href="https://www.paypal.com/donate/?hosted_button_id=TEQSQGYCV48F8">

                        <img src="imagenes/botones/boton_paypal.png" alt="Donar en Dólares"

                            style="width:100px;height:auto;border-radius: 5px;">

                    </a></b>

            </li>



        </ul>

    </nav>











<!--<div id="fondo-invierno"></div>-->

<div class="container">



        <div class="row">

            

           <!-- <div class="col-md-6" style="text-align: center;">



            <img src="imagenes/fondo/regalos_de_matrimonio/fondo_lnding.png" alt="Imagen" id="img-left">



            </div>-->

            <!-- Imagen para móviles -->

            <div class="col-md-6 d-block d-md-none" style="text-align: center;">

                <br>

                <br>

                 <br>

                <br>

                

             

                <div class="titulo-mobile-wrapper">
                    <img src="imagenes/fondo/regalos_de_matrimonio/PNG_matrimonio_mobile.png" alt="Imagen" id="img-left"
     style="opacity: 0.9; filter: drop-shadow(3px 3px 8px rgba(0,0,0,0.7));">
                </div>
     <!--img src="imagenes/fondo/regalos_de_matrimonio/contenido_caja.png" alt="Imagen" id="img-left"

     style="opacity: 0.9; filter: drop-shadow(3px 3px 8px rgba(0,0,0,0.7));">-->

                <p class="titulos-inicio text-center  text-md-start"><b>Selecciona tu aporte:

                    </b></p>







                <div class="row donation-grid-mobile">

                    <!--<div class="col-sm-4">-->

                    <!--    <button class="donation" value="5000" onclick="scrollToElement('metodoPago')">-->

                    <!--$5.000 <br>-->

                    <!--        <span class="donation-description" onclick="scrollToElement('metodoPago')"></span>-->

                    <!--    </button>-->

                    <!--</div>-->

                    <div class="col-6 donation-wrapper">

                        <button class="donation donation-code" value="60000" onclick="scrollToElement('metodoPagoM')">

                            <span class="donation-code-icon" aria-hidden="true"><img src="imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png" alt="" class="donation-gift-icon"></span>
                            <span class="donation-code-main">$60.000</span>

                        </button>
                        <span class="donation-code-sub"></span>

                    </div>





                    <!--<div class="col-sm-4">-->

                    <!--    <button class="donation" value="10000" onclick="scrollToElement('metodoPago')">-->

                    <!--        $10.000 <br> -->

                    <!--        <span class="donation-description"></span>-->

                    <!--    </button>-->

                    <!--</div>-->

                    <div class="col-6 donation-wrapper">

                        <button class="donation donation-code" value="100000" onclick="scrollToElement('metodoPagoM')">

                            <span class="donation-code-icon" aria-hidden="true"><img src="imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png" alt="" class="donation-gift-icon"></span>
                            <span class="donation-code-main">$100.000</span>

                        </button>
                        <span class="donation-code-sub"></span>

                    </div>

                    <!--<div class="col-sm-4">-->

                    <!--    <button class="donation" value="20000" onclick="scrollToElement('metodoPago')">$20.000 <br>-->

                    <!--        <span class="donation-description"></span>-->

                    <!--    </button>-->

                    <!--</div>-->

                    <div class="col-6 donation-wrapper">

                        <button class="donation donation-code" value="150000" onclick="scrollToElement('metodoPagoM')">

                            <span class="donation-code-icon" aria-hidden="true"><img src="imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png" alt="" class="donation-gift-icon"></span>
                            <span class="donation-code-main">$150.000</span>

                        </button>
                        <span class="donation-code-sub"></span>

                    </div>



                        <div class="col-6 donation-wrapper">

                            <button class="donation donation-code" value="200000" onclick="scrollToElement('metodoPagoM')">

                                <span class="donation-code-icon" aria-hidden="true"><img src="imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png" alt="" class="donation-gift-icon"></span>
                                <span class="donation-code-main">$200.000</span>

                            </button>
                            <span class="donation-code-sub"></span>

                        </div>

                        <div class="col-12 donation-wrapper donation-wrapper-otro">

                            <button class="donation donation-code donation-otro" value="">
                                <span class="donation-code-icon" aria-hidden="true"><img src="imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png" alt="" class="donation-gift-icon"></span>
                                <span class="donation-code-main donation-code-main-otro">
                                    <span class="donation-otro-label">OTRO</span>
                                    <span class="donation-otro-input-wrap">
                                        <span class="donation-otro-currency">$</span>
                                        <input id="custom-amount" class="donation-otro-input" type="number" min="1" placeholder="Monto" onkeydown="noPuntoComa( event )">
                                    </span>
                                </span>
                            </button>
                            <span class="donation-code-sub donation-code-sub-otro">Tú decides cuánto amor regalar</span>

                        </div>

                </div>

                    <div id="payment-options">

                        <br>

                        <p class="titulos-inicio" id="metodoPagoM"><b>Selecciona un método de pago:</b></p>

                        <div class="payment-button-group">

                            <!--<button id="2" class="payment-button" style="background-image: url('imagenes/botones/boton_fintoc.png');"></button>-->

                            <button id="2" class="payment-button"><img alt="Fintoc Logo"

                                    src="https://assets.fintoc.com/?img_name=button_cl_light_trailing"> </button>

                                <button id="1" class="payment-button"

                                    style="background-image: url('imagenes/botones/boton_webpay.png');"></button>

                                <button id="3" class="payment-button"

                                    style="background-image: url('imagenes/botones/Mach_Movil.png');"></button>



                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!--<div class="container mt-5 d-none d-md-block">-->

        <div class="container d-none d-md-block pt-4" style="margin-top: -120px;">



        <!-- Imagen para escritorio -->



        <div class="desktop-donation-column col-md-8 offset-md-6 col-12 text-center" >
                    <img src="imagenes/fondo/regalos_de_matrimonio/PNG_matrimonio.png" alt="Imagen" class="img-fluid titulo-img">                       
           <div class="col-md-11 text-center">



                <p class="titulos-inicio text-center  text-md-start"><br> <b>Selecciona tu aporte:

                    </b>

                </p>





            <div class="col-md-12">

                <div class="row ">

                    <!--<div class="col-sm-4">-->

                    <!--    <button class="donation" value="5000" onclick="scrollToElement('metodoPago')">-->

                    <!--$5.000 <br>-->

                    <!--        <span class="donation-description" onclick="scrollToElement('metodoPago')"></span>-->

                    <!--    </button>-->

                    <!--</div>-->

                  <div class="col-sm-4 donation-wrapper">

                        <button class="donation donation-code" value="60000" onclick="scrollToElement('metodoPagoE')">

                            <span class="donation-code-icon" aria-hidden="true"><img src="imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png" alt="" class="donation-gift-icon"></span>
                            <span class="donation-code-main">$60.000</span>

                        </button>
                        <span class="donation-code-sub"></span>
                    </div>





                    <!--<div class="col-sm-4">-->

                    <!--    <button class="donation" value="10000" onclick="scrollToElement('metodoPago')">-->

                    <!--        $10.000 <br> -->

                    <!--        <span class="donation-description"></span>-->

                    <!--    </button>-->

                    <!--</div>-->

                    <div class="col-sm-4 ">

                        <button class="donation donation-code" value="100000" onclick="scrollToElement('metodoPagoE')">

                            <span class="donation-code-icon" aria-hidden="true"><img src="imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png" alt="" class="donation-gift-icon"></span>
                            <span class="donation-code-main">$100.000</span>

                        </button>
                        <span class="donation-code-sub"></span>

                    </div>

                    <!--<div class="col-sm-4">-->

                    <!--    <button class="donation" value="20000" onclick="scrollToElement('metodoPago')">$20.000 <br>-->

                    <!--        <span class="donation-description"></span>-->

                    <!--    </button>-->

                    <!--</div>-->

                    <div class="col-sm-4 ">

                        <button class="donation donation-code" value="150000" onclick="scrollToElement('metodoPagoE')">

                            <span class="donation-code-icon" aria-hidden="true"><img src="imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png" alt="" class="donation-gift-icon"></span>
                            <span class="donation-code-main">$150.000</span>

                        </button>
                        <span class="donation-code-sub"></span>

                    </div>



                    <div class="row ">

                        <!--<div class="col-sm-6">-->

                        <!--    <button class="donation" value="30000" onclick="scrollToElement('metodoPago')">$30.000 <br>-->

                        <!--        <span class="donation-description"></span>-->

                        <!--    </button>-->

                        <!--</div>-->

                        <div class="col-sm-2  ">

                            <!--<button class="donation" value="50000" onclick="scrollToElement('metodoPago')">-->

                            <!--    <img src="imagenes/botones/50000.png" alt="$50.000" class="donation-img" width="90%" >-->

                            <!--</button>-->

                        </div>

                        <div class="col-sm-5  ">

                            <button class="donation donation-code" value="200000" onclick="scrollToElement('metodoPagoE')">

                                <span class="donation-code-icon" aria-hidden="true"><img src="imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png" alt="" class="donation-gift-icon"></span>
                                <span class="donation-code-main">$200.000</span>

                            </button>
                            <span class="donation-code-sub"></span>

                        </div>

                        <div class="col-sm-5" style="align-content: left">

                            <button class="donation donation-code donation-otro" value="">
                                <span class="donation-code-icon" aria-hidden="true"><img src="imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png" alt="" class="donation-gift-icon"></span>
                                <span class="donation-code-main donation-code-main-otro">
                                    <span class="donation-otro-label">OTRO</span>
                                    <span class="donation-otro-input-wrap">
                                        <span class="donation-otro-currency">$</span>
                                        <input id="custom-amount2" class="donation-otro-input" type="number" min="1" placeholder="Monto" onkeydown="noPuntoComa( event )">
                                    </span>
                                </span>
                            </button>
                            <span class="donation-code-sub donation-code-sub-otro">Tú decides cuánto amor regalar</span>

                        </div>

                    </div>

                     </div>

                    <div id="payment-options" class="mt-4">

                    

                        <p class="titulos-inicio" id="metodoPagoE"><b>Selecciona un método de pago:</b></p>

                        <div class="payment-button-group d-flex justify-content-center mt-3 flex-wrap">

                            <!--<button id="2" class="payment-button" style="background-image: url('imagenes/botones/boton_fintoc.png');"></button>-->

                            <button id="2" class="payment-button"><img alt="Fintoc Logo"

                                    src="https://assets.fintoc.com/?img_name=button_cl_light_trailing"> </button>

                                <button id="1" class="payment-button"

                                    style="background-image: url('imagenes/botones/boton_webpay.png');"></button>

                                <button id="3" class="payment-button"

                                    style="background-image: url('imagenes/botones/Mach_Movil.png');"></button>



                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

     </div>

    <!-- Modal Structure -->

    <div id="payment-modal" class="modal fade" tabindex="-1" role="dialog">

        <div class="modal-dialog" role="document">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title" style="color: #fff;text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.9);">Formulario

                        de Pago</h5>



                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">

                        <span aria-hidden="true">&times;</span>

                    </button>

                </div>

                <div class="modal-body">

                    <form id="payment-form" action="pagos/inicia.php" method="post">



                        <div class="two-columns">

                            <div>

                                <label for="rut" class="form-label">Rut:</label>

                                <input type="text" id="rut" name="rut" class="form-input" required tabindex="1"

                                    onkeyup="formatearRut(this)">



                                <label for="nombre" class="form-label">Nombre:</label>

                                <input type="text" id="nombre" name="nombre" class="form-input" required tabindex="3">



                                <label for="telefono" class="form-label">Teléfono: (9 dígitos)</label>

                                <input type="tel" id="telefono" name="telefono" class="form-input" required

                                    tabindex="5">

                            </div>



                            <div>

                                <label for="email" class="form-label">Email:</label>

                                <input type="email" id="email" name="email" class="form-input" required tabindex="2">



                                <label for="apellido" class="form-label">Apellidos:</label>

                                <input type="text" id="apellido" name="apellido" class="form-input" required

                                    tabindex="4">

                            </div>

                        </div>



                        <input id="ip_transaccion" name="ip_transaccion" type="text" value="<?php echo $miip ?>" hidden>

                        <input id="ip_ciudad" name="ip_ciudad" type="text" value="<?php echo $ip_ciudad ?>" hidden>

                        <input id="ip_region" name="ip_region" type="text" value="<?php echo $ip_region ?>" hidden>

                        <input id="ip_pais" name="ip_pais" type="text" value="<?php echo $ip_pais ?>" hidden>

                        <input id="ip_latitud" name="ip_latitud" type="text" value="<?php echo $ip_latitud ?>" hidden>

                        <input id="ip_longitud" name="ip_longitud" type="text" value="<?php echo $ip_longitud ?>"

                            hidden>



                        <input id="utm_source" name="utm_source" type="text" value="<?php echo $utm_source ?>" hidden>

                        <input id="utm_medium" name="utm_medium" type="text" value="<?php echo $utm_medium ?>" hidden>

                        <input id="utm_campaign" name="utm_campaign" type="text" value="<?php echo $utm_campaign ?>"

                            hidden>



                        <input type="hidden" id="monto" name="monto">

                        <input type="hidden" id="payment" name="payment">



                        <div id="texto-donacion" style="color: black;"></div><br>



                        <button type="submit" class="form-submit">Donar</button>

                    </form>

                </div>

            </div>

        </div>

    </div>

    <!--::header part start::-->

    <?php include('clicktocall.php'); ?>



    <!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>-->

    <!--<script src="path/to/jquery.magnific-popup.js"></script>-->

    <script src="js/jquery-1.12.1.min.js"></script>

    <script src="js/jquery.magnific-popup.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <script src="js/main.js"></script>



     <script>

        // function scrollToElement(elementId) {

        //     // Obtener el ancho de la ventana actual

        //     var windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;



        //     Verificar si el ancho de la ventana es menor o igual al valor proporcionado

        //     if (windowWidth <= 768) {

        //         var element = document.getElementById(elementId);

        //         if (element) {

        //             element.scrollIntoView({ behavior: 'smooth' });

        //         }

        //     }

        // }

        

// function scrollToElement(elementId) {

//     const element = document.getElementById(elementId);

//     if (element) {

//         const headerOffset = 100; // ajusta este valor según el alto de tu .menu-container

//         const elementPosition = element.getBoundingClientRect().top + window.pageYOffset;

//         const offsetPosition = elementPosition - headerOffset;



//         window.scrollTo({

//             top: offsetPosition,

//             behavior: "smooth"

//         });

//     }

// }

function scrollToElement(elementId) {

    const element = document.getElementById(elementId);

    if (element) {

        const headerOffset = 100;

        const elementPosition = element.getBoundingClientRect().top + window.pageYOffset;

        const offsetPosition = elementPosition - headerOffset;



        window.scrollTo({

            top: offsetPosition,

            behavior: "smooth"

        });

    }

}











function toggleOtroMonto() {

    const input = document.getElementById("custom-amount");

    if (input.classList.contains("d-none")) {

        input.classList.remove("d-none");

        input.focus();

    } else {

        input.classList.add("d-none");

    }

}



    </script>

  





</body>



</html>