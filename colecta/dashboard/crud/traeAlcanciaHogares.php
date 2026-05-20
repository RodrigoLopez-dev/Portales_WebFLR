<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../conexion/configuracion.php';
$sql = "SELECT al.nombre Hogar,
		dn.utm_source Codigo,
		sum(dn.monto) Monto,
		CONCAT(al.nombre,': <b>$',FORMAT(sum(dn.monto),0, 'de_DE'),'</b><br>') Lista,
		al.orden orden
		FROM donaciones dn
		LEFT JOIN medios_pago mp on dn.medio_pago_id = mp.id
		LEFT JOIN alcancias al on dn.utm_source = al.codigo
		WHERE dn.utm_medium='Hogares' and dn.estado_id = 1
		group by dn.utm_source
		ORDER BY al.orden ASC";

$result = mysqli_query($db, $sql);
$data = array();
foreach ($result as $row) {
	$data[] = $row;
}
mysqli_close($db);
echo json_encode($data);
/*	foreach ($data as $value) {
   $cadena =  $value['Hogar'] ." : $<b>". number_format($value['Monto'], 0, ',', '.')."</b>";
   echo $cadena." <br>";
}*/
?>