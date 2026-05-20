<?php
require_once __DIR__ . '/php/fip.php';

if (isset($_GET["utm_source"])) {
    $utm_source = $_GET["utm_source"];
} else {
    $utm_source = "Regalos";
}

if (isset($_GET["utm_medium"])) {
    $utm_medium = $_GET["utm_medium"];
} else {
    $utm_medium = "organico";
}

if (isset($_GET["utm_campaign"])) {
    $utm_campaign = $_GET["utm_campaign"];
} else {
    $utm_campaign = "Regalos";
}

$miip = get_client_ip();

$ip_array = ip_info($miip, 'location');

$ip_ciudad = (is_array($ip_array) && isset($ip_array['city'])) ? $ip_array['city'] : '';
$ip_region = (is_array($ip_array) && isset($ip_array['state'])) ? $ip_array['state'] : '';
$ip_pais = (is_array($ip_array) && isset($ip_array['country'])) ? $ip_array['country'] : '';
$ip_latitud = (is_array($ip_array) && isset($ip_array['latitude'])) ? $ip_array['latitude'] : '';
$ip_longitud = (is_array($ip_array) && isset($ip_array['longitude'])) ? $ip_array['longitude'] : '';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<main class="landing-regalos">
    <section class="landing-content">
        <div class="landing-left">
            <img src="imagenes/regalos/SEÑORA CUMPLEAÑOS FLR.png" alt="Persona mayor Fundación Las Rosas"
                class="landing-abuelita">

            <img src="assets/img/logoFLR/FLR_horTRANS.png" alt="Fundación Las Rosas" class="landing-logo-overlay">
        </div>
        <div class="landing-right">
            <div class="hero-message">
                <h1 class="hero-title">Regalos que trascienden la Vida</h1>

                <p class="hero-text">
                    Cada regalo se transforma en 1 donación<br>
                    que ayuda a los 2.300 residentes en<br>
                    nuestros 29 hogares a lo largo de Chile.
                </p>
            </div>


            <p class="titulos-inicio text-center">
                <b>Selecciona tu aporte:</b>
            </p>

            <div class="donation-grid">
                <!-- FILA SUPERIOR -->
                <div class="donation-row donation-row-top">
                    <div class="donation-wrapper">
                        <button type="button" class="donation donation-code donation-1" value="10000"
                            onclick="scrollToElement('metodoPago')">
                            <span class="donation-code-icon" aria-hidden="true">
                                <img src="imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png" alt=""
                                    class="donation-gift-icon">
                            </span>
                            <span class="donation-code-main">$10.000</span>
                        </button>
                    </div>

                    <div class="donation-wrapper">
                        <button type="button" class="donation donation-code donation-2" value="20000"
                            onclick="scrollToElement('metodoPago')">
                            <span class="donation-code-icon" aria-hidden="true">
                                <img src="imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png" alt=""
                                    class="donation-gift-icon">
                            </span>
                            <span class="donation-code-main">$20.000</span>
                        </button>
                    </div>

                    <div class="donation-wrapper">
                        <button type="button" class="donation donation-code donation-3" value="30000"
                            onclick="scrollToElement('metodoPago')">
                            <span class="donation-code-icon" aria-hidden="true">
                                <img src="imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png" alt=""
                                    class="donation-gift-icon">
                            </span>
                            <span class="donation-code-main">$30.000</span>
                        </button>
                    </div>
                </div>

                <!-- FILA INFERIOR -->
                <div class="donation-row donation-row-bottom">
                    <div class="donation-wrapper">
                        <button type="button" class="donation donation-code donation-4" value="40000"
                            onclick="scrollToElement('metodoPago')">
                            <span class="donation-code-icon" aria-hidden="true">
                                <img src="imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png" alt=""
                                    class="donation-gift-icon">
                            </span>
                            <span class="donation-code-main">$40.000</span>
                        </button>
                    </div>

                    <div class="donation-wrapper donation-wrapper-otro">
                        <button type="button" class="donation donation-code donation-otro" value="">
                            <span class="donation-code-icon" aria-hidden="true">
                                <img src="imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png" alt=""
                                    class="donation-gift-icon">
                            </span>

                            <span class="donation-code-main donation-code-main-otro">
                                <span class="donation-otro-label">OTRO</span>

                                <span class="donation-otro-input-wrap">
                                    <span class="donation-otro-currency">$</span>

                                    <input id="custom-amount" class="donation-otro-input" type="number" min="1"
                                        placeholder="Monto" onkeydown="noPuntoComa(event)"
                                        onclick="scrollToElement('metodoPago')">
                                </span>
                            </span>
                        </button>

                        <span class="donation-code-sub donation-code-sub-otro">
                            Tú decides cuánto amor regalar
                        </span>
                    </div>
                </div>
            </div>

            <div class="payment-options" id="metodoPago">
                <p class="titulos-inicio text-center">
                    <b>Selecciona un método de pago:</b>
                </p>

                <div class="payment-button-group">
                    <button type="button" data-payment="2" class="payment-button">
                        <img alt="Fintoc Logo" src="https://assets.fintoc.com/?img_name=button_cl_light_trailing">
                    </button>

                    <button type="button" data-payment="1" class="payment-button"
                        style="background-image: url('imagenes/botones/boton_webpay.png');">
                    </button>

                    <button type="button" data-payment="3" class="payment-button"
                        style="background-image: url('imagenes/botones/Mach_Movil.png');">
                    </button>
                </div>
            </div>
        </div>

    </section>
</main>

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
                    <input id="ip_transaccion" name="ip_transaccion" type="text"
                        value="<?php echo htmlspecialchars($miip, ENT_QUOTES, 'UTF-8'); ?>" hidden>
                    <input id="ip_ciudad" name="ip_ciudad" type="text"
                        value="<?php echo htmlspecialchars($ip_ciudad, ENT_QUOTES, 'UTF-8'); ?>" hidden>
                    <input id="ip_region" name="ip_region" type="text"
                        value="<?php echo htmlspecialchars($ip_region, ENT_QUOTES, 'UTF-8'); ?>" hidden>
                    <input id="ip_pais" name="ip_pais" type="text"
                        value="<?php echo htmlspecialchars($ip_pais, ENT_QUOTES, 'UTF-8'); ?>" hidden>
                    <input id="ip_latitud" name="ip_latitud" type="text"
                        value="<?php echo htmlspecialchars($ip_latitud, ENT_QUOTES, 'UTF-8'); ?>" hidden>
                    <input id="ip_longitud" name="ip_longitud" type="text"
                        value="<?php echo htmlspecialchars($ip_longitud, ENT_QUOTES, 'UTF-8'); ?>" hidden>

                    <input id="utm_source" name="utm_source" type="text"
                        value="<?php echo htmlspecialchars($utm_source, ENT_QUOTES, 'UTF-8'); ?>" hidden>
                    <input id="utm_medium" name="utm_medium" type="text"
                        value="<?php echo htmlspecialchars($utm_medium, ENT_QUOTES, 'UTF-8'); ?>" hidden>
                    <input id="utm_campaign" name="utm_campaign" type="text"
                        value="<?php echo htmlspecialchars($utm_campaign, ENT_QUOTES, 'UTF-8'); ?>" hidden>
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