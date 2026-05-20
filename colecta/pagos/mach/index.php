<?php
session_start();

require("../../conexion/configuracion.php");
include 'key/authorization.php';

$appUrl = rtrim(getenv('APP_URL'), '/');
if ($appUrl === '') {
	$appUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/colecta';
}

$machBaseUrl = getenv('MACH_API_BASE_URL');
if ($machBaseUrl === '') {
	$machBaseUrl = 'https://biz.soymach.com';
}

$token = isset($_SESSION['token']) ? $_SESSION['token'] : '';

if ($token === '') {
	header("Location: " . $appUrl . "/");
	exit;
}

$curl = curl_init();

curl_setopt_array(
	$curl,
	array(
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_HEADER => false,
		CURLOPT_URL => rtrim($machBaseUrl, '/') . "/payments/" . urlencode($token),
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_NONE,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_RETURNTRANSFER => true
	)
);

$response = curl_exec($curl);
$curlError = curl_error($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($response === false || $curlError !== '') {
	error_log("ERROR MACH index.php CURL: " . $curlError);
	header("Location: " . $appUrl . "/");
	exit;
}

$array = json_decode($response, true);

if ($httpCode < 200 || $httpCode >= 300 || !is_array($array)) {
	error_log("ERROR MACH index.php HTTP " . $httpCode . " RESPONSE: " . $response);
	header("Location: " . $appUrl . "/");
	exit;
}

$status = isset($array['status']) ? $array['status'] : '';
$url = isset($array['url']) ? $array['url'] : '';
$amount = isset($array['amount']) ? $array['amount'] : 0;
$customer_id = isset($array['metadata']['customer_id']) ? $array['metadata']['customer_id'] : '';

if ($url === '' || $customer_id === '') {
	error_log("ERROR MACH index.php respuesta incompleta: " . $response);
	header("Location: " . $appUrl . "/");
	exit;
}

if ($status === 'COMPLETED' || $status === 'CONFIRMED') {
	$customer_id_safe = $db->real_escape_string($customer_id);

	$sql = "UPDATE donaciones_mach SET estado_pago_id = 1 WHERE orden_compra = '$customer_id_safe'";
	$db->query($sql);

	$sql = "UPDATE donaciones SET estado_id = 1 WHERE id = '$customer_id_safe'";
	$db->query($sql);
}
?>
<!doctype html>
<html lang="es">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Donando con MACH</title>
	<meta name="viewport" content="width=device-width">

	<link rel="icon" href="<?php echo htmlspecialchars($appUrl . '/images/icono.jpg', ENT_QUOTES, 'UTF-8'); ?>">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
	<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700&display=swap" rel="stylesheet">

	<link href="assets/css/bootstrap.min.css" rel="stylesheet">
	<link href="assets/css/material-bootstrap-wizard.css" rel="stylesheet">
	<link href="assets/css/estilos.css" rel="stylesheet">

	<script src="qr/js/funciones.js"></script>
	<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
</head>

<body>
	<div class="image-container set-full-height">
		<div class="container" style="margin-top:-70px;">
			<div class="row">
				<div class="col-sm-8 col-sm-offset-2">
					<div class="wizard-container">
						<div class="card wizard-card" data-color="flr" id="wizard">
							<div class="wizard-header">
								<center>
									<img alt="Mach" style="max-width:300px;margin-top:-30px;"
										src="logo-2020-transp.png"><br><br>
									<h4
										style="font-family:Nunito,sans-serif;color:#333333;font-size:20px;margin-top:-40px;">
										➊<b> Abre MACH</b>&nbsp; &nbsp; ➋<b> Escanea</b>&nbsp; &nbsp; ➌ <b>Paga</b>
									</h4>
								</center>
							</div>

							<div class="row" style="position:relative">
								<div style="margin-top:-30px;">
									<div class="qr_mach" align="center" style="filter:brightness(200%);"></div>
								</div>

								<div class="col-md-12 col-sm-12 col-xs-12" align="center">
									<div class="row">
										<div class="col-md-12 money-color d-flex justify-content-center">
											<h3 style="font-family:Nunito,sans-serif;color:#6200EE;">
												<b>
													$ <?php echo number_format((int) $amount, 0, ',', '.'); ?>
													<img src="<?php echo htmlspecialchars($appUrl . '/images/logos/FLR_horTRANS_color.png', ENT_QUOTES, 'UTF-8'); ?>"
														width="180">
												</b>
											</h3>
										</div>
									</div>

									<div class="col-md-6 col-sm-6 col-xs-6">
										<a class="btn btn-info btn-block"
											style="background:#6200EE;font-family:Nunito,sans-serif;font-size:15px;"
											href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"
											role="button">Abrir MACH</a>
									</div>

									<div class="col-md-6 col-sm-6 col-xs-6">
										<a class="btn btn-info btn-block"
											style="background:#6200EE;font-family:Nunito,sans-serif;font-size:15px;"
											href="<?php echo htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8'); ?>"
											role="button">Volver</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="footer">
				<div class="container text-center">
					Gracias por tu donación <i class="fa fa-heart heart"></i>
				</div>
			</div>
		</div>
	</div>

	<form name="exito" id="exito"
		action="<?php echo htmlspecialchars($appUrl . '/gracias.php', ENT_QUOTES, 'UTF-8'); ?>" method="post">
		<input type="hidden" name="monto" value="<?php echo htmlspecialchars($amount, ENT_QUOTES, 'UTF-8'); ?>">
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($customer_id, ENT_QUOTES, 'UTF-8'); ?>">
	</form>

	<script type="text/javascript">
		qr('<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>');

		<?php if ($status === 'COMPLETED' || $status === 'CONFIRMED') { ?>
			document.getElementById('exito').submit();
		<?php } else { ?>
			setTimeout(function () {
				location.reload();
			}, 5000);
		<?php } ?>
	</script>
</body>

</html>