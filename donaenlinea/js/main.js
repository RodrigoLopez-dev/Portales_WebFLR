$(document).ready(function () {
    $('#menuToggle').on('click', function () {
        $('.menu-options').toggleClass('active');
    });

    $('.donation').on('click', function () {
        $('.donation').removeClass('selected');
        $(this).addClass('selected');

        var amount = $(this).val();

        if (amount === '') {
            $('#custom-amount').css('display', 'inline-block').focus();
            return;
        }

        $('#custom-amount').css('display', 'none');
        $('#monto').val(amount);
        $('#texto-donacion').text('El monto a donar es de $' + formatearConSeparadorDeMiles(amount));
    });

    $('#custom-amount').on('input change', function () {
        var amount = $(this).val();

        $('#monto').val(amount);

        if (amount) {
            $('#texto-donacion').text('El monto a donar es de $' + formatearConSeparadorDeMiles(amount));
        } else {
            $('#texto-donacion').text('');
        }
    });

    $('.payment-button').on('click', function () {
        var paymentMethod = $(this).data('payment');

        $('#payment').val(paymentMethod);
        $('#payment-modal').modal('show');
    });

    $('#payment-form').on('submit', function (e) {
        var monto = $('#monto').val();
        var payment = $('#payment').val();

        if (!monto) {
            alert('Debes seleccionar un monto');
            $('#payment-modal').modal('hide');
            e.preventDefault();
            return false;
        }

        if (!payment) {
            alert('Debes seleccionar un método de pago');
            e.preventDefault();
            return false;
        }

        var rut = $('#rut').val();

        if (!validarRut(rut)) {
            alert('Rut inválido');
            $('#rut').focus();
            e.preventDefault();
            return false;
        }

        var telefono = $('#telefono').val();

        if (telefono.length !== 9 || !/^\d{9}$/.test(telefono)) {
            alert('El número de teléfono debe tener 9 dígitos');
            $('#telefono').focus();
            e.preventDefault();
            return false;
        }
    });
});