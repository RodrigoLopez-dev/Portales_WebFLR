<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../conexion/configuracion.php';

$sql = "SELECT sum(monto) totales, DATE_FORMAT((fecha ),'%m') mes,DATE_FORMAT((fecha ),'%d') dia, CONCAT('(',utm_campaign,') ',utm_source) fuente
		FROM donaciones where estado_id=1 AND DAY((fecha ))=" . $_GET['dia'] . " AND YEAR(fecha) = " . $_GET['agno'] . " AND MONTH((fecha ))=" . $_GET['mes'] . "
		GROUP BY utm_campaign,utm_source";

$result = mysqli_query($db, $sql);
$data = array();

foreach ($result as $row) {
	$data[] = $row;
}

mysqli_close($db);
echo json_encode($data);
?>