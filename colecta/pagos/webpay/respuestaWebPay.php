<?php
require("../../enigma.php");
require("../../conexion/configuracion.php");

$eni = new Enigma();

$appUrl = rtrim(getenv('APP_URL'), '/');
if ($appUrl === '') {
	$appUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/colecta';
}

$token = isset($_POST['token_ws']) ? $_POST['token_ws'] : '';

if ($_GET) {
	$eni->decode_get2($_SERVER["REQUEST_URI"]);

	$trs_orden_compra = isset($_GET['id']) ? $_GET['id'] : '';
	$monto = isset($_GET['monto']) ? $_GET['monto'] : '';

	$id = $eni->encode_this('id=' . $trs_orden_compra . '&monto=' . $monto);

	if ($trs_orden_compra === '' || $token === '') {
		echo '<script>alert("No fue posible validar la donación.");window.location="' . $appUrl . '/";</script>';
		exit();
	}

	$stmt = $db->prepare("
        SELECT COUNT(*)
        FROM donaciones_webpay
        WHERE tbk_orden_compra = ?
          AND token = ?
          AND tbk_respuesta = '0'
    ");

	if (!$stmt) {
		error_log("ERROR prepare respuestaWebPay.php: " . $db->error);
		echo '<script>alert("No fue posible validar la donación.");window.location="' . $appUrl . '/";</script>';
		exit();
	}

	$stmt->bind_param("ss", $trs_orden_compra, $token);
	$stmt->execute();
	$stmt->bind_result($totalRows_RS_Busca);
	$stmt->fetch();
	$stmt->close();

	if ((int) $totalRows_RS_Busca === 0) {
		echo '<script>alert("Lo sentimos, puede que su donación no se haga efectiva.");window.location="' . $appUrl . '/";</script>';
		exit();
	}
	?>
	<form name="exito" id="exito" action="<?php echo htmlspecialchars($appUrl . '/gracias.php', ENT_QUOTES, 'UTF-8'); ?>"
		method="post">
		<input type="hidden" name="monto" value="<?php echo htmlspecialchars($monto, ENT_QUOTES, 'UTF-8'); ?>">
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($trs_orden_compra, ENT_QUOTES, 'UTF-8'); ?>">
	</form>

	<script>
		document.getElementById('exito').submit();
	</script>
	<?php
	exit();
}

header("Location: " . $appUrl . "/");
exit();