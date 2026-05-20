<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('../php/funciones.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array(
        'rut' => $_POST['rut'],
        'nombre' => $_POST['nombre'],
        'telefono' => $_POST['telefono'],
        'email' => $_POST['email'],
        'apellido' => $_POST['apellido'],
        'ip_transaccion' => $_POST['ip_transaccion'],
        'ip_ciudad' => $_POST['ip_ciudad'],
        'ip_region' => $_POST['ip_region'],
        'ip_pais' => $_POST['ip_pais'],
        'ip_latitud' => $_POST['ip_latitud'],
        'ip_longitud' => $_POST['ip_longitud'],
        'utm_source' => $_POST['utm_source'],
        'utm_medium' => $_POST['utm_medium'],
        'utm_campaign' => $_POST['utm_campaign'],
        'monto' => $_POST['monto'],
        'medio_pago_id' => $_POST['payment'],
        'estado_pago_id' => 2
    );
    $order_id = guardarDonacion($data);

    switch ($data['medio_pago_id']) {
        case "1":
            $next_page = "webpay/pagos";
            break;
        case "2":
            $next_page = "fintoc/pagos";
            break;
        case "3":
            $next_page = "mach/pagos";
            break;
    }

    echo '<form name="pagos" id="pagos" action="' . $next_page . '" method="post">
    <input type="hidden" name="monto" value="' . $data['monto'] . '">
    <input type="hidden" name="nombre" value="' . $data['nombre'] . '">
    <input type="hidden" name="email" value="' . $data['email'] . '">
    <input type="hidden" name="order_id" value="' . $order_id . '">
    </form>
    <script>document.getElementById("pagos").submit();</script>';
}
?>