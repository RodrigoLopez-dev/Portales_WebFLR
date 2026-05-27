<?php

include('php/fip.php');

if (isset($_GET["utm_source"])) {
    $utm_source = $_GET["utm_source"];
} else {
    $utm_source = "organico";
}

if (isset($_GET["utm_medium"])) {
    $utm_medium = $_GET["utm_medium"];
} else {
    $utm_medium = "organico";
}

if (isset($_GET["utm_campaign"])) {
    $utm_campaign = $_GET["utm_campaign"];
} else {
    $utm_campaign = "organico";
}

$miip = get_client_ip();

$ip_array = ip_info($miip, 'location');

$ip_ciudad = $ip_array['city'];
$ip_region = $ip_array['state'];
$ip_pais = $ip_array['country'];
$ip_latitud = $ip_array['latitude'];
$ip_longitud = $ip_array['longitude'];

?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<main class="container d-none d-md-block pt-4">
    <!-- Imagen para escritorio -->
    <div class="col-md-8 offset-md-6 col-12 text-center">
        <br>
        <img src="imagenes/invierno2026-gradocalor.png" alt="Imagen" class="img-fluid" width="100%" heigth="100%"
            margin-top="10%">
        <br> <br>
        <img src="imagenes/fondo/Texto_contiene _caja.png" alt="Imagen" class="img-fluid" width="75%" heigth="75%"
            margin-top="10%">
        <div class="col-md-11 text-center">
            <p class="titulos-inicio text-center  text-md-start"><br> <b>Selecciona tu aporte:
                </b>
            </p>
            <div class="col-md-12">
                <div class="row">

                    <div class="col-sm-4 donation-wrapper">
                        <button class="donation" value="15000" onclick="scrollToElement('metodoPagoE')">
                            <img src="imagenes/botones/btn_15.png" alt="$15.000" class="donation-img" width="100%"
                                height="60%">
                        </button>
                    </div>

                    <div class="col-sm-4 ">
                        <button class="donation" value="30000" onclick="scrollToElement('metodoPagoE')">
                            <img src="imagenes/botones/btn_30.png" alt="$30.000" class="donation-img" width="100%"
                                height="60%">
                        </button>
                    </div>

                    <div class="col-sm-4 ">
                        <button class="donation" value="45000" onclick="scrollToElement('metodoPagoE')">
                            <img src="imagenes/botones/btn_45.png" alt="$45.000" class="donation-img" width="100%"
                                height="60%">
                        </button>
                    </div>

                    <div class="row ">
                        <div class="col-sm-5  ">
                            <button class="donation" value="60000" onclick="scrollToElement('metodoPagoE')">
                                <img src="imagenes/botones/btn_60.png" alt="$60.000" class="donation-img" width="100%"
                                    height="60%">
                            </button>
                        </div>

                        <div class="col-sm-5" style="align-content: left">
                            <button class="donation" value=""> <img src="imagenes/botones/btn_otro.png" alt="otro"
                                    class="donation-img" width="90%"><br>
                                <span class="donation-description"></span>
                            </button>
                            <input id="custom-amount2" type="number" min="1" onkeydown="noPuntoComa( event )"
                                style="display:none;">
                        </div>

                    </div>
                </div>
                <div id="payment-options" class="mt-4">
                    <p class="titulos-inicio" id="metodoPagoE"><b>Selecciona un método de pago:</b></p>
                    <div class="payment-button-group d-flex justify-content-center mt-3 flex-wrap">
                        <button id="2" class="payment-button"
                            style="background-image: url('imagenes/botones/boton_fintoc.png');"></button>
                        <button id="1" class="payment-button"
                            style="background-image: url('imagenes/botones/boton_webpay.png');"></button>
                        <button id="3" class="payment-button"
                            style="background-image: url('imagenes/botones/boton_mach.png');"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
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
                            <input type="tel" id="telefono" name="telefono" class="form-input" required tabindex="5">
                        </div>
                        <div>
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" id="email" name="email" class="form-input" required tabindex="2">
                            <label for="apellido" class="form-label">Apellidos:</label>
                            <input type="text" id="apellido" name="apellido" class="form-input" required tabindex="4">
                        </div>
                    </div>
                    <input id="ip_transaccion" name="ip_transaccion" type="text" value="<?php echo $miip ?>" hidden>
                    <input id="ip_ciudad" name="ip_ciudad" type="text" value="<?php echo $ip_ciudad ?>" hidden>
                    <input id="ip_region" name="ip_region" type="text" value="<?php echo $ip_region ?>" hidden>
                    <input id="ip_pais" name="ip_pais" type="text" value="<?php echo $ip_pais ?>" hidden>
                    <input id="ip_latitud" name="ip_latitud" type="text" value="<?php echo $ip_latitud ?>" hidden>
                    <input id="ip_longitud" name="ip_longitud" type="text" value="<?php echo $ip_longitud ?>" hidden>
                    <input id="utm_source" name="utm_source" type="text" value="<?php echo $utm_source ?>" hidden>
                    <input id="utm_medium" name="utm_medium" type="text" value="<?php echo $utm_medium ?>" hidden>
                    <input id="utm_campaign" name="utm_campaign" type="text" value="<?php echo $utm_campaign ?>" hidden>
                    <input type="hidden" id="monto" name="monto">
                    <input type="hidden" id="payment" name="payment">
                    <div id="texto-donacion" style="color: black;"></div><br>
                    <button type="submit" class="form-submit">Donar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>