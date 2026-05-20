function valida_fono(input) {
    var num = input.value.replace(/\./g, '');
    if (!isNaN(num)) {
        input.value = num;
    } else {
        alert('Solo se permiten números');
        input.value = input.value.replace(/[^\d\.]*/g, '');
    }
}

 document.getElementById('form_voluntario').addEventListener('submit', function(event) {
            if (!document.getElementById('acepta-terminos').checked) {
                alert('Debe aceptar los términos y condiciones para continuar.');
                event.preventDefault(); // Evita que el formulario se envíe
            }
        });

$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();

    // Ocultamos inicialmente todos los campos y el botón de enviar
    $("#verTrabaja").hide();
    $("#verEmailCorporativo").hide();
    $("#verAcompañado").hide();
    $("#verPersonas").hide();  // Aseguramos que el campo de personas esté inicialmente oculto  
    $("#mensaje").hide();
    $("#verRut").hide();
    $("#verNombre").hide();
    $("#verApellidos").hide();
    $("#verPrefijo").hide();
    $("#verFono").hide();
    $("#verEdad").hide();
    $("#verMail").hide();
    $("#verMail2").hide();
    $("#verContacto").hide();
    $("#verApoyoHogar").hide();
    $("#verGrupo").hide();
    $("#verHogar").hide();
    $("#verRegion").hide();
    $("#verComuna").hide();
    $("#verDiasCalle").hide();
    $("#verParticipacion").hide();
    $("#verRegionP").hide();
    $("#verComunaP").hide();
    $("#verOrganizacion").hide();
    $("#verNombreOrganizacion").hide();
    $("#nombreOrganizacion").hide();
    $("#verContactoE").hide();
    $("#verNombreE").hide();
    $("#verPrefijoE").hide();
    $("#verNumeroE").hide();
    $("#verFonoE").hide();
    $("#verDigital").hide();
    $("#boton-enviar").hide();

    // Mostrar/ocultar campos basados en tipo de voluntario
    $("#tipoVoluntario").change(function() {
        var tipoVoluntario = $(this).val();
        if (tipoVoluntario === "Individual" || tipoVoluntario === "Grupal") {
            $("#verTrabaja").show();
            $("#verRut").show();
            $("#verNombre").show();
            $("#verApellidos").show();
            $("#verPrefijo").show();
            $("#verFono").show();
            $("#verEdad").show();
            $("#verMail").show();
            $("#verMail2").show();
            $("#verContacto").show();
            $("#verContactoE").show();
            $("#verParticipacion").show();
            $("#verRegionP").show();
            $("#verComunaP").show();
            $("#verNombreE").show(); 
            $("#verNumeroE").show();
            $("#verPrefijoE").show();
            $("#verFonoE").show();
            $("#verApoyoHogar").show();
            $("#verDiasCalle").show();
            $("#verDigital").show();
            $("#boton-enviar").show();

            if (tipoVoluntario === "Grupal") {
                $("#verTrabaja").hide();
                $("#verGrupo").show();
                $("#verOrganizacion").show();
                $("#verNombreOrganizacion").show();
                $("#nombreOrganizacion").show();
            } else {
               $("#verTrabaja").show();
                $("#verGrupo").hide();
                $("#verOrganizacion").hide();
                $("#verNombreOrganizacion").hide();
                $("#nombreOrganizacion").hide();
            }
        } else {
            // Ocultamos todo si no se selecciona una opción válida
            $("#verTrabaja").hide();
            $("#verRut").hide();
            $("#verNombre").hide();
            $("#verApellidos").hide();
            $("#verPrefijo").hide();
            $("#verFono").hide();
            $("#verEdad").hide();
            $("#verMail").hide();
            $("#verMail2").hide();
            $("#verContacto").hide();
            $("#verApoyoHogar").hide();
            $("#verGrupo").hide();
            $("#verHogar").hide();
            $("#verRegion").hide();
            $("#verComuna").hide();
            $("#verDiasCalle").hide();
            $("#verParticipacion").hide();
            $("#verRegionP").hide();
            $("#verComunaP").hide();
            $("#verOrganizacion").hide();
            $("#verNombreOrganizacion").hide();
            $("#nombreOrganizacion").hide();
            $("#verContactoE").hide();
            $("#verNombreE").hide(); 
            $("#verNumeroE").hide();
            $("#verPrefijoE").hide();
            $("#verFonoE").hide();
            $("#verDigital").hide();
            $("#boton-enviar").hide();
        }
    });

    // Mostrar u ocultar campos de "trabaja en la fundación"
    $("#trabajaFlr").change(function() {
        var trabajaFlr = $(this).val();

        // Si selecciona "Sí" en "¿Trabaja en Fundación?"
        if (trabajaFlr === "Si") {
            $("#verEmailCorporativo").show();  // Mostrar correo electrónico corporativo
            $("#verAcompañado").show();  // Mostrar "¿Vas acompañado?"
        } else {
            $("#verEmailCorporativo").hide();  // Ocultar correo electrónico corporativo
            $("#verAcompañado").hide();  // Ocultar "¿Vas acompañado?"
            $("#emailCorporativo").val('');  // Limpiar el campo de correo corporativo
            $("#acompañado").val('');  // Limpiar el campo "¿Vas acompañado?"
        }
    });

