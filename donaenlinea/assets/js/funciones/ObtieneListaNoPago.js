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

function ObtieneListaNoPago(){

$.ajax({
    url: "crud/traeListaNoPago.php",
    type: "GET",
    async:true,
    dataType: "json"
})
 .done(function( data, textStatus, jqXHR ) {

   var cabeza = '<table id="tabla_espera" class="table table-striped table-bordered" style="width:100%">'+
      '<thead>'+
       '<tr>'+
       '<th><font size="2"> Pedido </font></th>'+
       '<th><font size="2">Fecha</font></th>'+
       '<th><font size="2">Cliente</font></th>'+
       '<th><font size="2">Datos</font></th>'+
       '<th><font size="2">Detalle</font></th>'+
       '<th><font size="2">Estado</font></th>'+
       '<th><font size="2">Cambios</font></th>'+
       '<th><font size="2">Editar</font></th>'+
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
          '<td><b>Mail:</b> <a href="mailto: '+ data[x]['email']+'"><font size="2">'+ data[x]['email']+'</font></a><br><b>Fono:</b> '+ data[x]['fono']+'</td>'+
          '<td><font size="2"><b>Aporte:</b> $'+ formatNumber(data[x]['aporte'],'.',',')+'<br>'+
          '<font size="2"><b>Cantidad: </b>'+ data[x]['cant_coronas']+' </font><br><a href="detalle.php?'+id+'">Ver más </font><i class="fas fa-eye"></i></a></td>'+
          '<td><span class="'+ data[x]['class_estado']+'"><font size="2">'+ data[x]['estado']+'</font></span></td>'+
          '<td><font size="2">'+ data[x]['cambios']+'</font></td>'+
          '<td><a href="edit.php?'+id+'" title="Editar datos" class="btn btn-info"><i class="far fa-edit"></i></a></td>'+
          '</tr>'
  }
  var pie = '<thead>'+
   '<tr>'+
   '<th><font size="2">Pedido </font></th>'+
   '<th><font size="2">Fecha</font></th>'+
   '<th><font size="2">Cliente</font></th>'+
   '<th><font size="2">Datos</font></th>'+
   '<th><font size="2">Detalle</font></th>'+
   '<th><font size="2">Estado</font></th>'+
   '<th><font size="2">Cambios</font></th>'+
   '<th><font size="2">Editar</font></th>'+
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
