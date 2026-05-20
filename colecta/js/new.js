
var comunasYRegion = {
  "Región de Arica y Parinacota": ["Arica", "Camarones", "Putre", "General Lagos"],
  "Región de Tarapacá": ["Iquique", "Alto Hospicio", "Pozo Almonte", "Camiña", "Colchane", "Huara", "Pica"],
  "Región de Antofagasta": ["Antofagasta", "Mejillones", "Sierra Gorda", "Taltal", "Calama", "Ollagüe", "San Pedro de Atacama", "Tocopilla", "María Elena"],
  "Región de Atacama": ["Copiapó", "Caldera", "Tierra Amarilla", "Chañaral", "Diego de Almagro", "Vallenar", "Freirina", "Huasco", "Alto del Carmen"],
  "Región de Coquimbo": ["La Serena", "Coquimbo", "Andacollo", "La Higuera", "Paiguano", "Vicuña", "Illapel", "Canela", "Los Vilos", "Salamanca", "Ovalle", "Combarbalá", "Monte Patria", "Punitaqui", "Río Hurtado"],
  "Región de Valparaíso": ["Valparaíso", "Casablanca", "Concón", "Juan Fernández", "Puchuncaví", "Quilpué", "Quintero", "Villa Alemana", "Viña del Mar", "Isla de Pascua", "La Calera", "Hijuelas", "La Cruz", "Nogales", "Quillota", "Algarrobo", "Cartagena", "El Quisco", "El Tabo", "San Antonio", "Santo Domingo", "Petorca", "Cabildo", "La Ligua", "Papudo", "Zapallar"],
  "Región Metropolitana de Santiago": ["Santiago", "Cerrillos", "Cerro Navia", "Conchalí", "El Bosque", "Estación Central", "Huechuraba", "Independencia", "La Cisterna", "La Florida", "La Granja", "La Pintana", "La Reina", "Las Condes", "Lo Barnechea", "Lo Espejo", "Lo Prado", "Macul", "Maipú", "Ñuñoa", "Pedro Aguirre Cerda", "Peñalolén", "Providencia", "Pudahuel", "Quilicura", "Quinta Normal", "Recoleta", "Renca", "San Joaquín", "San Miguel", "San Ramón", "Vitacura", "Puente Alto", "Pirque", "San José de Maipo", "Colina", "Lampa", "Tiltil", "Melipilla", "Alhué", "Curacaví", "María Pinto", "San Pedro", "Talagante", "El Monte", "Isla de Maipo", "Padre Hurtado", "Peñaflor"],
  "Región de O'Higgins": ["Rancagua", "Codegua", "Coinco", "Coltauco", "Doñihue", "Graneros", "Las Cabras", "Machalí", "Malloa", "Mostazal", "Olivar", "Peumo", "Pichidegua", "Quinta de Tilcoco", "Rengo", "Requínoa", "San Vicente", "Pichilemu", "La Estrella", "Litueche", "Marchigüe", "Navidad", "Paredones", "San Fernando", "Chépica", "Chimbarongo", "Lolol", "Nancagua", "Palmilla", "Peralillo", "Placilla", "Pumanque", "Santa Cruz"],
  "Región del Maule": ["Talca", "San Clemente", "Pelarco", "Pencahue", "Maule", "San Rafael", "Curepto", "Constitución", "Empedrado", "Río Claro", "Linares", "Colbún", "Longaví", "Parral", "Retiro", "San Javier", "Villa Alegre", "Yerbas Buenas", "Curicó", "Hualañé", "Licantén", "Molina", "Rauco", "Romeral", "Sagrada Familia", "Teno", "Vichuquén"],
  "Región de Ñuble": ["Chillán", "Bulnes", "Chillán Viejo", "Cobquecura", "Coelemu", "Coihueco", "El Carmen", "Ninhue", "Ñiquén", "Pemuco", "Pinto", "Portezuelo", "Quillón", "Quirihue", "Ránquil", "San Carlos", "San Fabián", "San Ignacio", "San Nicolás", "Treguaco", "Yungay"],
  "Región del Biobío": ["Concepción", "Coronel", "Chiguayante", "Florida", "Hualpén", "Hualqui", "Lota", "Penco", "San Pedro de la Paz", "Santa Juana", "Talcahuano", "Tomé", "Yumbel", "Cabrero", "Laja", "Los Ángeles", "Mulchén", "Nacimiento", "Negrete", "Quilaco", "Quilleco", "San Rosendo", "Santa Bárbara", "Tucapel", "Antuco", "Alto Biobío", "Arauco", "Cañete", "Contulmo", "Curanilahue", "Lebu", "Los Álamos", "Tirúa"],
  "Región de La Araucanía": ["Temuco", "Carahue", "Cunco", "Curarrehue", "Freire", "Galvarino", "Gorbea", "Lautaro", "Loncoche", "Melipeuco", "Nueva Imperial", "Padre Las Casas", "Perquenco", "Pitrufquén", "Pucón", "Saavedra", "Teodoro Schmidt", "Toltén", "Vilcún", "Villarrica", "Cholchol", "Angol", "Collipulli", "Curacautín", "Ercilla", "Lonquimay", "Los Sauces", "Lumaco", "Purén", "Renaico", "Traiguén", "Victoria"],
  "Región de Los Ríos": ["Valdivia", "Corral", "Lanco", "Los Lagos", "Máfil", "Mariquina", "Paillaco", "Panguipulli", "La Unión", "Futrono", "Lago Ranco", "Río Bueno"],
  "Región de Los Lagos": ["Puerto Montt", "Calbuco", "Cochamó", "Fresia", "Frutillar", "Los Muermos", "Llanquihue", "Maullín", "Puerto Varas", "Castro", "Ancud", "Chonchi", "Curaco de Vélez", "Dalcahue", "Puqueldón", "Queilén", "Quellón", "Quemchi", "Quinchao", "Osorno", "Puerto Octay", "Purranque", "Puyehue", "Río Negro", "San Juan de la Costa", "San Pablo", "Chaitén", "Futaleufú", "Hualaihué", "Palena"],
  "Región de Aysén": ["Coyhaique", "Lago Verde", "Aysén", "Cisnes", "Guaitecas", "Cochrane", "O'Higgins", "Tortel", "Chile Chico", "Río Ibáñez"],
  "Región de Magallanes y de la Antártica Chilena": ["Punta Arenas", "Laguna Blanca", "Río Verde", "San Gregorio", "Cabo de Hornos", "Antártica", "Porvenir", "Primavera", "Timaukel", "Natales", "Torres del Paine"]
};

