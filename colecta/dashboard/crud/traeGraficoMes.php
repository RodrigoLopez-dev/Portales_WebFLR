<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../conexion/configuracion.php';

$sql = "SELECT sum(monto) aporte, DATE_FORMAT((fecha ),'%d') dia,DATE_FORMAT((fecha ),'%m') mes,(SELECT sum(monto) aporte FROM
		donaciones where estado_id=1 AND YEAR(fecha) = " . $_GET['agno'] . " AND MONTH(fecha )=" . $_GET['mes'] . ")total_mes,
		(SELECT count(id) aporte FROM
		donaciones where estado_id=1  AND YEAR(fecha) = " . $_GET['agno'] . " AND MONTH(fecha )=" . $_GET['mes'] . ")cantidad,
		(SELECT sum(monto) aporte FROM donaciones where estado_id=1)total_general,
		(SELECT sum(monto) aporte FROM donaciones where estado_id=1 and medio_pago_id = 1)total_general_webpay,
		(SELECT sum(monto) aporte FROM donaciones where estado_id=1 and medio_pago_id = 2)total_general_mach,
		(SELECT sum(monto) aporte FROM donaciones where estado_id=1 and medio_pago_id = 5)total_general_fintoc,
		(SELECT count(id) aporte FROM donaciones where estado_id=1)cantidad_general
		FROM donaciones where estado_id=1 AND YEAR(fecha) = " . $_GET['agno'] . " AND MONTH(fecha )=" . $_GET['mes'] . "
		GROUP BY DATE_FORMAT((fecha ),'%d-%m-%Y')";

$result = mysqli_query($db, $sql);
$data = array();

foreach ($result as $row) {
	$data[] = $row;
}

mysqli_close($db);
echo json_encode($data);
?>