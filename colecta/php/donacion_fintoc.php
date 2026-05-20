<?php
require("../enigma.php");

$eni = new Enigma();

$appUrl = rtrim(getenv('APP_URL'), '/');
if ($appUrl === '') {
	$appUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/colecta';
}

if (!empty($_POST)) {
	include "../conexion/configuracion.php";

	if ($_POST["donacion"] == "") {
		$monto = $_POST["txt_otro"];
	} else {
		$monto = $_POST["donacion"];
	}

	$monto = (int) $monto;

	if ($monto <= 0) {
		echo "<script>alert(\"Monto inválido.\");window.location='" . $appUrl . "';</script>";
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
            5,
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
		$donacionID = $db->insert_id;

		echo '<form name="pagos" id="pagos" action="../pagos/fintoc/pagos.php" method="post">
            <input type="hidden" name="monto" value="' . htmlspecialchars($monto, ENT_QUOTES, 'UTF-8') . '">
            <input type="hidden" name="order_id" value="' . htmlspecialchars($donacionID, ENT_QUOTES, 'UTF-8') . '">
            <input type="hidden" name="utm_source" value="' . htmlspecialchars($utm_source, ENT_QUOTES, 'UTF-8') . '">
            <input type="hidden" name="utm_medium" value="' . htmlspecialchars($utm_medium, ENT_QUOTES, 'UTF-8') . '">
            <input type="hidden" name="utm_campaign" value="' . htmlspecialchars($utm_campaign, ENT_QUOTES, 'UTF-8') . '">
        </form>
        <script>document.getElementById("pagos").submit();</script>';

		exit;
	}

	error_log("ERROR INSERT donaciones Fintoc: " . $db->error);
	echo "<script>alert(\"Ocurrió un error. Inténtelo nuevamente.\");window.location='" . $appUrl . "';</script>";
	exit;
}

echo "<script>alert(\"Ocurrió un error. Inténtelo nuevamente.\");window.location='" . $appUrl . "';</script>";
exit;
?>