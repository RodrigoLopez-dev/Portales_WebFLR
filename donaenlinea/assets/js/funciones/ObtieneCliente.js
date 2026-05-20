function ObtieneClienteMail(){
var p = {};
p["email"] = $('#email1').val();

$.ajax({
    url: "crud/traeClienteMail.php",
    type: "GET",
    async:true,
    data:p,
    dataType: "json"
})
 .done(function( data, textStatus, jqXHR ) {

   for (x=0;x<data.length;x++)
   {
      var nombre = data[x]['nombre'];
      var fono = data[x]['fono'];
      var email = data[x]['email'];
  }


 $('#nombre').val(nombre);
 $('#fono').val(fono);
 $('#email').val($('#email1').val());
 //$('#nombre').prop('readonly', true);
 //$('#fono').prop('readonly', true);
 $('#email').prop('readonly', true);

 })
 .fail(function( jqXHR, textStatus, errorThrown ) {
     if ( console && console.log ) {
         console.log( "Nota: " +' / '+JSON.stringify(jqXHR) );
     }
     alert('E-mail no registrado');
     $('#nombre').val('');
     $('#nombre').focus();
     $('#fono').val('');
     $('#email').val($('#email1').val());
     $('#nombre').prop('readonly', false);
     $('#fono').prop('readonly', false);
     $('#email').prop('readonly', false);
});
}

function ObtieneClienteFono(){
var p = {};
p["fono"] = $('#fono1').val();

$.ajax({
    url: "crud/traeClienteFono.php",
    type: "GET",
    async:true,
    data:p,
    dataType: "json"
})
 .done(function( data, textStatus, jqXHR ) {


      for (x=0;x<data.length;x++)
      {
         var nombre = data[x]['nombre'];
         var fono = data[x]['fono'];
         var email = data[x]['email'];
     }

    $('#nombre').val(nombre);
    $('#fono').val($('#fono1').val());
    $('#email').val(email);

 //$('#nombre').prop('readonly', true);
 //$('#fono').prop('readonly', true);
 $('#email').prop('readonly', true);

 })
 .fail(function( jqXHR, textStatus, errorThrown ) {
     if ( console && console.log ) {
         console.log( "Nota: " +' / '+JSON.stringify(jqXHR) );
     }
     alert('Teléfono no registrado')
     $('#nombre').val('');
     $('#nombre').focus();
     $('#fono').val($('#fono1').val());
     $('#email').val('');
     $('#nombre').prop('readonly', false);
     $('#fono').prop('readonly', false);
     $('#email').prop('readonly', false);
});
}
