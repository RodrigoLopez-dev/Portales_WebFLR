<?php
require_once __DIR__ . '/includes/helpers.php';
require("conexion/configuracion.php");

if ($_POST) {

	if (isset($_POST["monto"])) {
		$monto = $_POST["monto"];
	} else {
		$monto = "error";
	}

	if (isset($_POST["id"])) {
		$cod = $_POST["id"];
	} else {
		$cod = "error";
	}

	$sql2 = "UPDATE donaciones_mach SET estado_pago_id=1 WHERE id='$cod'";
	$db->query($sql2);
	$query = "SELECT  nombre,
									email
										FROM donaciones_mach
										WHERE id='" . $cod . "'";

	$result = $db->query($query);
	$row = $result->fetch_row();
	$nombre = $row[0];
	$email = $row[1];
}

?>
<!doctype html>
<html lang="en">

<head>
	<meta http-equiv="Expires" content="0">
	<meta http-equiv="Last-Modified" content="0">
	<meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
	<meta http-equiv="Pragma" content="no-cache">
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="theme-color" content="#af0a3d" />
	<style>
		#div_iframe {
			display: flex;
			justify-content: center;
		}
	</style>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-113984126-1"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag() { dataLayer.push(arguments); }
		gtag('js', new Date());
		gtag('config', 'UA-113984126-1');
	</script>
	<title>Prueba</title>
	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
	<meta name="viewport" content="width=device-width" />
	<link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png" />
	<link rel="icon" type="image/png" href="assets/img/favicon.ico" />
	<!--     Fonts and icons     -->
	<link rel="stylesheet" type="text/css"
		href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
	<!-- CSS Files -->
	<link href="assets/css/bootstrap.min.css" rel="stylesheet" />
	<link href="assets/css/material-bootstrap-wizard.css" rel="stylesheet" />
	<!-- CSS Just for demo purpose, don't include it in your project -->
	<link href="assets/css/estilos.css" rel="stylesheet" />
	<!--   Core JS Files   -->
	<script src="https://code.jquery.com/jquery-3.4.1.min.js" type="text/javascript"></script>
	<script src="<?php echo asset('js/core/bootstrap.min.js'); ?>"></script>
	<script src="assets/js/jquery.bootstrap.js" type="text/javascript"></script>
	<!--  Plugin for the Wizard -->
	<script src="assets/js/material-bootstrap-wizard.js"></script>
	<!--  More information about jquery.validate here: http://jqueryvalidation.org/	 -->
	<script src="assets/js/jquery.validate.min.js"></script>
</head>

