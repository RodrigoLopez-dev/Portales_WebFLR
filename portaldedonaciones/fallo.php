<?php
$monto = isset($_POST['monto'])
    ? $_POST['monto']
    : 0;

$cod = isset($_POST['id'])
    ? $_POST['id']
    : '';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>


<main class="landing-invierno fallo-page">
    <section class="landing-content">

        <!-- IZQUIERDA -->
        <div class="landing-left">
            <img src="imagenes/invierno2026-personamayor.png" alt="Persona mayor Fundación Las Rosas"
                class="landing-residente">
        </div>

        <!-- DERECHA -->
        <div class="landing-right fallo-right">

            <div class="fallo-card">

                <div class="hero-message">
                    <h1 class="hero-title fallo-title">
                        Algo salió mal
                        <i class="fa fa-exclamation-circle fallo-icon"></i>
                    </h1>

                    <p class="hero-text fallo-text">
                        No pudimos completar la donación en este momento.
                        Puedes intentarlo nuevamente.
                    </p>
                </div>

                <?php if (!empty($cod) || !empty($monto)): ?>
                    <div class="fallo-detalle">
                        <h2>Detalle del intento</h2>

                        <?php if (!empty($cod)): ?>
                            <p>
                                <strong>Código donación:</strong>
                                <?php echo htmlspecialchars($cod); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($monto)): ?>
                            <p>
                                <strong>Monto:</strong>
                                $<?php echo number_format((float) $monto, 0, ',', '.'); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <a href="index.php" class="fallo-btn">
                    Intentarlo nuevamente
                </a>

            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>