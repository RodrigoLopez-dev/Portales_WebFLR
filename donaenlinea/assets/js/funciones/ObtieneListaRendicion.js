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

function ObtieneListaRecaudacion(sector_id){
var p = {};
p["sector_id"] = sector_id;

$.ajax({
    url: "crud/traeListaRecaudacion.php",
    type: "GET",
    async:true,
    data:p,
    dataType: "json"
})
 .done(function( data, textStatus, jqXHR ) {

   var cabeza = '<table id="tabla_rendicion" class="table table-striped table-bordered" style="width:100%">'+
      '<thead>'+
       '<tr>'+
       '<th>Pedido</th>'+
       '<th>Cliente</th>'+
       '<th style="width:180px">Quien envía</th>'+
       '<th>Aporte</th>'+
       '<th>Valor a rendir</th>'+
       '<th>N° comprobante</th>'+
       '</tr>'+
     '</thead>'+
    '<tbody>'

var tabla =  '<tr>';
   for (x=0;x<data.length;x++){

var id = encode_this('id='+data[x]['orden']);

       tabla = tabla +
          '<td>'+ data[x]['orden']+'</td>'+
          '<td><a href="lista_cliente?cliente='+data[x]['cliente']+'">'+ data[x]['cliente'] +'</a></td>'+
          '<td>'+ data[x]['quienEnvia'] +'</td>'+
          '<td>$'+ data[x]['aporte']+'</td>'+
          '<td><input class="form-control" type="text" id="rendicion" value="'+data[x]['aporte']+'"></td>'+
          '<td><input class="form-control" type="text" id="comprobante" value=""></td>'+
          '</tr>'
  }
  var pie = '<thead>'+
   '<tr>'+
   '<th>Pedido</th>'+
   '<th>Cliente</th>'+
   '<th style="width:180px">Quien envía</th>'+
   '<th>Aporte</th>'+
   '<th>Valor a rendir</th>'+
   '<th>N° comprobante</th>'+
   '</tr>'+
 '</thead></tbody></table>'
  $('#tabla').html(cabeza+tabla+pie);

  $(document).ready(function() {
  $('#tabla_rendicion').DataTable( {
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