$(document).ready(function() {

function resetCampos() {
    // Ocultar todos los campos posibles antes de mostrar los que correspondan
    $("#verTrabaja").hide();
    $("#verEmailCorporativo").hide();
    $("#verAcompañado").hide();
    $("#verPersonas").hide();
    $("#verRut").hide();
    $("#verNombre").hide();
    $("#verTipoParticipacion").hide();
    $("#verMail").hide();
    $("#verMail2").hide();
    $("#verFono").hide();
    $("#verEdad").hide();
    $("#verApoyoHogar").hide();
    $("#verRegionP").hide();
    $("#verComunaP").hide();
    $("#verHogarAyuda").hide();
    $("#verBotonEnviar").hide();
    $("#verOrganizacion").hide();
    $("#verNombreOrganizacion").hide();
    $("#verTipoParticipacionGrupal").hide();
    $("#verTipoColecta").hide();
    $("#verNumeroPersonas").hide();
    $("#verDatosEncargado").hide();
    $("#verRegionUbicacion").hide();
    $("#verComunaUbicacion").hide();
    $("#verParticipacion").hide();
    $("#verRegionApoyo").hide();
}

       $('[data-toggle="tooltip"]').tooltip();
       
    // Inicialmente ocultamos los campos
    $("#mensaje").hide();
    $("#verTrabaja").hide();
    $("#verEmailCorporativo").hide();
    $("#verAcompañado").hide();
    $("#verPersonas").hide();
    $("#verRut").hide();
    $("#verNombre").hide();
    $("#verTipoParticipacion").hide();
    $("#verMail").hide();
    $("#verMail2").hide();
    $("#verFono").hide();
    $("#verEdad").hide();
    $("#verApoyoHogar").hide();
    $("#verRegionP").hide();
    $("#verComunaP").hide();
    $("#verHogarAyuda").hide();
    $("#verBotonEnviar").hide();
    $("#verOrganizacion").hide();
    $("#verNombreOrganizacion").hide();
    $("#verTipoParticipacionGrupal").hide();
    $("#verTipoColecta").hide(); // Ocultamos inicialmente el tipo de colecta
    $("#verNumeroPersonas").hide();
    $("#verDatosEncargado").hide();
   

    // Lógica para mostrar campos según el tipo de voluntariado
    $("#tipoVoluntario").change(function() {
resetCampos();
        var tipoVoluntario = $(this).val();

        // Si el tipo de voluntario es Grupal
        if (tipoVoluntario === "Grupal") {
            // Mostrar solo los campos relacionados con la opción grupal
            $("#verOrganizacion").show();
            $("#verNombreOrganizacion").show();
            $("#verTipoParticipacionGrupal").show();
            $("#verTipoColecta").show(); // Mostrar el campo de Tipo de Colecta

            // Ocultar todos los demás campos relacionados con los datos del encargado y personas
            $("#verRut").hide();
            $("#verNombre").hide();
            $("#verTipoParticipacion").hide();
            $("#verMail").hide();
            $("#verMail2").hide();
            $("#verFono").hide();
            $("#verEdad").hide();
            $("#verRegionP").hide();
            $("#verComunaP").hide();
            $("#verHogarAyuda").hide();
            $("#verPersonas").hide(); // Ocultar número de personas
            $("#verDatosEncargado").hide(); // Ocultar datos del encargado
          

// Lógica para selección del tipo de colecta
$("#tipoColecta").change(function() {
    var tipoColecta = $(this).val();
    
    // Si la colecta es en calle o dentro de organización
    if (tipoColecta === "colecta_calle" || tipoColecta === "colecta_organizacion") {
        // Mostrar los campos de datos del encargado
        $("#verDatosEncargado").show();
        $("#verRutEncargado").show();
        $("#verEmailEncargado").show();
        $("#verConfirmEmailEncargado").show();
        $("#verFonoEncargado").show();
        $("#verRegionP").show();
        
        // Si la colecta es "colecta en calle", mostrar el campo "Número de personas"
        if (tipoColecta === "colecta_calle") {
            $("#verNumeroPersonas").show(); // Mostrar número de personas
        } else {
            // Si es "colecta dentro de organización", ocultar el campo "Número de personas"
            $("#verNumeroPersonas").hide(); // Ocultar número de personas
        }

    } else {
        // Ocultar todos los campos si no se selecciona una colecta válida
        $("#verRegionP").hide();
        $("#verComunaP").hide();
        $("#verHogarAyuda").hide();
        $("#verNumeroPersonas").hide();
        $("#verDatosEncargado").hide();
    }
});


        } else if (tipoVoluntario === "Individual") { // Si el tipo es Individual
            // Mostrar campos básicos para voluntariado individual
            $("#verTrabaja").show();

            // Lógica si trabaja en la fundación
            $("#trabajaFlr").change(function() {
                var trabajaFlr = $(this).val();
                if (trabajaFlr === "Si") {
                    // Si trabaja en la fundación, mostrar solo los campos relacionados (sin nombre, rut, etc.)
                    $("#verEmailCorporativo").show(); // Mostrar correo corporativo
                    $("#verAcompañado").show(); // Mostrar "¿Vas acompañado?"
                    $("#verBotonEnviar").show(); // Mostrar botón de enviar
                    $("#verRut").hide(); // Ocultar el campo de RUT
                    $("#verNombre").hide(); // Ocultar el campo de Nombre
                    $("#verTipoParticipacion").hide();
                    $("#verMail").hide(); // Ocultar el campo de Email
                    $("#verMail2").hide(); // Ocultar el campo de Confirmar Email
                    $("#verFono").hide(); // Ocultar el campo de Teléfono
                    $("#verEdad").hide(); // Ocultar el campo de Edad
                    $("#verRegionP").hide();
                    $("#verComunaP").hide();
                    $("#verHogarAyuda").hide();
                } else {
                    // Si no trabaja en la fundación, mostrar todos los campos necesarios
                    $("#verEmailCorporativo").hide(); // Ocultar correo corporativo
                    $("#verAcompañado").hide(); // Mostrar "¿Vas acompañado?"
                    $("#verRut").show(); // Mostrar el campo de RUT
                    $("#verNombre").show(); // Mostrar el campo de Nombre
                    $("#verTipoParticipacion").show();
                    $("#verMail").show(); // Mostrar el campo de Email
                    $("#verMail2").show(); // Mostrar el campo de Confirmar Email
                    $("#verFono").show(); // Mostrar el campo de Teléfono
                    $("#verEdad").show(); // Mostrar el campo de Edad
                    $("#verRegionP").show();
                    $("#verComunaP").show();
                    $("#verHogarAyuda").hide();
                    $("#verBotonEnviar").show(); // Mostrar botón de enviar
                }
            });

            // Si selecciona "Sí" en "¿Vas acompañado?", muestra el campo para ingresar el número de personas
            $("#acompañado").change(function() {
                if ($(this).val() === "Si") {
                    $("#verPersonas").show(); // Mostrar el campo de número de personas
                } else {
                    $("#verPersonas").hide(); // Ocultar el campo
                    $("#numPersonas").val(""); // Limpiar el campo de número de personas
                }
            });
        } else { // Si no se selecciona un tipo de voluntariado
            // Ocultar todos los campos
            $("#verTrabaja").hide();
            $("#verEmailCorporativo").hide();
            $("#verAcompañado").hide();
            $("#verPersonas").hide();
            $("#verRut").hide();
            $("#verNombre").hide();
            $("#verMail").hide();
            $("#verMail2").hide();
            $("#verFono").hide();
            $("#verEdad").hide();
            $("#verBotonEnviar").hide();
            $("#verOrganizacion").hide();
            $("#verNombreOrganizacion").hide();
            $("#verTipoColecta").hide();
            $("#verNumeroPersonas").hide();
            $("#verDatosEncargado").hide();
            $("#verRegionP").hide();
            $("#verComunaP").hide();
            $("#verHogarAyuda").hide();
        }
    });

    // Mostrar las comunas basadas en la región seleccionada
    $(document).ready(function() {

function resetCampos() {
    // Ocultar todos los campos posibles antes de mostrar los que correspondan
    $("#verTrabaja").hide();
    $("#verEmailCorporativo").hide();
    $("#verAcompañado").hide();
    $("#verPersonas").hide();
    $("#verRut").hide();
    $("#verNombre").hide();
    $("#verTipoParticipacion").hide();
    $("#verMail").hide();
    $("#verMail2").hide();
    $("#verFono").hide();
    $("#verEdad").hide();
    $("#verApoyoHogar").hide();
    $("#verRegionP").hide();
    $("#verComunaP").hide();
    $("#verHogarAyuda").hide();
    $("#verBotonEnviar").hide();
    $("#verOrganizacion").hide();
    $("#verNombreOrganizacion").hide();
    $("#verTipoParticipacionGrupal").hide();
    $("#verTipoColecta").hide();
    $("#verNumeroPersonas").hide();
    $("#verDatosEncargado").hide();
    $("#verRegionUbicacion").hide();
    $("#verComunaUbicacion").hide();
    $("#verParticipacion").hide();
    $("#verRegionApoyo").hide();
}

        // $("#regionColectaCalleP").change(function() {
        //     var regionSeleccionada = $(this).val();
        //     var opcionesComuna = "<option value=''>Seleccione Comuna</option>";

        //     if (regionSeleccionada && comunasP[regionSeleccionada]) {
        //         comunasP[regionSeleccionada].forEach(function(comunaP) {
        //             opcionesComuna += "<option value='" + comunaP + "'>" + comunaP + "</option>";
        //         });
        //         $("#verComunaP").show();
        //     } else {
        //         $("#verComunaP").hide();
        //     }

        //     $("#comunaP").html(opcionesComuna);
        //     $("#verHogarAyuda").hide();
        // });

        // $("#comunaP").change(function() {
        //     var comunaSeleccionada = $(this).val();
        //     var opcionesHogar = "<option value=''>Seleccione Hogar</option>";

        //     if (comunaSeleccionada && hogares[comunaSeleccionada]) {
        //         hogares[comunaSeleccionada].forEach(function(hogar) {
        //             opcionesHogar += "<option value='" + hogar + "'>" + hogar + "</option>";
        //         });
        //         $("#verHogarAyuda").show();
        //     } else {
        //         $("#verHogarAyuda").hide();
        //     }

        //     $("#hogarAyuda").html(opcionesHogar);
        // });
    });

    // Definir las comunas y los hogares disponibles
var comunasPorRegion = {
            "Región de Coquimbo": ["La Serena"],
            "Región de Valparaíso": ["Casablanca", "La Calera", "Nogales", "Puchuncaví", "Quillota"],
            "Región Metropolitana de Santiago": ["Buin","Independencia", "Isla de Maipo", "La Florida", "Lampa", "Melipilla", "Ñuñoa", "Recoleta", "San José de Maipo", "Santiago" ],
            "Región de O'Higgins": ["Chépica"],
            "Región del Maule": ["Curicó", "Linares", "Talca"],
            "Región del Bio Bio":["Arauco", "Talcahuano"],
            "Región de los Ríos": ["Valdivia"],
            "Región de los Lagos": ["Osorno"]
           
        };
        var hogaresPorComuna = {
            "La Serena":["H14 - La Visitación de María"],
            "Casablanca": ["H26 - María Inmaculada"],
            "La Calera":["H41 - Hogar Padre Sáez"],
            "Nogales":["H34 - Nuestra Señora del Carmen"],
            "Punchuncaví":["H37 - Nuestra Señora del Rosario"],
            "Quillota": ["H22 - San Alberto Hurtado"],
            "Independencia":[ "H1 - Nuestra Señora de la Merced", "H3 - Nuestra Señora de las Rosas", "H7 - Juan Pablo I", "H12 - Jesús Crucificado", "H28 - Nuestra Señora de Guadalupe", "Sin Preferencia" ],
            "Isla de Maipo":["H24 - Nuestra Señora de las Mercedes"],
            "La Florida": ["H20 - Cardenal José María Caro"],
            "Lampa":["H6 - María auxiliadora"],
            "Melipilla":["H23 - San José de Melipilla"],
            "Ñuñoa":["H5 - Nuestra Señora de la Paz"],
            "Recoleta":["H21 - Hogar San Carlos"],
            "San José de Maipo":["H40 - Santos Arcángeles Miguel, Gabriel y Rafael"],
            "Santiago":["H4 - Santísima Trinidad", "H9 - Hogar Santa Ana", "Sin Preferencia"],
            "Buin":["H8 - Santa Teresa de os Andes"],
            "Chépica": ["H18 - Sagrados Corazones de Jesús y María"],
            "Curicó": ["H27 - María Olga Tuñón de Barriga"],
            "Linares": [" H17 - Sagrado Corazón de Jesús"],
            "Talca" : ["H15 - Madre del Buen Consejo"],
            "Arauco":["H39 - San Juan Pablo II"],
            "Talcahuano":["H29 - Santa Teresa de Calcuta"], 
            "Valdivia":["H19 - Padre Pío"],
            "Osorno":["H2 - Santa María de Osorno"]

            // Añadir más comunas y sus respectivos hogares
        };
        
        
        // Función para llenar comunas en base a región seleccionada
// Llenar dinámicamente las regiones en los selectores de región Individual y Grupal
const regionSelectores = ["#regionIndividual", "#regionGrupal"];
regionSelectores.forEach(selector => {
    const select = $(selector);
    select.empty().append('<option value="">Seleccione una región</option>');
    Object.keys(comunasPorRegion).forEach(region => {
        select.append('<option value="' + region + '">' + region + '</option>');
    });
});

// Lógica para cargar comunas al seleccionar región (Individual o Grupal)
$("#regionIndividual, #regionGrupal").change(function () {
    const region = $(this).val();
    const comunas = comunasPorRegion[region] || [];
    const idComuna = $(this).attr("id") === "regionIndividual" ? "#comunaIndividual" : "#comunaGrupal";

    $(idComuna).empty().append('<option value="">Seleccione una comuna</option>');
    comunas.forEach(c => {
        $(idComuna).append('<option value="' + c + '">' + c + '</option>');
    });

    // Limpiar hogares cuando cambia la región
    const hogarId = idComuna === "#comunaIndividual" ? "#hogarIndividual" : "#hogarGrupal";
    $(hogarId).empty().append('<option value="">Seleccione un hogar</option>');
});

// Lógica para cargar hogares al seleccionar comuna
$("#comunaIndividual, #comunaGrupal").change(function () {
    const comuna = $(this).val();
    const hogares = hogaresPorComuna[comuna] || [];
    const hogarId = $(this).attr("id") === "comunaIndividual" ? "#hogarIndividual" : "#hogarGrupal";

    $(hogarId).empty().append('<option value="">Seleccione un hogar</option>');
    hogares.forEach(h => {
        $(hogarId).append('<option value="' + h + '">' + h + '</option>');
    });
});





// Mostrar campos de Región y Comuna para Externo o Grupal
$("#tipoVoluntario").change(function () {
    let tipo = $(this).val();
    if (tipo === "Individual" && $("#trabajaFlr").val() === "No") {
        $("#verRegionUbicacion").show();
        $("#verComunaUbicacion").show();
        $("#verParticipacion").show();
    } else if (tipo === "Grupal") {
        $("#verRegionUbicacion").show();
        $("#verComunaUbicacion").show();
        $("#verParticipacion").show();
    } else {
        $("#verRegionUbicacion").hide();
        $("#verComunaUbicacion").hide();
        $("#verParticipacion").hide();
    }
});

// // Cargar comunas según región
// const comunasP = {
//     "Región de Coquimbo": ["La Serena", "Coquimbo", "Ovalle"],
//     "Región de Valparaíso": ["Valparaíso", "Viña del Mar", "Quilpué"],
//     "Región Metropolitana": ["Santiago", "Puente Alto", "Maipú"],
//     "Región del Maule": ["Talca", "Curicó", "Linares"],
//     "Región del Biobío": ["Concepción", "Talcahuano", "Los Ángeles"],
//     "Región de Los Lagos y Región de Los Ríos": ["Puerto Montt", "Valdivia", "Osorno"]
// };

// $("#regionUbicacion").change(function () {
//     const region = $("#regionUbicacion option:selected").text();
//     const comunas = comunasPorRegion[region] || [];
//     const comunaSelect = $("#comunaUbicacion");
//     comunaSelect.empty();
//     comunaSelect.append('<option value="">Seleccione una comuna</option>');
//     comunas.forEach(c => {
//         comunaSelect.append('<option value="' + c + '">' + c + '</option>');
//     });
// });


// INDIVIDUAL
$("#tipoParticipacion").change(function () {
    const valor = $(this).val();

    if (valor === "Sí") {
        $("#verRegionIndividual").show();
        $("#verComunaIndividual").show();
        $("#verHogarIndividual").show();
    } else if (valor === "No") {
        $("#verRegionIndividual").show();
        $("#verComunaIndividual").show();
        $("#verHogarIndividual").hide();
    } else {
        $("#verRegionIndividual").hide();
        $("#verComunaIndividual").hide();
        $("#verHogarIndividual").hide();
    }
});

// GRUPAL
$("#tipoParticipacionGrupal").change(function () {
    const valor = $(this).val();

    if (valor === "Sí") {
        $("#verRegionGrupal").show();
        $("#verComunaGrupal").show();
        $("#verHogarGrupal").show();
    } else if (valor === "No") {
        $("#verRegionGrupal").show();
        $("#verComunaGrupal").show();
        $("#verHogarGrupal").hide();
    } else {
        $("#verRegionGrupal").hide();
        $("#verComunaGrupal").hide();
        $("#verHogarGrupal").hide();
    }
});



// Prevenir envío del formulario al presionar Enter en campos individuales
$(document).on("keydown", "form input", function(event) {
    return event.key !== "Enter";
});

// Lógica para tipoParticipacion (Individual)
$("#tipoParticipacion").change(function () {
    const valor = $(this).val();

    if (valor === "Sí") {
        $("#verRegionIndividual").show();
        $("#verComunaIndividual").show();
        $("#verHogarIndividual").show();
    } else if (valor === "No") {
        $("#verRegionIndividual").show();
        $("#verComunaIndividual").show();
        $("#verHogarIndividual").hide();
    } else {
        $("#verRegionIndividual").hide();
        $("#verComunaIndividual").hide();
        $("#verHogarIndividual").hide();
    }
});

// Lógica para tipoParticipacionGrupal (Grupal)
$("#tipoParticipacionGrupal").change(function () {
    const valor = $(this).val();

    if (valor === "Sí") {
        $("#verRegionGrupal").show();
        $("#verComunaGrupal").show();
        $("#verHogarGrupal").show();
    } else if (valor === "No") {
        $("#verRegionGrupal").show();
        $("#verComunaGrupal").show();
        $("#verHogarGrupal").hide();
    } else {
        $("#verRegionGrupal").hide();
        $("#verComunaGrupal").hide();
        $("#verHogarGrupal").hide();
    }
});

});