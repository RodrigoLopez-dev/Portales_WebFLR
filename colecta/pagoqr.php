<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/estilo_pago_qr.css">

    <script>
        function formatearInput(input) {
            // Elimina cualquier caracter que no sea un número
            let valor = input.value.replace(/\D/g, '');
            // Agrega los separadores de miles
            valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            // Actualiza el valor del input
            input.value = valor;
        }

        function limpiarCajaEntrada() {
            document.getElementById("amount").value = "";
        }
    </script>
    <title>POS Fintoc</title>
</head>

<body>
    <div class="container">
        <img src="https://fundacionlasrosas.cl/imagen_corporativa/logos/FLR_cuadb.png" alt="Logo Fundación Las Rosas"
            class="logo">
        <h1>Terminal de cobro de Fundación Las Rosas</h1>
        <p>El Terminal de Cobro de Fundación Las Rosas es una herramienta diseñada para procesar donaciones de manera
            rápida y segura.
            Puedes ingresar el monto que desees cobrar y generar un código QR.</p>
        <form action="generate_qr.php" method="post">
            <label for="amount">Ingresa institución:</label>
            <input type="text" id="utm" name="utm" placeholder="Ingresa institución">
            <br><br>
            <label for="amount">Monto a cobrar:</label>
            <input type="text" id="amount" name="amount" oninput="formatearInput(this)" placeholder="Ingresa un monto"
                inputmode="numeric" required>
            <button type="submit">Generar QR</button>
            <button onclick="limpiarCajaEntrada()">Limpiar</button>
        </form>
    </div>
</body>

</html>