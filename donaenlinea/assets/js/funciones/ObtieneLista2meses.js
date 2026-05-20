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

function ObtieneLista2meses(pago_id){
var p = {};
p["pago_id"] = pago_id;

$.ajax({
    url: "crud/traeLista2meses.php",
    type: "GET",
    async:true,
    data:p,
    dataType: "json"
})
 .done(function( data, textStatus, jqXHR ) {

   var cabeza = '<table id="tabla_completa" class="table table-striped table-bordered" style="width:100%">'+
      '<thead>'+
       '<tr>'+
       '<th>Pedido</th>'+
       '<th>Fecha</th>'+
       '<th>Cliente</th>'+
       '<th>Teléfono</th>'+
       '<th>Quién envía</th>'+
       '<th>/Difunto<br>/Novia<br>/Padres</th>'+
       '<th>/Deudo<br>/Novio<br>/Recién nacido</th>'+
       '<th>Detalle</th>'+
       '<th>Estados</th>'+
       '<th>Vendedor</th>'+
       '<th>Acciones</th>'+
       '</tr>'+
     '</thead>'+
    '<tbody>'

 var tabla =  '<tr>';
   for (x=0;x<data.length;x++){

 var id = encode_this('orden='+data[x]['id']);

 if(data[x]['medio_pago']=='Recaudador'){
 var medio = '<a href="recaudacion.php?'+id+'">'+data[x]['medio_pago']+'</a>';
 }else{
 var medio = data[x]['medio_pago'];
 }
       tabla = tabla +
          '<td>'+ data[x]['id']+'</td>'+
          '<td>'+ data[x]['creado'] +'</td>'+
          '<td>'+ data[x]['nombre']+'</td>'+
          '<td>'+ data[x]['fono']+'</td>'+
          '<td>'+ data[x]['quienEnvia']+'</td>'+
          '<td>'+ data[x]['dato1']+'</td>'+
          '<td>'+ data[x]['dato2']+'</td>'+
          '<td><b>Pago:</b> '+ medio +'</b><br><b> Aporte:</b> $'+ formatNumber(data[x]['aporte_total'])+'<br><a href="detalle.php?'+id+'">Ver más</a></td>'+
          '<td><b>Envio :</b> <span class="'+ data[x]['class_envio']+'"></span> '+ data[x]['estado_envio']+
          '<br><b>Tipo :</b> '+ data[x]['tipo']+
          '<br><span class="'+ data[x]['class']+'">'+ data[x]['estado']+'</span>'+
          '<br><b>Despacho: </b>''+ data[x]['fecha_despacho_cl']+'<br>'+
          '</td>'+
          '<td>'+ data[x]['vendedor']+'</td>'+
          '<td><a href="edit.php?'+id+'" title="Editar datos" class="btn btn-sm btn-info"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a></td>'+
          '</tr>'
  }
  var pie = '<thead>'+
   '<tr>'+
   '<th>Pedido</th>'+
   '<th>Fecha</th>'+
   '<th>Cliente</th>'+
   '<th>Teléfono</th>'+
   '<th>Quién envía</th>'+
   '<th>/Difunto<br>/Novia<br>/Padres</th>'+
   '<th>/Deudo<br>/Novio<br>/Recién nacido</th>'+
   '<th>Detalle</th>'+
   '<th>Estados</th>'+
   '<th>Vendedor</th>'+
   '<th>Acciones</th>'+
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
