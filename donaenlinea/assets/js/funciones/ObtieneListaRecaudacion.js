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

   var cabeza = '<table id="tabla_recaudacion" class="table table-striped table-bordered" style="width:100%">'+
      '<thead>'+
       '<tr>'+
       '<th><font size="2">Pedido</font></th>'+
       '<th><font size="2">Fecha</font></th>'+
       '<th><font size="2">Cliente</font></th>'+
       '<th style="width:180px"><font size="2">Quien envía</font></th>'+
       '<th><font size="2">Montos</font></th>'+
       '<th><font size="2">Dirección de cobranza</font></th>'+
       '<th><font size="2">Sector</font></th>'+
       '<th style="width:100px"><font size="2">Detalle</font></th>'+
       '<th><font size="2">Estado</font></th>'+
       '<th ><font size="2">Editar</font></th>'+
       '</tr>'+
     '</thead>'+
    '<tbody>'

var tabla =  '<tr>';
   for (x=0;x<data.length;x++){

var id = encode_this('id='+data[x]['orden']);
var orden = encode_this('orden='+data[x]['orden']);

       tabla = tabla +
          '<td><font size="2"><a href="detalle.php?'+orden+'">'+ data[x]['orden']+'</a></font></td>'+
          '<td><font size="2">' + data[x]['creado']+'</font></td>'+
          '<td><font size="2"><a href="lista_cliente?cliente='+data[x]['cliente']+'">'+ data[x]['cliente'] +'</a></font></td>'+
          '<td><font size="2">'+ data[x]['quienEnvia'] +'</font></td>'+
          '<td><font size="2"><b>Aporte: </b>$'+ formatNumber(data[x]['aporte'])+'</font>'+
          '<br><font size="2"><b>Rendido: </b>$'+ formatNumber(data[x]['monto_rendido'])+'</font></td>'+
          '<td><font size="2">'+data[x]['direccion']+', '+data[x]['comuna']+', '+data[x]['ciudad']+'</font></td>'+
          '<td><font size="2">'+ data[x]['sector']+'</font></td>'+
          '<td><font size="2"><b>A quién cobrar: </b>'+data[x]['aquien']+'</font><br>'+
          '<font size="2"><b>Comentario: </b>'+data[x]['comentario']+'<br><a href="detalle.php?'+orden+'">Ver más <i class="fas fa-eye"></i></a></font></td>'+
         '<td><span class="'+ data[x]['class']+'"><font size="2">'+ data[x]['estado']+'</font></span></td>'+
          '<td><a href="edita_recaudacion.php?'+id+'" title="Editar datos" class="btn btn-sm btn-info"><span class="fas fa-edit" aria-hidden="true"></span></a>'+
          '</td>'+
          '</tr>'
  }
  var pie = '<thead>'+
  '<tr>'+
  '<th><font size="2">Pedido</font></th>'+
  '<th><font size="2">Fecha</font></th>'+
  '<th><font size="2">Cliente</font></th>'+
  '<th style="width:180px"><font size="2">Quien envía</font></th>'+
  '<th><font size="2">Montos</font></th>'+
  '<th><font size="2">Dirección de cobranza</font></th>'+
  '<th><font size="2">Sector</font></th>'+
  '<th style="width:100px"><font size="2">Detalle</font></th>'+
  '<th><font size="2">Estado</font></th>'+
  '<th ><font size="2">Editar</font></th>'+
  '</tr>'+
 '</thead></tbody></table>'
  $('#tabla').html(cabeza+tabla+pie);

  $(document).ready(function() {
  $('#tabla_recaudacion').DataTable( {
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


function ObtieneListaRecaudacionImprime(sector_id){
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

   var cabeza = '<table id="tabla_recaudacion" class="table table-striped table-bordered" style="width:100%">'+
      '<thead>'+
       '<tr>'+
       '<th>Pedido</th>'+
       '<th>Cliente</th>'+
       '<th>Aporte</th>'+
       '<th>Medio de pago</th>'+
       '<th>Dirección de cobranza</th>'+
       '<th>Sector</th>'+
       '<th>Estado del pago</th>'+
       '<th>Creado</th>'+
       '<th>Quines envían</th>'+
       '<th>Editar</th>'+
       '</tr>'+
     '</thead>'+
    '<tbody>'

var tabla =  '<tr>';
   for (x=0;x<data.length;x++){

var id = encode_this('orden='+data[x]['orden']);

       tabla = tabla +
          '<td>'+ data[x]['orden']+'</td>'+
          '<td>'+ data[x]['cliente'] +'</td>'+
          '<td>$'+ formatNumber(data[x]['aporte_total'])+'</td>'+
          '<td>'+ data[x]['medio_pago'] +'</td>'+
          '<td>'+data[x]['direccion']+', '+data[x]['comuna']+', '+data[x]['ciudad']+'</td>'+
          '<td>'+ data[x]['sector']+'</td>'+
          '<td><span class="'+ data[x]['class']+'">'+ data[x]['estado']+'</span></td>'+
          '<td>'+ data[x]['creado']+'</td>'+
          '<td>'+ data[x]['quienEnvia']+'</td>'+
          '<td><a href="edit.php?'+id+'" title="Editar datos" class="btn btn-sm btn-info"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a></td>'+
          '</tr>'
  }
  var pie = '<thead>'+
             '<tr>'+
             '<th>Orden</th>'+
             '<th>Fecha de pedido</th>'+
             '<th>Cliente</th>'+
             '<th>Aporte</th>'+
             '<th>Medio de pago</th>'+
             '<th>Detalle</th>'+
             '<th>Estado pago</th>'+
             '<th>Vendedor</th>'+
             '<th>Editar</th>'+
             '</tr>'+
             '</thead></tbody></table>'
  $('#tabla').html(cabeza+tabla+pie);

 })
 .fail(function( jqXHR, textStatus, errorThrown ) {
     if ( console && console.log ) {
         console.log( "Nota: " +' / '+JSON.stringify(jqXHR) );
     }
     alert('No hay datos')
});
}
