<?php 
header("Content-type: application/excel");
header("Content-Disposition: attachment; filename=flr_donaciones.xls");
header("Pragma: no-cache");
header("Expires: 0");
?> 
<!DOCTYPE html>
<html>
<head>
	<title>Reporte de Reclamos</title>
</head>
<body>
<table>
	<thead>
		<tr>
			<th>Nombre</th>
			<th>Email</th>
			<th>Tel&eacute;fono</th>
			<th>Monto</th>
			<th>Producto</th>
			<th>Fecha de donaci&oacute;n</th>
			<th>utm_source</th>
			<th>utm_term</th>
			<th>utm_campaign</th>
			<th>utm_content</th>
		</tr>
	</thead>
	<tbody>
	<?php
	function acentos($val=''){
		$val=str_replace('á', '&aacute;', $val);
		$val=str_replace('é', '&eacute;', $val);
		$val=str_replace('í', '&iacute;', $val);
		$val=str_replace('ó', '&oacute;', $val);
		$val=str_replace('ú', '&uacute;', $val);
		$val=str_replace('Á', '&Aacute;', $val);
		$val=str_replace('É', '&Eacute;', $val);
		$val=str_replace('Í', '&Iacute;', $val);
		$val=str_replace('Ó', '&Oacute;', $val);
		$val=str_replace('Ú', '&Uacute;', $val);
		$val=str_replace('ñ', '&ntilde;', $val);
		$val=str_replace('Ñ', '&Ntilde;', $val);
		return $val;
	}
	$servidor="localhost"; 
	$user="fundacio_donar"; 
	$pass=")6;waR_C!LON";
	$db="fundacio_donacion";
	mysql_connect($servidor,$user,$pass) ; 
	mysql_select_db($db) ; 
	mysql_set_charset( 'utf8' );
	$qry=mysql_query("SELECT p.nombre,p.email,p.telefono,d.monto,p.producto, p.fecha_registro,p.utm_source,p.utm_term,p.utm_campaign,p.utm_content FROM donacion_persona p INNER JOIN donacion_donacion d ON p.id_persona = d.id_persona INNER JOIN donacion_webpay w ON d.id_donacion = w.tbk_orden_compra AND w.tbk_respuesta = 0 ORDER BY w.id_webpay ASC" ) ; 
	$campos = mysql_num_fields($qry) ;
	while($row=mysql_fetch_array($qry)){ 
	echo "<tr>"; 
	for($j=0; $j<$campos; $j++) { 
	echo "<td>".acentos($row[$j])."</td>"; 
	} 
	echo "</tr>"; 
	} 
	?>
	</tbody>
</table>
</body>
</html>