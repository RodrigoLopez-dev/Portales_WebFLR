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

function ObtieneListaDato1(dato1){
var p = {};
p["dato1"] = dato1;

$.ajax({
    url: "crud/traeListaDato1.php",
    type: "GET",
    async:true,
    data:p,
    dataType: "json"
})
 .done(function( data, textStatus, jqXHR ) {

var tarjeta ='';
for (x=0;x<data.length;x++){

var id = encode_this('orden='+data[x]['orden_id']);

tarjeta = tarjeta +'<div class="card">'+
    '<div class="card-header"><b><a href="busca_pedido.php?'+id+'">'+'Orden : '+ data[x]['orden_id']+' </b></a></div>'+
    '<div class="card-body">'+
    '  <h5 class="card-title"><b>'+ data[x]['descripcion']+'</b></h5>'+
    '  <p class="card-text"><b>'+ data[x]['tipo1']+': </b>'+ data[x]['dato1']+'<br><b>'+
     data[x]['tipo2']+': </b>'+ data[x]['dato2']+'<br><b> Direccion: </b>'
      +data[x]['direccion']+' , '+data[x]['comuna']+' , '+data[x]['ciudad']+'</p>'+
    '</div>'+
  '</div><br>';


}

$('#tabla').html(tarjeta);

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
