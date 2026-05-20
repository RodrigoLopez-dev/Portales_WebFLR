function noPuntoComa(event) {
  var e = event || window.event;
  var key = e.keyCode || e.which;

  if (key === 110 || key === 190 || key === 188) {
    e.preventDefault();
  }
}

function formatearConSeparadorDeMiles(numero) {
  var separador = ".";
  var numeroString = String(numero).trim();

  var posicionPunto = numeroString.indexOf(".");
  var parteEntera =
    posicionPunto > -1 ? numeroString.slice(0, posicionPunto) : numeroString;
  var parteDecimal =
    posicionPunto > -1 ? numeroString.slice(posicionPunto) : "";

  var parteEnteraFormateada = parteEntera.replace(
    /\B(?=(\d{3})+(?!\d))/g,
    separador,
  );

  return parteEnteraFormateada + parteDecimal;
}

function validarRut(rut) {
  rut = rut.replace(/[^0-9kK]/g, "");

  if (rut.length < 3) {
    return false;
  }

  var dv = rut.charAt(rut.length - 1);
  var rutNumerico = parseInt(rut.slice(0, -1), 10);

  if (isNaN(rutNumerico)) {
    return false;
  }

  var suma = 0;
  var factor = 2;
  var rutString = String(rutNumerico);

  for (var i = rutString.length - 1; i >= 0; i--) {
    suma += factor * parseInt(rutString.charAt(i), 10);
    factor = factor >= 7 ? 2 : factor + 1;
  }

  var digitoEsperado = 11 - (suma % 11);
  var digito =
    digitoEsperado === 11
      ? "0"
      : digitoEsperado === 10
        ? "K"
        : String(digitoEsperado);

  return digito.toUpperCase() === dv.toUpperCase();
}

function formatearRut(input) {
  var rut = input.value.replace(/[^0-9kK]/g, "");

  if (rut.length < 2) {
    input.value = rut;
    return;
  }

  var rutNumerico = rut.slice(0, -1);
  var digitoVerificador = rut.slice(-1).toUpperCase();

  input.value = rutNumerico + "-" + digitoVerificador;
}
