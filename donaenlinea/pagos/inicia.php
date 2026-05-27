<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('../php/funciones.php');

function post_value($key, $default = '')
{
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Acceso no permitido.');
}

$data = array(
    'rut' => post_value('rut'),
    'nombre' => post_value('nombre'),
    'telefono' => post_value('telefono'),
    'email' => post_value('email'),
    'apellido' => post_value('apellido'),
    'ip_transaccion' => post_value('ip_transaccion'),
    'ip_ciudad' => post_value('ip_ciudad'),
    'ip_region' => post_value('ip_region'),
    'ip_pais' => post_value('ip_pais'),
    'ip_latitud' => post_value('ip_latitud'),
    'ip_longitud' => post_value('ip_longitud'),
    'utm_source' => post_value('utm_source', 'organico'),
    'utm_medium' => post_value('utm_medium', 'organico'),
    'utm_campaign' => post_value('utm_campaign', 'donaenlinea'),
    'monto' => post_value('monto'),
    'medio_pago_id' => post_value('payment'),
    'estado_pago_id' => 2
);

if ($data['rut'] === '' || $data['nombre'] === '' || $data['email'] === '' || $data['monto'] === '' || $data['medio_pago_id'] === '') {
    exit('Faltan datos obligatorios para iniciar el pago.');
}

switch ($data['medio_pago_id']) {
    case '1':
        $next_page = 'webpay/pagos';
        break;
    case '2':
        $next_page = 'fintoc/pagos';
        break;
    case '3':
        $next_page = 'mach/pagos';
        break;
    default:
        exit('Medio de pago no válido.');
}

$order_id = guardarDonacion($data);
?>
<form name="pagos" id="pagos" action="<?php echo htmlspecialchars($next_page, ENT_QUOTES, 'UTF-8'); ?>" method="post">
    <input type="hidden" name="monto" value="<?php echo htmlspecialchars($data['monto'], ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($data['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($data['email'], ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id, ENT_QUOTES, 'UTF-8'); ?>">
</form>

<script>
document.getElementById("pagos").submit();
</script>