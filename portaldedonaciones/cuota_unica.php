<?php
function input_get($key, $default = '')
{
    return isset($_GET[$key]) ? trim((string) $_GET[$key]) : $default;
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$id           = input_get('id');
$rut          = input_get('rut');
$nombre       = input_get('nombre');
$email        = input_get('email');
$monto        = input_get('monto');
$utm_source   = input_get('utm_source');
$utm_medium   = input_get('utm_medium');
$utm_campaign = input_get('utm_campaign');

if ($id === '') {
    $id = 'vacio';
}
?>
<form name="cuota_unica" id="cuota_unica" action="cuota_unica_sm.php" method="post">
    <input type="hidden" name="id" value="<?php echo e($id); ?>">
    <input type="hidden" name="rut" value="<?php echo e($rut); ?>">
    <input type="hidden" name="nombre" value="<?php echo e($nombre); ?>">
    <input type="hidden" name="email" value="<?php echo e($email); ?>">
    <input type="hidden" name="monto" value="<?php echo e($monto); ?>">
    <input type="hidden" name="utm_source" value="<?php echo e($utm_source); ?>">
    <input type="hidden" name="utm_medium" value="<?php echo e($utm_medium); ?>">
    <input type="hidden" name="utm_campaign" value="<?php echo e($utm_campaign); ?>">
</form>

<script>
    document.getElementById('cuota_unica').submit();
</script>