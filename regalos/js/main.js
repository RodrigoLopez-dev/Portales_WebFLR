$(document).ready(function () {
  $("#menuToggle").on("click", function () {
    $(".menu-options").toggleClass("active");
  });

  function actualizarMonto(amount) {
    const amountValue = (amount || "").toString().trim();

    if (!amountValue) {
      $("#monto").val("");
      $("#texto-donacion").text("Ingresa un monto para donar");
      return;
    }

    $("#monto").val(amountValue);
    $("#texto-donacion").text(
      "El monto a donar es de $" + formatearConSeparadorDeMiles(amountValue),
    );
  }

  $(".donation").on("click", function (event) {
    if ($(event.target).closest(".donation-otro-input").length) {
      return;
    }

    $(".donation").removeClass("selected");
    $(this).addClass("selected");

    const amount = $(this).val();

    if (amount === "") {
      const customInput = $(this).find(".donation-otro-input");
      actualizarMonto(customInput.val());

      setTimeout(function () {
        customInput.trigger("focus");
      }, 0);
    } else {
      actualizarMonto(amount);
    }
  });

  $(".donation-otro-input").on("click", function (event) {
    event.stopPropagation();

    const customButton = $(this).closest(".donation");

    $(".donation").removeClass("selected");
    customButton.addClass("selected");

    actualizarIconosRegalo();
    actualizarMonto($(this).val());
  });

  $(".donation-otro-input").on("input", function () {
    const customButton = $(this).closest(".donation");

    if (customButton.hasClass("selected")) {
      actualizarMonto($(this).val());
    }
  });

  const giftIconOpen = "imagenes/botones/regalo_abierto.png";
  const giftIconClosed = "imagenes/botones/regalo_cerrado.png";
  const giftIconSelected = "imagenes/botones/regalo.png";

  function actualizarIconosRegalo() {
    $(".donation-code .donation-gift-icon").each(function () {
      const button = $(this).closest(".donation-code");

      $(this).attr(
        "src",
        button.hasClass("selected") ? giftIconSelected : giftIconOpen,
      );
    });
  }

  actualizarIconosRegalo();

  $(".donation-code").on("mouseenter", function () {
    if (!$(this).hasClass("selected")) {
      $(this).find(".donation-gift-icon").attr("src", giftIconClosed);
    }
  });

  $(".donation-code").on("mouseleave", function () {
    if (!$(this).hasClass("selected")) {
      $(this).find(".donation-gift-icon").attr("src", giftIconOpen);
    }
  });

  $(".donation-code").on("click", function () {
    actualizarIconosRegalo();
  });

  $(".payment-button").on("click", function () {
    const paymentMethod = $(this).data("payment");

    if (!paymentMethod) {
      alert("Medio de pago no válido");
      return;
    }

    $("#payment").val(paymentMethod);
    $("#payment-modal").modal("show");
  });

  $("#payment-form").on("submit", function (e) {
    const monto = $("#monto").val();
    const montoNumerico = parseInt(monto, 10);

    if (!monto || isNaN(montoNumerico) || montoNumerico <= 0) {
      alert("Debes seleccionar un monto válido");
      $("#payment-modal").modal("hide");
      e.preventDefault();
      return;
    }

    const payment = $("#payment").val();

    if (!payment) {
      alert("Debes seleccionar un medio de pago");
      e.preventDefault();
      return;
    }

    const rut = $("#rut").val();

    if (!validarRut(rut)) {
      alert("Rut inválido");
      $("#rut").focus();
      e.preventDefault();
      return;
    }

    const telefono = $("#telefono").val();

    if (telefono.length !== 9 || !/^\d{9}$/.test(telefono)) {
      alert("El número de teléfono debe tener 9 dígitos");
      $("#telefono").focus();
      e.preventDefault();
      return;
    }
  });
});
