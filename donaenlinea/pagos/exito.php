<?php

$monto = isset($_GET['monto']) ? $_GET['monto'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$medio_pago = isset($_GET['medio_pago']) ? $_GET['medio_pago'] : '';

if ($monto === '' || $id === '' || $medio_pago === '') {
    echo "<script>alert('Datos de pago incompletos.'); window.location='../';</script>";
    exit;
}

?>
<form name="exito" id="exito" action="../gracias" method="post">
    <input type="hidden" name="monto" value="<?php echo htmlspecialchars($monto, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="medio_pago" value="<?php echo htmlspecialchars($medio_pago, ENT_QUOTES, 'UTF-8'); ?>">
</form>

<script>
    document.getElementById('exito').submit();
</script>