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

function ObtieneListaEsperaPago(){

$.ajax({
    url: "crud/traeListaEsperaPago.php",
    type: "GET",
    async:true,
    dataType: "json"
})
 .done(function( data, textStatus, jqXHR ) {

   var cabeza = '<table id="tabla_espera" class="table table-striped table-bordered" style="width:100%">'+
      '<thead>'+
       '<tr>'+
       '<th>Pedido</th>'+
       '<th>Fecha</th>'+
       '<th>Cliente</th>'+
       '<th>Email</th>'+
       '<th>Fono</th>'+
       '<th>Detalle</th>'+
       '<th>Estado</th>'+
       '<th>Cambios</th>'+
       '<th>Acciones</th>'+
       '</tr>'+
     '</thead>'+
    '<tbody>'

var tabla =  '<tr>';
   for (x=0;x<data.length;x++){

var id = encode_this('orden='+data[x]['orden_id']);


       tabla = tabla +
          '<td>'+ data[x]['orden_id']+'</td>'+
          '<td>'+ data[x]['fecha'] +'</td>'+
          '<td><a href="lista_cliente?cliente='+data[x]['nombre']+'">'+ data[x]['nombre']+'</a></td>'+
          '<td>'+ data[x]['email']+'</td>'+
          '<td>'+ data[x]['fono']+'</td>'+
          '<td><a href="detalle.php?'+id+'">Ver</a></td>'+
          '<td><span class="label label-warning">'+ data[x]['estado']+'</span></td>'+
          '<td>'+ data[x]['cambios']+'</td>'+
          '<td><a href="edit.php?'+id+'" title="Editar datos" class="btn btn-sm btn-info"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a></td>'+
          '</tr>'
  }
  var pie = '<thead>'+
   '<tr>'+
   '<th>Pedido</th>'+
   '<th>Fecha</th>'+
   '<th>Cliente</th>'+
   '<th>Email</th>'+
   '<th>Fono</th>'+
   '<th>Detalle</th>'+
   '<th>Estado</th>'+
   '<th>Cambios</th>'+
   '<th>Acciones</th>'+
   '</tr>'+
 '</thead></tbody></table>'
  $('#tabla').html(cabeza+tabla+pie);


  $(document).ready(function() {
  $('#tabla_espera').DataTable( {
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