<body>
	<div class="image-container set-full-height" style="background-image: url('assets/img/portal.jpg')">
		<!--   Creative Tim Branding   -->
		<a href="https://www.fundacionlasrosas.cl">
			<div class="logo-container">
				<img src="assets/img/logofinal01.png" width="150px">
			</div>
		</a>
		<!--   Big container   -->
		<div class="container">
			<div class="row">
				<div class="col-sm-8 col-sm-offset-2">
					<!--      Wizard container        -->
					<div class="wizard-container">
						<div class="card wizard-card" data-color="flr" id="wizard">
							<!--        You can switch " data-color="blue" "  with one of the next bright colors: "green", "orange", "red", "purple"             -->
							<div class="wizard-header">
								<h3 class="wizard-title"><?php $nom = explode(" ", $nombre, 5);
								echo $nom[0]; ?></h3>
								<h3 class="info-text">¡Muchas gracias por tu aporte! <i class="fa fa-heart heart"
										style="color:#FF0000" ;></i></h3>
							</div>
							<div class="tab-content">
								<h4 class="info-text">
									<?php
									echo '<b>Detallamos tu donación :</b> <br><br>
													Código donación: ' . $cod . ' <br>
													Medio de pago: MACH <br>
													Monto de donación: $' . number_format($monto, 0, ',', '.') . '<br>
													Email: ' . $email; ?>
									<div id='cargando' style="display: flex; justify-content: center;">
										<img src="https://www.fundacionlasrosas.cl/portaldonaciones/loading.gif"
											width="50" height="50"><br><br>
										<div class="info-text">
											Enviando correo ...
										</div>
									</div>
									<br><br><br>
									Tu donación y todos los recursos serán destinados a la prevención y mitigación del
									Coronavirus en nuestros 28 hogares a lo largo de Chile.<br><br>
									Si tienes alguna consulta que hacernos, llama gratis al <b>800 720 111</b> o
									envíanos un email a <b>amigos@flrosas.cl</b>
									y con gusto te atenderemos.<br><br>
									¿Aún no eres Amigo de Fundación Las Rosas? Llama al <b>800 720 111</b>, opción 1 y
									conviértete en
									Amigo de los que más lo necesitan, sé Amigo de un adulto mayor.<br><br><br>
								</h4>
								<br><br>
								<?php
								$de = 'Donaciones Fundación Las Rosas';
								$mail = 'Fundación Las Rosas <no-responder@fundacionlasrosas.cl>';
								$para = $email;
								$header = 'From:' . $mail . " \r\n";
								$header .= "X-Mailer: PHP/" . phpversion() . " \r\n";
								$header .= "Mime-Version: 1.0 \r\n";
								$header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
								$mensaje = '<html><body><img src="https://www.fundacionlasrosas.cl/portaldonaciones/bannerGracias.jpg" width="602" height="195" border="0">';
								$asunto = 'Gracias por tu aporte en Fundación Las Rosas';
								$mensaje .= '<br><br>' . $nom[0] . ' ¡muchas gracias por tu aporte!<br><br>';
								$mensaje .= 'Detallamos tu donación: <br><br>
											Código donación: ' . $cod . ' <br>
											Medio de pago: MACH <br>
											Monto de donación: $' . $monto . '<br>
											Email: ' . $email;
								$mensaje .= '<br><br><br>
											Tu donación y todos los recursos serán destinados a la prevención y mitigación del Coronavirus en
											 nuestros 28 hogares a lo largo de Chile.<br><br>
											Si tienes alguna consulta que hacernos, llama gratis al 800 720 111 o envíanos un email a amigos@flrosas.cl
											 y con gusto te atenderemos.<br><br>
											<b>¿Aún no eres Amigo de Fundación Las Rosas?</b><br>
											Llama al 800 720 111, opción 1 y conviértete en
											Amigo de los que más lo necesitan, sé Amigo de un adulto mayor.';
								$mensaje .= "<br><br><b>Cualquier consulta llame al fono: 800 720 111  </b><br>";
								$mensaje .= "Fundación Las Rosas <br>Alonso de Ovalle 1465 piso 5 - Santiago<br><br>";
								$mensaje .= "<a href='http://www.flr.cl/firma' target='_blank'><img src='http://www.flr.cl/banner.jpg' width='500' height='90' border='0'></a> <br>";
								if (mail($para, $asunto, utf8_decode($mensaje), $header)) {
									echo "<b><br>Se le ha enviado un correo a " . $email . " </b> <br><br>";
									echo
										"<script>
											function myFunction(){
												div = document.getElementById('cargando');
												div.style.display = 'none';
											}
											myFunction();
										</script>";
								} else {
									echo "Fallo el envio de correo<br><br>";
								}
								?>
								<h4> <a href="https://www.fundacionlasrosas.cl/portaldonaciones"> > Volver al Portal de
										Donaciones </a></h4>
								<h4> <a href="https://www.fundacionlasrosas.cl"> > Continuar navegando en
										fundacionlasrosas.cl </a></h4>
							</div>
						</div>
					</div> <!-- wizard container -->
				</div>
			</div> <!-- row -->
		</div> <!--  big container -->
		<div class="footer">
			<div style="display: flex; justify-content: center;">
				<a class="btn-second" href="tel:800720111"> 800 720 111 <br> Mesa de ayuda</a>
				<a class="btn-fourth" href="tel:800720111">Llámanos <i class="material-icons">touch_app </i></a>
			</div>
			<div class="container text-center">
				Los cuidamos para siempre <i class="fa fa-heart heart"></i>
			</div>
		</div>
	</div>
</body>

</html>