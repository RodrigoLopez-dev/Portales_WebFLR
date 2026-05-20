<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Donaciones</title>
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css'>
    <script src="js/funciones.js"></script>

    <?php include('css/styles.html'); ?>
    <!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-KHMCJBPH');</script>
<!-- End Google Tag Manager -->

    <!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '3773397849539419');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=3773397849539419&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
</head>
<?php

if($_POST)
{
	if(isset($_POST["monto"])){$monto=$_POST["monto"];}else{$monto="error";}
	if(isset($_POST["id"])){$cod=$_POST["id"];}else{$cod="error";}  
  if(isset($_POST["medio_pago"])){$medio_pago=$_POST["medio_pago"];}else{$medio_pago="error";}
}
?>

<body>
    <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KHMCJBPH"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<nav class="menu-container">
  <div class="logo">
    <img src="https://fundacionlasrosas.cl/colecta/imagen/logos/FLR_horTRANS.png" alt="Logo">
  </div>
  <button id="menuToggle" class="menu-toggle">&#9776;</button> <!-- Botón hamburguesa -->
  <ul class="menu-options">
  <li><b><a href="https://fundacionlasrosas.cl" class="titulos-inicio">Inicio</a></b></li>
    <li><b><a href="https://widget.forpay.cl/sus/index.php?key=4dc5927dfd7fb4278315e222b081d01ev2" class="titulos-inicio">Hazte Amigo</a></b></li>
    <li>
    <b><a href="https://www.paypal.com/paypalme/fundacionlasrosas">
        <img src="imagenes/botones/boton_paypal.png" alt="Donar en Dólares" style="width:100px;height:auto;border-radius: 5px;">
    </a></b>
</li>
    
  </ul>
</nav>


<!-- Modal Structure -->

<div class="container">
    <div class="row">
        <div class="col-md-6" style="text-align: center;">
        <img src="https://fundacionlasrosas.cl/imagen_corporativa/logos/FLR_cuadTRANS.png" alt="Imagen" id="img-left" style="height: 400px; width: auto;">


            </div>
                 <div class="col-md-6"><br> <br> <br> 
                 <h4 class="titulos-inicio" style="color:blue">¡Muchas gracias por su aporte! <i class="fa fa-heart heart" style="color:#af0a3d";></i></h4> 
                 <br> 
                 <p class="titulos-inicio">Gracias por su generosa donación a Fundación Las Rosas, 
                  donde cuidamos y brindamos amor a nuestros queridos adultos
                  mayores. <br>Su apoyo es fundamental para mantener sus sonrisas
                   y su bienestar. Su contribución marca la diferencia en la 
                   vida de quienes residen en nuestros hogares.</p>       
                    <?php
                    echo '<br><h4 class="titulos-inicio">Detallamos su donación :</h4>
                    <p class="titulos-inicio">Código donación: '.$cod.' <br>
                    Monto de donación: $'. number_format($monto,0,',','.').'
                    <br>Medio de pago :  '.$medio_pago.'</p><br>';
                    ?>
                    <h4 style="color:black";><a href=https://fundacionlasrosas.cl/portaldedonaciones> >> Hacer otra donación</a> </h4><br>
                   
                 </div>
          </div>
        </div>
    </div>
</div>


<footer class="custom-footer">
    <div class="footer-container">
        <div class="footer-row">
            <div class="footer-section">
                <img src="imagenes/logos/FLR_horTRANS.png" class="footer-logo" alt="">
            </div>
            <div class="footer-section">
                <h4>Comparte <i class="fa fa-share-alt"></i></h4>
                <a href="https://www.facebook.com/sharer/sharer.php?u=https%3A//fundacionlasrosas.cl/portaldedonaciones"><img src='imagenes/iconos/facebook-circle.png' class="social-icon"></a>
                <a href="https://twitter.com/intent/tweet?text='Cambiemos%20el%20pronóstico%20a%20su%20duro%20invierno'.%20..."><img src='imagenes/iconos/twitter-circle.png' class="social-icon"></a>
                <a href="https://api.whatsapp.com/send?text=Hola,..."><img src='imagenes/iconos/whatsapp-circle.png' class="social-icon"></a>
            </div>
            <div class="footer-section">
                <h4>Contacto <i class="fa fa-phone"></i></h4>
                <p style="color: #F2F2F2;">
                  Fono ayuda <a style="color: #F2F2F2;" href="tel:227307140">: 22 730 71 40</a>
                  <br>
                  Email <a style="color: #F2F2F2;" href="mailto:info@flrosas.cl">: info@flrosas.cl</a>
                </p>
            </div>
        </div>
    </div>
</footer>



<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
