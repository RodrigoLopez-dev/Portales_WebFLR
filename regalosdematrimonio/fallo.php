<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Donaciones</title>
  
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="js/funciones.js"></script>

    <?php include('css/styles.html'); ?>
</head>
<?php

if($_POST)
{
	if(isset($_POST["monto"])){$monto=$_POST["monto"];}else{$monto="error";}
	if(isset($_POST["id"])){$cod=$_POST["id"];}else{$cod="error";}
}
?>

<body>
<nav class="menu-container">
  <div class="logo">
    <img src="https://fundacionlasrosas.cl/colecta/imagen/logos/FLR_horTRANS.png" alt="Logo">
  </div>
  <button id="menuToggle" class="menu-toggle">&#9776;</button> <!-- Bot車n hamburguesa -->
  <ul class="menu-options">
    <li><a href="#inicio">Hazte socio</a></li>
    <li><a href="#nosotros">Contactanos</a></li>
  </ul>
</nav>


<!-- Modal Structure -->

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <img src="https://fundacionlasrosas.cl/imagen_corporativa/logos/FLR_cuadTRANS.png" alt="Imagen" id="img-left">
            </div>
                 <div class="col-md-6">
                 <h4 style="color:white";>Algo salio mal !!!</h4>         
                    <?php
                    echo '<br><h4>Inténtalo nuevamente</h4><br><br>
                    <h4 style="color:white";><a href=https://fundacionlasrosas.cl/portaldedonaciones> >> Volver</a> </h4><br>';
                    ?>
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
                <a href="https://twitter.com/intent/tweet?text='Cambiemos%20el%20pron車stico%20a%20su%20duro%20invierno'.%20..."><img src='imagenes/iconos/twitter-circle.png' class="social-icon"></a>
                <a href="https://api.whatsapp.com/send?text=Hola,..."><img src='imagenes/iconos/whatsapp-circle.png' class="social-icon"></a>
            </div>
            <div class="footer-section">
                <h4>Contacto <i class="fa fa-phone"></i></h4>
                <p>
                  Fono ayuda <a href="tel:227307140">: 22 730 71 40</a>
                  <br>
                  Email <a href="mailto:info@flrosas.cl">: info@flrosas.cl</a>
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
