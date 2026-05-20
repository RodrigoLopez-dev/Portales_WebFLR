<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Generar códigos QR con PHP y HTML</title>
</head>

<body>
	<div class='container'>
		<h1>Generar códigos QR con PHP y HTML</h1>
		<div class='col-md-6'>
			<div class='result'></div>
		</div>
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
		<script>
			function createBusinessPayment() {
				var textqr = 'machapp://pay-biz/payment/ec67dcab-c36a-44b9-9868-40d97bfd9198';
				var sizeqr = '300';
				parametros = { "textqr": textqr, "sizeqr": sizeqr };
				$.ajax({
					type: "POST",
					url: "qr.php",
					data: parametros,
					success: function (datos) {
						$(".result").html(datos);
					}
				})
			};
			createBusinessPayment();
		</script>
</body>

</html>