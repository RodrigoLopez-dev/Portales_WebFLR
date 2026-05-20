function noPuntoComa( event ) {
  
    var e = event || window.event;
    var key = e.keyCode || e.which;

    if ( key === 110 || key === 190 || key === 188 ) {     
        
       e.preventDefault();     
    }
}

function formatearConSeparadorDeMiles(numero) {
    var separador = "."; // Separador de miles
  
    // Convertir el número a una cadena y eliminar espacios en blanco
    var numeroString = String(numero).trim();
  
    // Buscar la posición del punto decimal (si existe)
    var posicionPunto = numeroString.indexOf(".");
  
    // Separar la parte entera y la parte decimal del número
    var parteEntera = posicionPunto > -1 ? numeroString.slice(0, posicionPunto) : numeroString;
    var parteDecimal = posicionPunto > -1 ? numeroString.slice(posicionPunto) : "";
  
    // Agregar el separador de miles a la parte entera del número
    var parteEnteraFormateada = parteEntera.replace(/\B(?=(\d{3})+(?!\d))/g, separador);
  
    // Devolver el número formateado
    return parteEnteraFormateada + parteDecimal;
  }

  function validarRut(rut) {
    rut = rut.replace(/[^0-9kK]/g, ''); // Eliminar caracteres no numéricos ni 'k' de verificación
    if (rut.length < 3) {
      return false;
    }
    var dv = rut.charAt(rut.length - 1); // Último dígito o 'k' de verificación
    var rutNumerico = parseInt(rut.slice(0, -1)); // Parte numérica del RUT

    if (isNaN(rutNumerico)) {
      return false;
    }

    var suma = 0;
    var factor = 2;
    var digito;

    // Calcular la suma ponderada de los dígitos del RUT
    for (var i = rutNumerico.toString().length - 1; i >= 0; i--) {
      suma += factor * rutNumerico.toString().charAt(i);
      factor = factor >= 7 ? 2 : factor + 1;
    }

    // Calcular el dígito verificador esperado
    var digitoEsperado = 11 - (suma % 11);
    digito = digitoEsperado === 11 ? '0' : digitoEsperado === 10 ? 'K' : digitoEsperado.toString();

    // Comparar el dígito verificador calculado con el ingresado
    return digito.toUpperCase() === dv.toUpperCase();
  }
  
   function formatearRut(input) {
  // Eliminar espacios en blanco y guiones actuales
  var rut = input.value.replace(/\s|-/g, '');
  
  // Verificar si el RUT tiene al menos un dígito
  if (rut.length < 1) return;
  
  // Separar el RUT en parte numérica y dígito verificador
  var rutNumerico = rut.slice(0, -1);
  var digitoVerificador = rut.slice(-1).toUpperCase();
  
  // Formatear el RUT con guion al final
  var rutFormateado = rutNumerico + '-' + digitoVerificador;
  
  // Actualizar el valor del input con el RUT formateado
  input.value = rutFormateado;
}

  