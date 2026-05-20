$(document).ready(function() {

    $('#menuToggle').on('click', function() {
        $('.menu-options').toggleClass('active');
    });
    

    function actualizarMonto(amount) {
        const amountValue = (amount || '').toString().trim();

        if (!amountValue) {
            $('#monto').val('');
            $('#texto-donacion').text('Ingresa un monto para donar');
            return;
        }

        $('#monto').val(amountValue);
        $('#texto-donacion').text('El monto a donar es de $' + formatearConSeparadorDeMiles(amountValue));
    }

    $('.donation').on('click', function(event) {
        if ($(event.target).closest('.donation-otro-input').length) {
            return;
        }

        $('.donation').removeClass('selected');
        $(this).addClass('selected');

        const amount = $(this).val();
        if (amount === '') {
            const customInput = $(this).find('.donation-otro-input');
            actualizarMonto(customInput.val());
            setTimeout(function() {
                customInput.trigger('focus');
            }, 0);
        } else {
            actualizarMonto(amount);
        }
    });

    $('.donation-otro-input').on('click', function(event) {
        event.stopPropagation();
        const customButton = $(this).closest('.donation');
        $('.donation').removeClass('selected');
        customButton.addClass('selected');
        actualizarIconosRegalo();
        actualizarMonto($(this).val());
    });

    $('.donation-otro-input').on('input', function() {
        const customButton = $(this).closest('.donation');
        if (customButton.hasClass('selected')) {
            actualizarMonto($(this).val());
        }
    });

    const giftIconOpen = 'imagenes/fondo/regalos_de_matrimonio/botones/regalo_abierto.png';
    const giftIconClosed = 'imagenes/fondo/regalos_de_matrimonio/botones/regalo_cerrado.png';
    const giftIconSelected = 'imagenes/fondo/regalos_de_matrimonio/botones/regalo.png';

    function actualizarIconosRegalo() {
        $('.donation-code .donation-gift-icon').each(function() {
            const button = $(this).closest('.donation-code');
            $(this).attr('src', button.hasClass('selected') ? giftIconSelected : giftIconOpen);
        });
    }

    actualizarIconosRegalo();

    $('.donation-code').on('mouseenter', function() {
        if (!$(this).hasClass('selected')) {
            $(this).find('.donation-gift-icon').attr('src', giftIconClosed);
        }
    });

    $('.donation-code').on('mouseleave', function() {
        if (!$(this).hasClass('selected')) {
            $(this).find('.donation-gift-icon').attr('src', giftIconOpen);
        }
    });

    $('.donation-code').on('click', function() {
        actualizarIconosRegalo();
    });

   
    // Al hacer clic en un botón de método de pago
    $('.payment-button').click(function() {
      
      var paymentMethod = $(this).attr('id'); // Obtener el método de pago del botón de pago
      $('#payment').val(paymentMethod); // Establecer el método de pago en el campo de formulario con id "payment"
      $('#payment-modal').modal('show'); // Abrir el modal de pago
    
     });


    $('#payment-form').on('submit', function(e) {
      // Validar que el input monto no esté vacío
      var monto = $('#monto').val();
      if (!monto) {
          alert('Debes seleccionar un monto');
          $('.modal-body').closest('.modal').modal('hide');
          e.preventDefault(); // Detener el envío del formulario
          return;
      }

      // Validar el rut
      var rut = $('#rut').val();
      if (!validarRut(rut)) {
          alert('Rut inválido');
          $('#rut').focus();
          e.preventDefault(); // Detener el envío del formulario
          return;
      }  

      var telefono = $('#telefono').val();
      if (telefono.length !== 9 || !/^\d{9}$/.test(telefono)) {
          alert('El número de teléfono debe tener 9 dígitos');
          $('#telefono').focus();
          e.preventDefault(); // Detener el envío del formulario
          return;
      }




  });
  
       
  });
  