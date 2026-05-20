<?php
if (!empty($_POST)) {
	include "../conexion/configuracion.php";

	$appUrl = rtrim(getenv('APP_URL'), '/');
	if ($appUrl === '') {
		$appUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/colecta';
	}

	if ($_POST["donacion"] == "") {
		$monto = $_POST["txt_otro"];
	} else {
		$monto = $_POST["donacion"];
	}

	$monto = (int) $monto;

	if ($monto <= 0) {
		print "<script>alert(\"Monto inválido.\");window.location='" . $appUrl . "';</script>";
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
            3,
            3,
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
		?>
		<form id="fm" action="https://www.bancoestado.cl/bancoestado/aportevoluntario/aporte.asp" method="post" name="fm">
			<input id="monto" name="monto" type="hidden" value="<?php echo htmlspecialchars($monto, ENT_QUOTES, 'UTF-8'); ?>">
			<input id="id_institucion" name="id_institucion" type="hidden" value="0008">
		</form>
		<script>document.getElementById('fm').submit();</script>
		<?php
		exit;
	}

	error_log("ERROR INSERT donaciones BancoEstado: " . $db->error);
	print "<script>alert(\"Ocurrió un error. Inténtelo nuevamente.\");window.location='" . $appUrl . "';</script>";
	exit;
}

print "<script>alert(\"Ocurrió un error. Inténtelo nuevamente.\");window.location='../';</script>";
exit;
?>