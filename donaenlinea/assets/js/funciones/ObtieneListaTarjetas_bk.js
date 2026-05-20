function formatNumber(num) {
    if (!num || num == 'NaN') return '-';
    if (num == 'Infinity') return '&#x221e;';
    num = num.toString().replace(/\$|\,/g, '');
    if (isNaN(num))
        num = "0";
    sign = (num == (num = Math.abs(num)));
    num = Math.floor(num * 100 + 0.50000000001);
    cents = num % 100;
    num = Math.floor(num / 100).toString();
    if (cents < 10)
        cents = "0" + cents;
    for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3) ; i++)
        num = num.substring(0, num.length - (4 * i + 3)) + '.' + num.substring(num.length - (4 * i + 3));
    return (((sign) ? '' : '-') + num );
}



function ObtieneListaTarjetas(){


$.ajax({
    url: "crud/traeListaTarjeta.php",
    type: "GET",
    async:true,
    dataType: "json"
})
 .done(function( data, textStatus, jqXHR ) {

   var cabeza = '<table id="tabla_completa" class="table table-striped table-bordered" style="width:100%">'+
      '<thead>'+
       '<tr>'+
       '<th>Orden</th>'+
       '<th>Aporte</th>'+
       '<th>Donante</th>'+
       '<th>Quien envia</th>'+
       '<th>Tarjeta</th>'+
       '<th>Mensaje</th>'+
       '<th>Estado de pago</th>'+
       '</tr>'+
     '</thead>'+
    '<tbody>'

var tabla =  '<tr>';
   for (x=0;x<data.length;x++){

var id = encode_this('orden='+data[x]['orden_id']);

       tabla = tabla +
          '<td> '+data[x]['orden_id']+'</td>'+
          '<td> $'+formatNumber(data[x]['aporte'],0,'','.')+'</td>'+
          '<td> '+data[x]['donante']+'</td>'+
          '<td> '+data[x]['quienEnvia']+'<br>'+data[x]['email']+'</td>'+
          '<td> '+data[x]['tarjeta']+'</td>'+
          '<td><textarea name="message" rows="3" cols="15"> '+data[x]['mensaje']+'</textarea></td>'+
          '<td> '+data[x]['estado_pago']+'<br>'+data[x]['fecha']+'</td>'+
          '</tr>'
  }
  var pie = '<thead>'+
  '<tr>'+
  '<th>Orden</th>'+
  '<th>Aporte</th>'+
  '<th>Donante</th>'+
  '<th>Quien envia</th>'+
  '<th>Tarjeta</th>'+
  '<th>Mensaje</th>'+
  '<th>Estado de pago</th>'+
  '</tr>'+
 '</thead></tbody></table>'
  $('#tabla').html(cabeza+tabla+pie);

  $(document).ready(function() {
  $('#tabla_completa').DataTable( {
      "order": [[ 0, "desc" ]]
  	} );
  } )

 })
 .fail(function( jqXHR, textStatus, errorThrown ) {
     if ( console && console.log ) {
         console.log( "Nota: " +' / '+JSON.stringify(jqXHR) );
     }
     alert('No hay datos')
});
}
