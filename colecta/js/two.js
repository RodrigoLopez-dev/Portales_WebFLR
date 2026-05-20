$("#form_voluntario").submit(function(event) {
    event.preventDefault(); // Evitar el envío del formulario hasta que todo sea validado

    var nombre = $("#nombre").val().toLowerCase();
    nombre = nombre.replace(/\w\S*/g, (w) => (w.replace(/^\w/, (c) => c.toUpperCase()))); // Asegura que el nombre tenga la primera letra en mayúscula
    $("#nombre").val(nombre);

    var email = $("#email").val().toLowerCase();
    var confirmEmail = $("#confirmEmail").val().toLowerCase();
    $("#email").val(email);

    formateaRut($("#rut").val());
    
  $("#telefono").on("blur", function () {
    valida_fono(this);
});
 
    // Si es "Individual" y trabaja en la fundación, validamos solo ciertos campos
    var tipoVoluntario = $("#tipoVoluntario").val();
    var trabajaFlr = $("#trabajaFlr").val();

    // Validación en función de la selección
    if (tipoVoluntario === "Individual") {

    if ($("#regionUbicacion").is(":visible") && $("#regionUbicacion").val() === "") {
        alert("Debe seleccionar una región de ubicación");
        $("#regionUbicacion").focus();
        event.preventDefault();
        return;
    }

    if ($("#comunaUbicacion").is(":visible") && $("#comunaUbicacion").val() === "") {
        alert("Debe seleccionar una comuna de ubicación");
        $("#comunaUbicacion").focus();
        event.preventDefault();
        return;
    }

    if ($("#eligeHogar").is(":visible") && $("#eligeHogar").val() === "") {
        alert("Debe indicar si su participación es para un hogar o región");
        $("#eligeHogar").focus();
        event.preventDefault();
        return;
    }

    if ($("#eligeHogar").val() === "no" && $("#regionApoyo").is(":visible") && $("#regionApoyo").val() === "") {
        alert("Debe seleccionar una región de participación");
        $("#regionApoyo").focus();
        event.preventDefault();
        return;
    }

        // Si es individual, validamos solo los campos visibles
        if (trabajaFlr === "Si") {

    if ($("#emailCorporativo").is(":visible")) {
        const email = $("#emailCorporativo").val();
        if (email === "") {
            alert("Debe ingresar su correo institucional.");
            event.preventDefault();
            return;
        } else if (!email.endsWith("@flrosas.cl")) {
            alert("El correo debe ser institucional (@flrosas.cl)");
            event.preventDefault();
            return;
        }
    }

            // Solo correo corporativo y cuántas personas
            if ($("#emailCorporativo").is(":visible") && $("#emailCorporativo").val() == "") {
                alert('Ingrese el correo corporativo');
                $("#emailCorporativo").focus();
                return;
            }
            if ($("#acompañado").is(":visible") && $("#acompañado").val() === "") {
                alert('Indique si va acompañado');
                $("#acompañado").focus();
                return;
            }
            if ($("#acompañado").val() === "Si" && $("#numPersonas").is(":visible") && $("#numPersonas").val() === "") {
                alert('Indique cuántas personas van con usted');
                $("#numPersonas").focus();
                return;
            }
        } else {
            // Si no trabaja en la fundación, validamos los campos normales
            if ($("#rut").val() == "" || !VerificaRut($("#rut").val())) {
                alert('Ingrese un RUT válido');
                $("#rut").focus();
                return;
            }
            if ($("#nombre").val() == "") {
                alert('Ingrese un nombre');
                $("#nombre").focus();
                return;
            }
            if (!valida_email($("#email"))) {
                alert('Ingrese un email válido');
                $("#email").focus();
                return;
            }
            if (email !== confirmEmail) {
                alert('Los emails no coinciden. Por favor, verifique.');
                $("#confirmEmail").focus();
                return;
            }
            if ($("#telefono").val().length != 9) {
                alert('El teléfono debe tener 9 dígitos');
                $("#telefono").focus();
                return;
            }
            if ($("#tipoParticipacion").val() == "") {
                alert('Seleccione el tipo de participación');
                $("#tipoParticipacion").focus();
                return;
            }
            if ($("#regionColectaCalleP").val() == "") {
                alert('Seleccione la región');
                $("#regionColectaCalleP").focus();
                return;
            }
            if ($("#comunaP").val() == "") {
                alert('Seleccione la comuna');
                $("#comunaP").focus();
                return;
            }
            if ($("#hogarAyuda").val() == "") {
                alert('Seleccione el hogar a ayudar');
                $("#hogarAyuda").focus();
                return;
            }
        }
      
    } else if (tipoVoluntario === "Grupal") {
        // Si es grupal, validamos los campos básicos
         if ($("#tipo-organizacion").val() == "") {
            alert('Seleccione el tipo de organización');
            $("#tipo-organizacion").focus();
            return;
        }
        if ($("#tipo-organizacion").val() == "otra") {
            const otra = $("#otraOrganizacion").val().trim();
        if (otra === "") {
            alert('Debe especificar el tipo de organización');
            $("#otraOrganizacion").focus();
            return;
    }}
        if ($("#nombreOrganizacion").val() == "") {
            alert('Ingrese el nombre de la organización');
            $("#nombreOrganizacion").focus();
            return;
        }
        if ($("#tipoParticipacion").val() == "") {
            alert('Seleccione el tipo de participación');
            $("#tipoParticipacion").focus();
            return;
            }
        if ($("#tipoColecta").val() == "") {
            alert('Seleccione el tipo de colecta');
            $("#tipoColecta").focus();
            return;
        }

        // Si es "colecta_calle", validamos los campos adicionales
        if ($("#tipoColecta").val() === "colecta_calle") {
            if ($("#numeroPersonas").val() == "") {
                alert('Indique cuántas personas se comprometen en la colecta');
                $("#numeroPersonas").focus();
                return;
            }
            if ($("#nombreEncargado").val() == "") {
                alert('Ingrese el nombre del encargado');
                $("#nombreEncargado").focus();
                return;
            }
            if ($("#rutEncargado").val() == "" || !VerificaRut($("#rutEncargado").val())) {
                alert('Ingrese un RUT válido del encargado');
                $("#rutEncargado").focus();
                return;
            }
            if ($("#emailEncargado").val() == "") {
                alert('Ingrese el email del encargado');
                $("#emailEncargado").focus();
                return;
            }
            if ($("#confirmEmailEncargado").val() == "") {
                alert('Confirme el email del encargado');
                $("#confirmEmailEncargado").focus();
                return;
            }
            if ($("#fonoEncargado").val() == "") {
                alert('Ingrese el telefono del encargado');
                $("#fonoEncargado").focus();
                return;
            }
            if ($("#regionColectaCalleP").val() == "") {
                alert('Seleccione la región');
                $("#regionColectaCalleP").focus();
                return;
            }
            if ($("#comunaP").val() == "") {
                alert('Seleccione la comuna');
                $("#comunaP").focus();
                return;
            }
            if ($("#hogarAyuda").val() == "") {
                alert('Seleccione el hogar a ayudar');
                $("#hogarAyuda").focus();
                return;
            }
        } else {
            // Si es "colecta_organizacion", validamos los campos adicionales
            if ($("#nombreEncargado").val() == "") {
                alert('Ingrese el nombre del encargado');
                $("#nombreEncargado").focus();
                return;
            }
            if ($("#rutEncargado").val() == "" || !VerificaRut($("#rutEncargado").val())) {
                alert('Ingrese un RUT válido del encargado');
                $("#rutEncargado").focus();
                return;
            }
            if ($("#emailEncargado").val() == "") {
                alert('Ingrese el email del encargado');
                $("#emailEncargado").focus();
                return;
            }
            if ($("#confirmEmailEncargado").val() == "") {
                alert('Confirme el email del encargado');
                $("#confirmEmailEncargado").focus();
                return;
            }
              if ($("#fonoEncargado").val() == "") {
                alert('Ingrese el telefono del encargado');
                $("#fonoEncargado").focus();
                return;
            }
            if ($("#regionColectaCalleP").val() == "") {
                alert('Seleccione la región');
                $("#regionColectaCalleP").focus();
                return;
            }
            if ($("#comunaP").val() == "") {
                alert('Seleccione la comuna');
                $("#comunaP").focus();
                return;
            }
            if ($("#hogarAyuda").val() == "") {
                alert('Seleccione el hogar a ayudar');
                $("#hogarAyuda").focus();
                return;
            }
        }
       
    }

    // Si todo es correcto, mostramos mensaje de éxito y realizamos el envío
    $("#boton-enviar").hide();
    $("#mensaje").show();

    const scriptURL = 'https://script.google.com/macros/s/AKfycbwl9kVbXIDbOno5eg9F5vV7729fwJxEg1-74iR7vLk3nwaesHdxvQWgycy-uRR85JqF/exec';
    const form = document.forms['form_voluntario'];
    const formData = new FormData(form);

    // Enviar datos al scriptURL
    event.preventDefault();
    fetch(scriptURL, { method: 'POST', body: formData })
        .then(response => window.location = "gracias.php")
        .catch(error => console.error('Error!', error.message));
});

