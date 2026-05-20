<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../conexion/configuracion.php';

/*$sql= "SELECT sum(do.monto) totales,do.ip_region region, DATE_FORMAT((do.fecha ),'%m') mes,DATE_FORMAT((do.fecha ),'%d') dia
FROM donaciones as do
where do.estado_id=1 AND DAY((do.fecha ))=".$_GET['dia']." AND YEAR((do.fecha)) = ".$_GET['agno']." AND MONTH((do.fecha ))=".$_GET['mes']." AND do.ip_pais ='Chile'
GROUP BY do.ip_region";*/

$sql = "SELECT sum(do.monto) totales,do.ip_region region, DATE_FORMAT((do.fecha ),'%m') mes,DATE_FORMAT((do.fecha ),'%d') dia
		FROM donaciones as do
		where do.estado_id=1  AND MONTH((do.fecha ))=" . $_GET['mes'] . " GROUP BY do.ip_region";

$result = mysqli_query($db, $sql);
$data = array();

foreach ($result as $row) {
	$data[] = $row;
}

mysqli_close($db);
echo json_encode($data);
?>