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



function ObtieneListaCompleta(opcion,dato){

var p = {};
if(opcion==1){
  p["fecha"] = dato;
}
if(opcion==2){
  p["pago_id"] = dato;
}
if(opcion==3){
  p["estado_id"] = dato;
}
if(opcion==4){
  p["cliente"] = dato;
}



$.ajax({
    url: "crud/traeListaCompleta.php",
    type: "GET",
    async:true,
    data:p,
    dataType: "json"
})
 .done(function( data, textStatus, jqXHR ) {

   var cabeza = '<table id="tabla_completa" class="table table-striped table-bordered" style="width:100%">'+
      '<thead>'+
       '<tr>'+
       '<th><font size="1">Pedido</font></th>'+
       '<th><font size="2">Fecha</font></th>'+
       '<th><font size="2">Cliente</font></th>'+
       '<th><font size="2">Datos</font></th>'+
       '<th><font size="2">Quién envía</font></th>'+
       '<th><font size="2">Detalle</font></th>'+
       '<th><font size="2">Estados</font></th>'+
       '<th><font size="2">Vendedor</font></th>'+
       '<th><font size="2">Editar</font></th>'+
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
          '<td><a href="detalle.php?'+id+'"><font size="2">'+ data[x]['id']+'</font></a></td>'+
          '<td><font size="2"><b>Creado: </b>'+ data[x]['creado'] +'<br>'+
          '<b>Despacho: </b>'+ data[x]['fecha_despacho_cl']+'</font>'+
          '</td>'+
          '<td><font size="2"><a href="lista_cliente?cliente='+data[x]['nombre']+'">'+ data[x]['nombre']+'</a></font></td>'+
          '<td><font size="2"><b>Mail:</b> <a href="mailto: '+ data[x]['email']+'">'+ data[x]['email']+'</font></a><br><b><font size="2">Fono:</b> '+ data[x]['fono']+'</font></td>'+
          '<td><font size="2">'+ data[x]['quienEnvia']+'</font></td>'+
          '<td><font size="2"><b>Pago:</b> '+ medio +'</b><br><b> Aporte:</b> $'+ formatNumber(data[x]['aporte_total'])+
          '<br><font size="2"><b>Cantidad: </b>'+ data[x]['cant_coronas']+' </font><br><a href="detalle.php?'+id+'">Ver más <i class="fas fa-eye"></i></a></font></td>'+
          '<td><font size="2"><b>Envio :</b> <span class="'+ data[x]['class_envio']+'"></span> '+ data[x]['estado_envio']+
          '<br><b>Tipo :</b> '+ data[x]['tipo']+
          '<br><span class="'+ data[x]['class']+'">'+ data[x]['estado']+'</span><br>'+
          '</td>'+
          '<td><font size="2">'+ data[x]['vendedor']+'</font></td>'+
          '<td><a href="edit.php?'+id+'" title="Editar datos" class="btn btn-info"><i class="far fa-edit"></i></a></td>'+
          '</tr>'
  }
  var pie = '<thead>'+
   '<tr>'+
   '<th><font size="1">Pedido</font></th>'+
   '<th><font size="2">Fecha</font></th>'+
   '<th><font size="2">Cliente</font></th>'+
   '<th><font size="2">Datos</font></th>'+
   '<th><font size="2">Quién envía</font></th>'+
   '<th><font size="2">Detalle</font></th>'+
   '<th><font size="2">Estados</font></th>'+
   '<th><font size="2">Vendedor</font></th>'+
   '<th><font size="2">Editar</font></th>'+
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
