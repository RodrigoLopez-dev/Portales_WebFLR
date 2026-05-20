<?php
ob_start();

require("../enigma.php");

$eni = new Enigma();

if (!empty($_POST)) {
	include "../conexion/configuracion.php";

	if ($_POST["donacion"] == "") {
		$monto = $_POST["txt_otro"];
	} else {
		$monto = $_POST["donacion"];
	}

	$ip_transaccion = $_POST["ip_transaccion"];
	$ip_latitud = $_POST["ip_latitud"];
	$ip_longitud = $_POST["ip_longitud"];
	$ip_ciudad = $_POST["ip_ciudad"];
	$ip_region = $_POST["ip_region"];
	$ip_pais = $_POST["ip_pais"];
	$utm_source = $_POST["utm_source"];
	$utm_medium = $_POST["utm_medium"];
	$utm_campaign = $_POST["utm_campaign"];

	$insertDonacion = $db->query("INSERT INTO donaciones (monto,medio_pago_id,estado_id,ip_transaccion,ip_latitud,ip_longitud,ip_ciudad,
        ip_region,ip_pais,utm_source,utm_medium,utm_campaign)
        VALUES ($monto,1,2,\"$ip_transaccion\",\"$ip_latitud\",\"$ip_longitud\",\"$ip_ciudad\",
        \"$ip_region\",\"$ip_pais\",\"$utm_source\",\"$utm_medium\",\"$utm_campaign\")");

	if ($insertDonacion) {
		$donacionID = $db->insert_id;
		$id = $eni->encode_this('monto=' . $monto . '&id=' . $donacionID);

		header("Location: ../pagos/webpay/procesar_aporte.php?" . $id);
		exit;
	}

	print "<script>alert(\"Ocurrió un error. Inténtelo nuevamente.\");window.location='../';</script>";
	exit;
}

print "<script>alert(\"Ocurrió un error. Inténtelo nuevamente.\");window.location='../';</script>";
exit;
?>