<?php
session_start();

include "../conexion/configuracion.php";
include '../pagos/mach/key/authorization.php';

if (!empty($_POST)) {

	$machBaseUrl = getenv('MACH_API_BASE_URL');
	if ($machBaseUrl === '') {
		$machBaseUrl = 'https://biz.soymach.com';
	}

	if ($_POST["donacion"] == "") {
		$monto = $_POST["txt_otro"];
	} else {
		$monto = $_POST["donacion"];
	}

	$monto = (int) $monto;

	if ($monto <= 0) {
		print "<script>alert(\"Monto inválido.\");window.location='../';</script>";
		exit;
	}

	$ip_transaccion = $db->real_escape_string($_POST["ip_transaccion"]);
	$ip_latitud = $db->real_escape_string($_POST["ip_latitud"]);
	$ip_longitud = $db->real_escape_string($_POST["ip_longitud"]);
	$ip_ciudad = $db->real_escape_string($_POST["ip_ciudad"]);
	$ip_region = $db->real_escape_string($_POST["ip_region"]);
	$ip_pais = $db->real_escape_string($_POST["ip_pais"]);
	$utm_source = $db->real_escape_string($_POST["utm_source"]);
	$utm_medium = $db->real_escape_string($_POST["utm_medium"]);
	$utm_campaign = $db->real_escape_string($_POST["utm_campaign"]);

	$insertDonacion = $db->query("
        INSERT INTO donaciones (
            monto,
            medio_pago_id,
            estado_id,
            ip_transaccion,
            ip_latitud,
            ip_longitud,
            ip_ciudad,
            ip_region,
            ip_pais,
            utm_source,
            utm_medium,
            utm_campaign
        )
        VALUES (
            $monto,
            2,
            2,
            \"$ip_transaccion\",
            \"$ip_latitud\",
            \"$ip_longitud\",
            \"$ip_ciudad\",
            \"$ip_region\",
            \"$ip_pais\",
            \"$utm_source\",
            \"$utm_medium\",
            \"$utm_campaign\"
        )
    ");

	if ($insertDonacion) {
		$orden_compra = $db->insert_id;

		$insertDonacionMach = $db->query("
            INSERT INTO donaciones_mach (
                orden_compra,
                monto,
                estado_pago_id
            )
            VALUES (
                \"$orden_compra\",
                $monto,
                2
            )
        ");

		if (!$insertDonacionMach) {
			error_log("ERROR INSERT donaciones_mach: " . $db->error);
			print "<script>alert(\"Ocurrió un error. Inténtelo nuevamente.\");window.location='../';</script>";
			exit;
		}

		$curl = curl_init();

		$createBusinessPayment = array(
			"payment" => array(
				"amount" => $monto,
				"message" => "Pago",
				"title" => "Donacion",
				"metadata" => array(
					"product_id" => "colecta2022",
					"customer_id" => $orden_compra
				)
			)
		);

		$data_string = json_encode($createBusinessPayment);

		curl_setopt_array(
			$curl,
			array(
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_HEADER => false,
				CURLOPT_URL => rtrim($machBaseUrl, '/') . "/payments",
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_NONE,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => $data_string,
				CURLOPT_RETURNTRANSFER => true,
			)
		);

		$response = curl_exec($curl);
		$curlError = curl_error($curl);
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if ($response === false || $curlError !== '') {
			error_log("ERROR MACH CURL: " . $curlError);
			print "<script>alert(\"No fue posible iniciar el pago MACH.\");window.location='../';</script>";
			exit;
		}

		$array = json_decode($response, true);

		if ($httpCode < 200 || $httpCode >= 300 || !isset($array['token'])) {
			error_log("ERROR MACH API HTTP " . $httpCode . " RESPONSE: " . $response);
			print "<script>alert(\"No fue posible iniciar el pago MACH.\");window.location='../';</script>";
			exit;
		}

		$token = $db->real_escape_string($array['token']);

		$sql2 = "UPDATE donaciones_mach SET token='$token' WHERE orden_compra='$orden_compra'";
		$db->query($sql2);

		$_SESSION['token'] = $token;
		?>
		<form name="mach" id="mach" action="../pagos/mach" method="post"></form>
		<script>document.getElementById('mach').submit();</script>
		<?php
		exit;
	}

	error_log("ERROR INSERT donaciones: " . $db->error);
	print "<script>alert(\"Ocurrió un error. Inténtelo nuevamente.\");window.location='../';</script>";
	exit;
}

print "<script>alert(\"Ocurrió un error. Inténtelo nuevamente.\");window.location='../';</script>";
exit;
?>