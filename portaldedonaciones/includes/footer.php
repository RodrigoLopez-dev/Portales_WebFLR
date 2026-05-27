<footer class="custom-footer">
    <div class="footer-container">
        <div class="footer-row">
            <div class="footer-section">
                <h4>Contacto <i class="fa fa-phone"></i></h4>
                <p style="color: #F2F2F2;">
                    Fono ayuda <a style="color: #F2F2F2;" href="tel:800 719 711">:<br class="mobile-space">800 719 711
                        Opción 1</a>
                    <br>
                    Email <a style="color: #F2F2F2;" href="mailto:info@flrosas.cl">: info@flrosas.cl</a>
                </p>
            </div>
            <div class="footer-section">
                <h4>Comparte <i class="fa fa-share-alt"></i></h4>
                <div class="social-icon-cont">
                    <a
                        href="https://www.facebook.com/sharer/sharer.php?u=https%3A//fundacionlasrosas.cl/portaldedonaciones"><img
                            src='imagenes/iconos/facebook-circle.png' class="social-icon"></a>
                    <a
                        href="https://twitter.com/intent/tweet?text='Cambiemos%20el%20pronóstico%20a%20su%20duro%20invierno'.%20..."><img
                            src='imagenes/iconos/twitter-circle.png' class="social-icon"></a>
                    <a href="https://api.whatsapp.com/send?text=Hola,..."><img src='imagenes/iconos/whatsapp-circle.png'
                            class="social-icon"></a>
                </div>
            </div>
            <div class="footer-section">
                <div class="poster-brand">
                    <img src="imagenes/logo AMOR QUE TRASCIENDE LA VIDA_FLR (1).png" alt="Amor que trasciende la vida"
                        class="poster-brand-img">

                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<script src="js/jquery.magnific-popup.js"></script>
<script src="js/funciones.js"></script>
<script src="js/main.js"></script>

<script>
    let donationSubmitting = false;

    const form = document.getElementById('payment-form');

    if (form) {
        let donationSubmitting = false;

        form.addEventListener('submit', function (e) {
            const submitButton = form.querySelector('button[type="submit"]');

            if (!form.checkValidity()) {
                donationSubmitting = false;

                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerText = 'Donar';
                }

                return;
            }

            if (donationSubmitting) {
                e.preventDefault();
                return false;
            }

            donationSubmitting = true;

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerText = 'Procesando...';
            }

            setTimeout(function () {
                if (!form.dataset.submitted) {
                    donationSubmitting = false;

                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerText = 'Donar';
                    }
                }
            }, 1500);
        });
    }

    /*     function scrollToElement(elementId) {
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
        } */

    function scrollToElement(id) {
        var element = document.getElementById(id);

        if (!element) {
            return;
        }

        var headerOffset = 100; // altura aprox del navbar mobile
        var elementPosition = element.getBoundingClientRect().top;
        var offsetPosition = elementPosition + window.pageYOffset - headerOffset;

        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
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