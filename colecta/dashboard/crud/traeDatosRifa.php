<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../conexion/configuracion.php';

$sql = "SELECT COUNT(`tbk_orden_compra`) personas, SUM(`tbk_monto`) total,(SUM(`tbk_monto`)/2000) numeros  FROM `compras_webpay` ";
$result = mysqli_query($db, $sql);
$data = array();
foreach ($result as $row) {
	$data[] = $row;
}
mysqli_close($db);
echo json_encode($data);
?>