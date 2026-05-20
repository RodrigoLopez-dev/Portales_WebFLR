<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["amount"])) {
        $amount = str_replace('.', '', $_POST['amount']);
        $utm = str_replace('.', '', $_POST['utm']);
        // Asegúrate de validar y sanear los datos del formulario aquí.
        // Crear el URL con el monto
        $url = "https://fundacionlasrosas.cl/colecta/pagofintoc?monto=" . urlencode($amount) . "&utm=" . $utm;
        // Generar el código QR
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($url);
    } else {
        // Manejo de errores si no se proporciona un monto
        $qrCodeUrl = "error.png"; // Puedes mostrar una imagen de error en lugar de un código QR
    }
} else {
    // Redirigir si no se recibe una solicitud POST
    header("Location: index.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/estilo_pago_qr.css">
    <title>Generar QR</title>
</head>

<body>
    <div class="container">
        <h1>Código QR de Pago</h1>
        <img src="https://fundacionlasrosas.cl/imagen_corporativa/logos/FLR_cuadb.png" alt="Logo Fundación Las Rosas"
            class="logo">
        <img src="<?php echo $qrCodeUrl; ?>" alt="Código QR">
        <p>Monto a cobrar: <?php echo '$' . number_format($amount, 0, ',', '.'); ?></p>
        <p>¡Gracias por tu generosidad! Tu donación es muy apreciada y contribuirá a nuestra causa.</p>
        <button onclick="goBack()">Volver Atrás</button>
    </div>
    <script>
        // Función para volver atrás en la historia del navegador
        function goBack() {
            window.history.back();
        }
    </script>
</body>

</html>