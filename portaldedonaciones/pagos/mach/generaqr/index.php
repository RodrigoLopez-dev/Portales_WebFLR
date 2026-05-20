<?php
session_start();

require_once __DIR__ . '/key/authorization.php';
require_once __DIR__ . '/../../../config/env.php';

load_env(__DIR__ . '/../../../.env');

if (empty($_SESSION['token'])) {
	die('Token MACH no encontrado.');
}

$token = $_SESSION['token'];

$response = fetchPaymentInfo($token, $headers);
$array = json_decode($response, true);

if (!$array || !is_array($array)) {
	error_log('Respuesta inválida desde MACH: ' . $response);
	die('No fue posible obtener información del pago.');
}

$status = isset($array['status']) ? $array['status'] : '';
$url = isset($array['payment_url'])
	? $array['payment_url']
	: (isset($array['url']) ? $array['url'] : '');
$amount = isset($array['amount']) ? (int) $array['amount'] : 0;
$customer_id = isset($array['metadata']['customer_id']) ? $array['metadata']['customer_id'] : '';

if ($status == 'COMPLETED') {
	updatePaymentStatus($customer_id, $amount);
}

function fetchPaymentInfo($token, $headers)
{
	$baseUrl = env_value('MACH_API_BASE_URL', 'https://biz.soymach.com');

	$headers[] = 'User-Agent: Mozilla/5.0';

	$curl = curl_init();

	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL => rtrim($baseUrl, '/') . "/payments/" . urlencode($token),
			CURLOPT_HTTPGET => true,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HEADER => false,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_USERAGENT => 'Mozilla/5.0'
		)
	);

	$response = curl_exec($curl);

	if ($response === false) {
		$error = curl_error($curl);
		curl_close($curl);
		error_log('Error CURL MACH: ' . $error);
		return '';
	}

	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	curl_close($curl);

	if ($httpCode < 200 || $httpCode >= 300) {
		error_log('MACH GET payment HTTP ' . $httpCode . ': ' . $response);
		return '';
	}

	return $response;
}

function updatePaymentStatus($customer_id, $amount)
{
	echo '<form id="redirectForm" action="../../../gracias" method="post">';
	echo '<input type="hidden" name="monto" value="' . htmlspecialchars($amount, ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="id" value="' . htmlspecialchars($customer_id, ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="medio_pago" value="Mach">';
	echo '</form>';
	echo '<script>document.getElementById("redirectForm").submit();</script>';
	exit;
}
?>

<!doctype html>
<html lang="es">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Donando con MACH</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
	<meta name="viewport" content="width=device-width">

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700&display=swap" rel="stylesheet">
	<link href="assets/css/bootstrap.min.css" rel="stylesheet">
	<link href="assets/css/material-bootstrap-wizard.css" rel="stylesheet">
	<link href="assets/css/estilos.css" rel="stylesheet">

	<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
	<script src="qr/js/funciones.js"></script>
	<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
</head>

<body>
	<div class="image-container set-full-height">
		<div class="logo-container" style="margin:50px;"></div>

		<div class="container" style="margin-top:-70px;">
			<div class="row">
				<div class="col-sm-8 col-sm-offset-2">

					<div class="wizard-container">
						<div class="card wizard-card" data-color="flr" id="wizard">

							<div class="wizard-header">
								<center>
									<img alt="Mach" style="max-width: 350px;margin:8px; padding-bottom:15px;"
										src="Mach_escritorio.png">
									<br><br>

									<h4
										style="font-family: Nunito, sans-serif;color:#333333;font-size:20px;margin-top:-40px;">
										➊ <b>Abre MACH</b>&nbsp;&nbsp;
										➋ <b>Escanea</b>&nbsp;&nbsp;
										➌ <b>Paga</b>
									</h4>
								</center>
							</div>

							<div class="row" style="position:relative">
								<div style="margin-top:-30px;">
									<div id="qr-container" align="center" style="filter: brightness(200%);"></div>
								</div>

								<div class="col-md-12 col-sm-12 col-xs-12" align="center">
									<div class="row">
										<div class="col-md-12 money-color d-flex justify-content-center">
											<h3 style="font-family: Nunito, sans-serif;color:#6200EE;">
												<b>$ <?php echo number_format((int) $amount, 0, ',', '.'); ?></b>
											</h3>
										</div>
									</div>

									<div class="col-md-6 col-sm-6 col-xs-6">
										<a class="btn btn-info btn-block"
											style="background:#6200EE;font-family: Nunito, sans-serif;font-size:15px;"
											href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"
											role="button">
											Abrir MACH
										</a>
									</div>

									<div class="col-md-6 col-sm-6 col-xs-6">
										<a class="btn btn-info btn-block"
											style="background:#6200EE;font-family: Nunito, sans-serif;font-size:15px;"
											href="#" onclick="history.go(-1); return false;" role="button">
											Volver
										</a>
									</div>
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

	<input type="hidden" name="status" id="status"
		value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>">

	<form name="exito" id="exito" action="../gracias" method="post">
		<input type="hidden" name="monto" value="<?php echo htmlspecialchars($amount, ENT_QUOTES, 'UTF-8'); ?>">
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($customer_id, ENT_QUOTES, 'UTF-8'); ?>">
	</form>

	<script>
		var urlMach = <?php echo json_encode($url); ?>;

		console.log("URL MACH recibida:", urlMach);

		document.addEventListener("DOMContentLoaded", function () {
			if (urlMach && urlMach.trim() !== "") {
				var qrContainer = document.getElementById("qr-container");

				new QRCode(qrContainer, {
					text: urlMach,
					width: 300,
					height: 300,
					correctLevel: QRCode.CorrectLevel.H
				});
			} else {
				console.error("URL inválida para QR:", urlMach);
			}
		});
	</script>
	<script>
		function checkMachStatus() {
			fetch('../check_status.php', {
				method: 'GET',
				credentials: 'same-origin'
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (data) {
					console.log('Estado MACH:', data);

					if (data.ok && data.paid && data.redirect_url) {
						window.location.href = data.redirect_url;
					}
				})
				.catch(function (error) {
					console.error('Error consultando estado MACH:', error);
				});
		}

		setInterval(checkMachStatus, 5000);
	</script>
</body>

</html>