// Función para formatear el RUT
function formateaRut(rut) {
    if (rut) {
        // Eliminar puntos y guion
        var sinPuntos = rut.replace(/\./g, "").replace(/-/g, "");
        if (sinPuntos.length > 1) {
            var inicio = sinPuntos.substring(0, sinPuntos.length - 1);
            var dv = sinPuntos.substring(sinPuntos.length - 1);
            return inicio + "-" + dv;
        }
    }
    return rut;
}

// Función para verificar el RUT
function VerificaRut(rut) {
    if (rut.toString().trim() !== '' && rut.toString().indexOf('-') > 0) {
        var caracteres = [];
        var serie = [2, 3, 4, 5, 6, 7];
        var dig = rut.toString().substr(rut.toString().length - 1, 1);
        rut = rut.toString().substr(0, rut.toString().length - 2);

        for (var i = 0; i < rut.length; i++) {
            caracteres[i] = parseInt(rut.charAt((rut.length - (i + 1))));  // Se invierte la cadena del RUT
        }

        var sumatoria = 0;
        var k = 0;
        var resto = 0;

        for (var j = 0; j < caracteres.length; j++) {
            if (k == 6) {
                k = 0;
            }
            sumatoria += parseInt(caracteres[j]) * parseInt(serie[k]);
            k++;
        }

        resto = sumatoria % 11;
        dv = 11 - resto;

        if (dv == 10) {
            dv = "K";
        } else if (dv == 11) {
            dv = 0;
        }

        if (dv.toString().trim().toUpperCase() == dig.toString().trim().toUpperCase()) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
 // Valida que el email sea corporativo (@flrosas.cl)
function valida_emailC(input) {
    const email = input.value;
    if (!email.endsWith("@flrosas.cl")) {
        alert("El correo debe ser institucional (@flrosas.cl)");
        // input.focus();
        return false;
    }
    return true;
}

// Valida que solo se ingresen números positivos
function valida_number(input) {
    const val = input.value;
    if (val !== "" && (!/^\d+$/.test(val) || parseInt(val) < 0)) {
        alert("Ingrese solo números válidos.");
        input.value = "";
        input.focus();
        return false;
    }
    return true;
}
function valida_email($input) {
    const email = $input.val().trim();
    const regex = /^[^@]+@[^@]+\.[a-zA-Z]{2,}$/;

    if (!regex.test(email)) {
        alert("Por favor, ingrese un correo electrónico válido.");
        $input.focus();
        return false;
    }

    return true;
}


function valida_fono(input) {
    const fono = input.value.trim();
    const regex = /^[0-9]{9}$/;

    // Limpia mensajes anteriores
    $(input).removeClass("is-invalid");
    $(input).next(".invalid-feedback").remove();

    if (!regex.test(fono)) {
        $(input).addClass("is-invalid")
               .after('<div class="invalid-feedback d-block">El teléfono debe contener exactamente 9 dígitos numéricos.</div>');
        return false; // La validación sigue indicando error, pero no impide seguir escribiendo
    }

    return true;
}


$("#tipo-organizacion").change(function () {
    const valor = $(this).val();
    if (valor === "otra") {
        $("#verOtraOrganizacion").show();
    } else {
        $("#verOtraOrganizacion").hide();
        $("#otraOrganizacion").val(""); // limpiar el campo si se cambia de idea
    }
});

