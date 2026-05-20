<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../conexion/configuracion.php';
$sql = "SELECT sum(do.monto) totales,do.utm_campaign, DATE_FORMAT((do.fecha ),'%m') mes
		FROM donaciones do where do.estado_id=1 AND YEAR(do.fecha) = " . $_GET['agno'] . "
		AND MONTH((do.fecha ))=" . $_GET['mes'] . "
		GROUP BY do.utm_campaign";

$result = mysqli_query($db, $sql);
$data = array();

foreach ($result as $row) {
	$data[] = $row;
}

mysqli_close($db);
echo json_encode($data);
?>