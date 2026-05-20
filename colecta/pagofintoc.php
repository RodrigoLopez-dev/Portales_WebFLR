<!DOCTYPE html>

<html lang="es">
<?php
include('php/fip.php');

if (isset($_GET["utm"])) {
    $utm_source = $_GET["utm"];
} else {
    $utm_source = "pago_qr";
}

$utm_medium = "pago_qr";
$utm_campaign = "pago_qr";
$miip = get_client_ip();
$ip_array = ip_info($miip, 'location');

if (isset($ip_array['city'])) {
    $ip_ciudad = $ip_array['city'];
} else {
    $ip_ciudad = '';
}

if (isset($ip_array['state'])) {
    $ip_region = $ip_array['state'];
} else {
    $ip_region = '';
}

if (isset($ip_array['country'])) {
    $ip_pais = $ip_array['country'];
} else {
    $ip_pais = '';
}

if (isset($ip_array['latitude'])) {
    $ip_latitud = $ip_array['latitude'];
} else {
    $ip_latitud = '';
}

if (isset($ip_array['longitude'])) {
    $ip_longitud = $ip_array['longitude'];
} else {
    $ip_longitud = '';
}

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/estilo_pago_qr.css">
    <title>Formulario de Donación</title>
</head>

<body>
    <div class="container">
        <h3><b>Bienvenido, esta es nuestra plataforma segura de pagos.</b></h3>
        <img src="https://fundacionlasrosas.cl/imagen_corporativa/logos/FLR_cuadb.png" alt="Logo Fundación Las Rosas"
            class="logo">
        <p>Tu apoyo es fundamental para nuestra fundación.</p>
        <!-- Mostrar el monto con separador de miles y símbolo "$" -->
        <p id="monto">Monto a pagar: $<?php echo number_format($_GET['monto'], 0, ',', '.'); ?></p>
        <form name="exito" id="exito" action="php/donacion_fintoc.php" method="post">
            <input id="donacion" name="donacion" value="<?php echo $_GET['monto']; ?>" hidden>
            <input id="ip_transaccion" name="ip_transaccion" type="text" value="<?php echo $miip ?>" hidden>
            <input id="ip_ciudad" name="ip_ciudad" type="text" value="<?php echo $ip_ciudad ?>" hidden>
            <input id="ip_region" name="ip_region" type="text" value="<?php echo $ip_region ?>" hidden>
            <input id="ip_pais" name="ip_pais" type="text" value="<?php echo $ip_pais ?>" hidden>
            <input id="ip_latitud" name="ip_latitud" type="text" value="<?php echo $ip_latitud ?>" hidden>
            <input id="ip_longitud" name="ip_longitud" type="text" value="<?php echo $ip_longitud ?>" hidden>
            <input id="utm_source" name="utm_source" type="text" value="<?php echo $utm_source ?>" hidden>
            <input id="utm_medium" name="utm_medium" type="text" value="<?php echo $utm_medium ?>" hidden>
            <input id="utm_campaign" name="utm_campaign" type="text" value="<?php echo $utm_campaign ?>" hidden>
            <!-- Estilo mejorado del botón de "Pagar" -->
            <button type="submit" id="boton_pagar">
                <img src="images/botones/boton_fintoc.png" alt="Pagar"
                    class="imagenes_boton">
            </button>
        </form>
        <p>¡Tu generosidad hace la diferencia!</p>
        <p>Gracias por apoyar a <br> <b>Fundación Las Rosas</b></p>
    </div>
</body>

</html>