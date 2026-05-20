<?php
$monto = isset($_POST['monto'])
    ? $_POST['monto']
    : (isset($_GET['monto']) ? $_GET['monto'] : 0);

$cod = isset($_POST['id'])
    ? $_POST['id']
    : (isset($_GET['id']) ? $_GET['id'] : '');

$medio_pago = isset($_POST['medio_pago'])
    ? $_POST['medio_pago']
    : (isset($_GET['medio_pago']) ? $_GET['medio_pago'] : 'No informado');
$payment_intent_id = isset($_POST['payment_intent_id'])
    ? $_POST['payment_intent_id']
    : (isset($_GET['payment_intent_id']) ? $_GET['payment_intent_id'] : '');

echo '<pre>';
print_r($payment_intent_id);
echo '</pre>';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<main class="landing-regalos gracias-page">
    <section class="landing-content">

        <!-- IZQUIERDA -->
        <div class="landing-left">
            <img src="imagenes/fondo/LANDING-DONACIONES-ROSTRO.png" alt="Persona mayor Fundación Las Rosas"
                class="landing-abuelita">
        </div>

        <!-- DERECHA -->
        <div class="landing-right gracias-right">

            <div class="gracias-card">

                <div class="hero-message">
                    <h1 class="hero-title gracias-title">
                        ¡Muchas gracias por su aporte!
                        <i class="fa fa-heart heart"></i>
                    </h1>

                    <p class="hero-text gracias-text">
                        Gracias por su generosa donación a Fundación Las Rosas,
                        donde cuidamos y brindamos amor a nuestros queridos adultos
                        mayores. Su apoyo es fundamental para mantener sus sonrisas
                        y su bienestar. Su contribución marca la diferencia en la
                        vida de quienes residen en nuestros hogares.
                    </p>
                </div>

                <div class="gracias-detalle">
                    <h2>Detalle de su donación</h2>

                    <p>
                        <strong>Código donación:</strong>
                        <?php echo htmlspecialchars($cod); ?>
                    </p>

                    <p>
                        <strong>Monto de donación:</strong>
                        $<?php echo number_format((float) $monto, 0, ',', '.'); ?>
                    </p>

                    <p>
                        <strong>Medio de pago:</strong>
                        <?php echo htmlspecialchars($medio_pago); ?>
                    </p>
                </div>

                <a href="index.php" class="gracias-btn">
                    Realizar otro aporte
                </a>

            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>