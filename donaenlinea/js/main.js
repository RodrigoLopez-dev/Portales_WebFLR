$(document).ready(function() {

    $('#menuToggle').on('click', function() {
        $('.menu-options').toggleClass('active');
    });
    

    $('.donation').click(function() {

        $('.donation').removeClass('selected');
        $(this).addClass('selected');
        
        var amount = $(this).val();
        if (amount === '') {
            $('#custom-amount').css('display', 'inline-block');
            $('#custom-amount').focus();
            $('#custom-amount').change(function() {
                amount = $(this).val();
                $('#monto').val(amount);
                $('#texto-donacion').text('El monto a donar es de $'+formatearConSeparadorDeMiles(amount));
                $('#custom-amount').css('display', 'none');
            });
        } else {
            $('#custom-amount').css('display', 'none');
            $('#monto').val(amount);
            $('#texto-donacion').text('El monto a donar es de $'+formatearConSeparadorDeMiles(amount));
        }
    });


    $('.donation').click(function() {

        $('.donation').removeClass('selected');
        $(this).addClass('selected');
        
        var amount = $(this).val();
        if (amount === '') {
            $('#custom-amount2').css('display', 'inline-block');
            $('#custom-amount2').focus();
            $('#custom-amount2').change(function() {
                amount = $(this).val();
                $('#monto').val(amount);
                $('#texto-donacion').text('El monto a donar es de $'+formatearConSeparadorDeMiles(amount));
                $('#custom-amount2').css('display', 'none');
            });
        } else {
            $('#custom-amount2').css('display', 'none');
            $('#monto').val(amount);
            $('#texto-donacion').text('El monto a donar es de $'+formatearConSeparadorDeMiles(amount));
        }
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
  