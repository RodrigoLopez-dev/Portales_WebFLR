<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../conexion/configuracion.php';

$sql = "SELECT count(id) donantes, DATE_FORMAT((fecha ),'%d') dia,DATE_FORMAT((fecha ),'%m') mes,
		(SELECT count(id) aporte FROM donaciones where estado_id=1
		AND YEAR(fecha) = " . $_GET['agno'] . " AND
		MONTH((fecha ))=" . $_GET['mes'] . ")cantidad
		FROM donaciones where estado_id=1 AND YEAR(fecha) = " . $_GET['agno'] . " AND MONTH((fecha ))=" . $_GET['mes'] . "
		GROUP BY DATE_FORMAT((fecha ),'%d-%m-%Y')";

$result = mysqli_query($db, $sql);
$data = array();

foreach ($result as $row) {
	$data[] = $row;
}

mysqli_close($db);
echo json_encode($data);
?>