// Mostrar u ocultar "¿Cuántas personas componen su grupo?"
$("#acompañado").change(function() {
    var vaAcompanado = $(this).val();  // Cambia #verAcompañado por #acompañado
    // console.log("Valor de '¿Vas acompañado?':", vaAcompanado); // Imprimir valor en consola

    if (vaAcompanado === "Si") {
        $("#verPersonas").show();  // Mostrar campo de cantidad de personas
    } else if (vaAcompanado === "No") {
        $("#verPersonas").hide();  // Ocultar campo de cantidad de personas
        $("#numPersonas").val('');  // Limpiar el valor del campo
    }
});




    $("#form_voluntario").submit(function(event) {
        var nombre = $("#nombre").val().toLowerCase();
        nombre = nombre.replace(/\w\S*/g, (w) => (w.replace(/^\w/, (c) => c.toUpperCase())));
        $("#nombre").val(nombre);

        var email = $("#email").val().toLowerCase();
        var confirmEmail = $("#confirmEmail").val().toLowerCase();
        $("#email").val(email);

        formateaRut($("#rut").val());

        if (VerificaRut($("#rut").val()) == false) {
            alert('RUT inválido');
            $("#rut").val("");
            $("#rut").focus();
            event.preventDefault();
        } else if ($("#edad").val() == "") {
            alert('Ingrese su Fecha de Nacimiento');
            $("#edad").focus();
            event.preventDefault();
        } else if ($("#nombre").val() == "") {
            alert('Ingrese un nombre');
            $("#nombre").focus();
            event.preventDefault();
        } else if ($("#apellidos").val() == "") {
            alert('Ingrese su apellido paterno');
            $("#apellidos").focus();
            event.preventDefault();
        } else if ($("#email").val().indexOf('@', 0) == -1 || $("#email").val().indexOf('.', 0) == -1) {
            alert('Ingrese un email correcto');
            $("#email").focus();
            event.preventDefault();
        } else if (email !== confirmEmail) {
            alert('Los emails no coinciden. Por favor, verifique.');
            $("#confirmEmail").focus();
            event.preventDefault();
        } else if ($("#prefijo").val() == "") {
            alert('Seleccione un prefijo de país');
            $("#prefijo").focus();
            event.preventDefault();
        } else if ($("#telefono").val().length != 9) {
            alert('El teléfono debe tener 9 dígitos');
            $("#telefono").focus();
            event.preventDefault();
        } else if ($("tipo-organizacion").val() == "") { ///TENGO QUE VER SI FUNCIONA
            alert('Ingrese un dato');
            $("#tipo-organizacion").focus();
        } else if ($("#prefijoE").val() == "") {
            alert('Seleccione un prefijo de país');
            $("#prefijoE").focus();
            event.preventDefault();
        } else if ($("#telefonoE").val().length != 9) {
            alert('El teléfono debe tener 9 dígitos');
            $("#telefonoE").focus();
            event.preventDefault();
        } else {
            $("#boton-enviar").hide();
            $("#mensaje").show();


           // const scriptURL = 'https://script.google.com/macros/s/AKfycbwN9DO1c_6bTeThlpKi7VbwAlpPGkOcDYFN9W0p86Dj_5bfg_gdE_m3BJMnxju6fHTpzQ/exec';
            // const form = document.forms['form_voluntario'];
            // event.preventDefault();
            // fetch(scriptURL, { method: 'POST', body: new FormData(form) })
            //     .then(response => window.location = "gracias")
            //     .catch(error => console.error('Error!', error.message));
             const scriptURL = 'https://script.google.com/macros/s/AKfycbwN9DO1c_6bTeThlpKi7VbwAlpPGkOcDYFN9W0p86Dj_5bfg_gdE_m3BJMnxju6fHTpzQ/exec';
        const endpointURL = 'https://intranet.flrosas.cl/colecta/volunteers/insert';
        const form = document.forms['form_voluntario'];
        const formData = new FormData(form);

        // Enviar datos al scriptURL
        event.preventDefault();
            fetch(scriptURL, { method: 'POST', body: new FormData(form) })
                .then(response => window.location = "gracias")
                .catch(error => console.error('Error!', error.message));
        

        // Enviar datos al endpoint
        const sendToEndpoint = fetch(endpointURL, {
            method: 'POST',
            body: formData
        });

        // Manejar ambas solicitudes en paralelo
        Promise.all([sendToScriptURL, sendToEndpoint])
            .then(responses => {
                // Manejar las respuestas
                const scriptResponse = responses[0];
                const endpointResponse = responses[1];

                if (!scriptResponse.ok) {
                    throw new Error('Error en el envío al scriptURL');
                }

                if (!endpointResponse.ok) {
                    throw new Error('Error en la solicitud al endpoint');
                }

                return Promise.all([scriptResponse.text(), endpointResponse.json()]);
            })
            .then(([scriptData, endpointData]) => {
                if (endpointData.type === "error") {
                    alert(endpointData.message);
                    $("#boton-enviar").show();
                    $("#mensaje").hide();
                } else {
                    window.location = "gracias";
                }
            })
            .catch(error => {
                console.error('Error!', error.message);
                $("#boton-enviar").show();
                $("#mensaje").hide();
            });
            
            
        }
    });

    function formateaRut(rut) {
        
        var actual = rut.replace(/^0+/, "");
        if (actual != '' && actual.length > 1) {
            var sinPuntos = actual.replace(/\./g, "");
            var actualLimpio = sinPuntos.replace(/-/g, "");
            var inicio = actualLimpio.substring(0, actualLimpio.length - 1);
            var dv = actualLimpio.substring(actualLimpio.length - 1);
            var rutPuntos = inicio + "-" + dv;
        }
        var inputRut = document.getElementById("rut");
        inputRut.value = rutPuntos;
        return rutPuntos;
    }

    function VerificaRut(rut) {
        
        if (rut.toString().trim() != '' && rut.toString().indexOf('-') > 0) {
            var caracteres = new Array();
            var serie = new Array(2, 3, 4, 5, 6, 7);
            var dig = rut.toString().substr(rut.toString().length - 1, 1);
            rut = rut.toString().substr(0, rut.toString().length - 2);

            for (var i = 0; i < rut.length; i++) {
                caracteres[i] = parseInt(rut.charAt((rut.length - (i + 1))));
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

    $("#diasCalle").multipleSelect({
        filter: false
    });

    $("#diasCalle").change(function() {
        var selected = $("#diasCalle").val();
        $("#resultadoDiasColectaCalle").val(selected.join(", "));
    });


 $("#comunaP").multipleSelect({
        filter: false
    });

    $("#comunaP").change(function() {
        var selected = $("#comunaP").val();
        $("#resultadoComunasColectaCalle").val(selected.join(", "));
    });

    $("#apoyoHogar").change(function() {
        if ($(this).val() == "SI") {
            $("#verRegion").show();
        } else {
            $("#verRegion").hide();
            $("#verComuna").hide();
            $("#verHogar").hide();
        }
    });

    $("#regionColecta").change(function() {
        if ($(this).val() != "") {
            $("#verComuna").show();
            updateComunas($(this).val());
        } else {
            $("#verComuna").hide();
            $("#verHogar").hide();
        }
    });

    $("#comuna").change(function() {
        if ($(this).val() != "") {
            $("#verHogar").show();
            updateHogares($(this).val());
        } else {
            $("#verHogar").hide();
        }
    });
//selecciona hogar
    function updateComunas(region) {
        var comunas = {
            
            "Región de O'Higgins": ["Chépica"],
            "Región del Maule": ["Curicó", "Talca", "Linares"],
            "Región de Valparaíso": ["Casablanca", "Nogales", "Quillota", "Ventana"],
            "Región de Coquimbo": ["La Serena"],
            "Región del Bio Bio":["Arauco", "Talcahuano"],
            "Región de los Ríos": ["Valdivia"],
            "Región de los Lagos": ["Osorno"],
            "Región Metropolitana de Santiago": ["Santiago", "Lampa", "La Florida", "San José de Maipo", "Independencia","Recoleta", "Isla de Maipo", "Melipilla", "Ñuñoa"]
        };

        var options = '<option value="">Seleccione Comuna</option>';
        if (comunas[region]) {
            comunas[region].forEach(comuna => {
                options += `<option value="${comuna}">${comuna}</option>`;
            });
        }
        $("#comuna").html(options);
    }

    function updateHogares(comuna) {
        var hogares = {
            "Chépica": ["H18 - Sagrados Corazones de Jesús y María"],
            "Curicó": ["H27 - María Olga Tuñón de Barriga"],
            "Talca" : ["H15 - Madre del Buen Consejo"],
            "Linares": [" H17 - Sagrado Corazón de Jesús"],
            "Casablanca": ["H26 - María Inmaculada"],
            "Nogales":["H34 - Nuestra Señora del Carmen"],
            "Ventana":["H37 - Nuestra Señora del Rosario"],
            "Quillota": ["H22 - San Alberto Hurtado"],
            "La Serena":["H14 - La Visitación de María"],
            "Valdivia":["H19 - Padre Pío"],
            "Arauco":["H39 - San Juan Pablo II"],
            "Talcahuano":["H29 - Santa Teresa de Calcuta"],
            "Osorno":["H2 - Santa María de Osorno"],
            "Independencia":["H3 - Nuestra Señora de las Rosas", "H1 - Nuestra Señora de la Merced","H7 - Juan Pablo I", "H12 - Jesús Crucificado", "H28 - Nuestra Señora de Guadalupe" ],
            "Recoleta":["H21 - Hogar San Carlos"],
            "Santiago":["H9 - Hogar Santa Ana", "H4 - Santísima Trinidad" ],
            "Ñuñoa":["H5 - Nuestra Señora de la Paz"],
            "La Florida": ["H20 - Cardenal José María Caro"],
            "San José de Maipo":["H40 - Santos Arcángeles Miguel, Gabriel y Rafael"],
            "Isla de Maipo":["H24 - Nuestra Señora de la Merced"],
            "Melipilla":["H23 - San José"],
            "Lampa":["H6 - María auxiliadora"],
            
            // Añadir más comunas y sus respectivos hogares
        };

        var options = '<option value="">Seleccione Hogar</option>';
        if (hogares[comuna]) {
            hogares[comuna].forEach(hogar => {
                options += `<option value="${hogar}">${hogar}</option>`;
            });
        }
        $("#hogar").html(options);
    }
});
//selecciona ubicacion colecta calle
  $(document).ready(function() {
            var comunasP = {
                "Región de O'Higgins": ["Rancagua", "San Fernando", "Nancagua", "Santa Cruz", "Chépica", "Chimbarongo", "Placilla", "Palmilla", "Peralillo", "Pichilemu", "La Estrella", "Marchihue"],
                "Región del Maule": ["Teno", "Romeral", "Sagrada Familia", "Molina", "Curicó", "Rauco", "Talca", "Linares", "Constitución", "San Javier", "Parral", "Longaví", "Villa Alegre", "Cauquenes", "Maule", "San Rafael", "Pelarco"],
                "Región de Valparaíso": ["Algarrobo", "Casablanca", "Concón", "El Quisco", "El Tabo", "La Calera", "La Cruz", "La Ligua", "Limache", "Nogales", "Olmué", "Puchuncaví", "Quillota", "Quilpué", "Quintero", "San Antonio", "Santo Domingo", "Valparaíso", "Ventana", "Villa Alemana", "Viña del Mar", "Zapallar"],
                "Región de Coquimbo": ["La Serena"],
                "Región de la Araucanía": ["Temuco"],
                "Región del Bio Bio": ["Concepción", "San Pedro", "Talcahuano", "Chiguayante"],
                "Región de Ñuble": ["Chillán"],
                "Región de los Ríos": ["Valdivia"],
                "Región de los Lagos": ["Osorno", "Purranque", "San Pablo", "Puerto Montt", "Castro", "Quellón"],
                "Región Metropolitana de Santiago": ["Santiago", "Lo Barnechea", "Las Condes", "Vitacura", "Providencia", "Colina(Chicureo)", "Lampa", "Puente Alto", "La Florida", "San José de Maipo", "Independencia", "Recoleta", "Isla de Maipo", "Melipilla", "Buin", "Ñuñoa", "La Reina", "Peñalolén"]
            };

            $("#regionColectaCalleP").change(function() {
                var regionSeleccionada = $(this).val();
                var opcionesComuna = "<option value=''>Seleccione Comuna</option>";

                if (regionSeleccionada && comunasP[regionSeleccionada]) {
                    comunasP[regionSeleccionada].forEach(function(comunaP) {
                        opcionesComuna += "<option value='" + comunaP + "'>" + comunaP + "</option>";
                    });
                }

                $("#comunaP").html(opcionesComuna);
                $("#comunaP").multipleSelect('refresh');
                $("#verComunaP").show();
            });

            $("#comunaP").multipleSelect({
                filter: true,
                width: '100%'
            });
        });
        
        
// [ "Región de O'Higgins": ["Rancagua", "San Fernando", "Nancagua", "Santa Cruz", "Chépica", "Chimbarongo", "Placilla", "Palmilla", "Peralillo", "Pichilemu", "La Estrella", "Marchihue"],
//             "Región del Maule": ["Teno", "Romeral", "Sagrada Familia", "Molina", "Curicó", "Rauco", "Talca", "Linares", "Constitución", "San Javier", "Parral", "Longaví", "Villa Alegre", "Cauquenes", "Maule", "San Rafael", "Pelarco"],
//             "Región de Valparaíso": ["Algarrobo", "Casablanca", "Concón", "El Quisco", "El Tabo", "La Calera", "La Cruz", "La Ligua", "Limache", "Nogales", "Olmué", "Puchuncaví", "Quillota", "Quilpué", "Quintero", "San Antonio", "Santo Domingo", "Valparaíso", "Ventana", "Villa Alemana", "Viña del Mar", "Zapallar"],
//             "Región de Coquimbo": ["La Serena"],
//             "Región de la Araucanía": ["Temuco","Arauco"],
//             "Región del Bio Bio":["Talcahuano"],
//             "Región de los Ríos": ["Valdivia"],
//             "Región de los Lagos": ["Osorno", "Purranque", "San Pablo", "Puerto Montt", "Castro", "Quellón"],
//             "Región Metropolitana de Santiago": ["Santiago", "Lo Barnechea", "Las Condes", "Vitacura", "Providencia", "Colina(Chicureo)", "Lampa", "Puente Alto", "La Florida", "San José de Maipo", "Independencia","Recoleta", "Islita", "Melipilla", "Buin", "Ñuñoa", "La Reina", "Peñalolén"]]
