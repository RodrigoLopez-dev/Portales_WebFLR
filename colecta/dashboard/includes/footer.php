</div>

<script src="<?php echo asset('dashboard/assets/js/core/jquery.min.js'); ?>"></script>
<script src="<?php echo asset('dashboard/assets/js/core/popper.min.js'); ?>"></script>
<script src="<?php echo asset('dashboard/assets/js/core/bootstrap-material-design.min.js'); ?>"></script>
<script src="<?php echo asset('dashboard/assets/js/plugins/perfect-scrollbar.jquery.min.js'); ?>"></script>
<script src="<?php echo asset('dashboard/assets/js/material-dashboard.js'); ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var alerts = document.querySelectorAll('.alert');

        for (var i = 0; i < alerts.length; i++) {
            (function (alertBox) {
                setTimeout(function () {
                    alertBox.style.transition = 'opacity 0.5s ease';
                    alertBox.style.opacity = '0';

                    setTimeout(function () {
                        if (alertBox.parentNode) {
                            alertBox.parentNode.removeChild(alertBox);
                        }
                    }, 500);
                }, 4000);
            })(alerts[i]);
        }
    });
</script>

<?php if (isset($extraScripts)) {
    echo $extraScripts;
} ?>

</body>

</html>