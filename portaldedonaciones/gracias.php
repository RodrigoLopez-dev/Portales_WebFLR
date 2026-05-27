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
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<main class="landing-invierno gracias-page">
    <section class="landing-content">

        <!-- IZQUIERDA -->
        <div class="landing-left">
            <img src="imagenes/invierno2026-personamayor.png" alt="Persona mayor Fundación Las Rosas"
                class="landing-residente">
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
                        Gracias a tu generosidad, entregaremos el calor y el cuidado que tanto necesitan los más 
                        de 2.300 residentes de nuestros 28 Hogares para enfrentar este crudo invierno. Tu apoyo 
                        es el motor constante que mantiene encendido el cariño y la dignidad en cada una de las 
                        Personas Mayores a nuestro cuidado.
                    </p>
                    <p class="hero-text gracias-text">
                        ¡Gracias por sumarte a un amor que trasciende la vida!
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