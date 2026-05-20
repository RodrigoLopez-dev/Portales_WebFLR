function qr(url) {
  var textqr = url;
  var sizeqr = "200";
  parametros = { textqr: textqr, sizeqr: sizeqr };
  $.ajax({
    type: "POST",
    url: "qr/qr.php",
    data: parametros,
    success: function (datos) {
      $(".qr_mach").html(datos);
    },
  });
}
