<?php require_once __DIR__ . '/../clicktocall.php'; ?>

<footer class="poster-bottom">
    <div>
        <span class="poster-icon"><i class="fa fa-phone"></i></span>
        <strong>800 719 711</strong>
    </div>

    <div class="poster-brand">
        <img src="imagenes/logo AMOR QUE TRASCIENDE LA VIDA_FLR (1).png" alt="Amor que trasciende la vida"
            class="poster-brand-img">
    </div>
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<script src="js/jquery.magnific-popup.js"></script>
<script src="js/funciones.js"></script>
<script src="js/main.js"></script>

<script>
    let donationSubmitting = false;

    form.addEventListener('submit', function (e) {
        if (donationSubmitting) {
            e.preventDefault();
            return false;
        }

        donationSubmitting = true;

        const submitButton = form.querySelector('button[type="submit"]');

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerText = 'Procesando...';
        }
    });

    function scrollToElement(elementId) {
        var element = document.getElementById(elementId);

        if (!element) {
            return;
        }

        // Solo hacemos scroll automático en pantallas bajas
        if (window.innerHeight <= 760) {
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    }

    function toggleOtroMonto() {
        const input = document.getElementById("custom-amount");

        if (!input) {
            return;
        }

        if (input.classList.contains("d-none")) {
            input.classList.remove("d-none");
            input.focus();
        } else {
            input.classList.add("d-none");
        }
    }
</script>

</body>


</html>