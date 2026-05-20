<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../conexion/configuracion.php';

$sql = "SELECT sum(monto) monto, DATE_FORMAT((fecha ),'%H') dia, DATE_FORMAT((fecha ),'%d-%m-%Y') fecha,
		(SELECT sum(monto) aporte FROM donaciones where estado_id=1
		AND YEAR(fecha) = " . $_GET['agno'] . " AND
		MONTH(fecha)=" . $_GET['mes'] . " AND DAY(fecha )=" . $_GET['dia'] . ")total,
		(SELECT count(id) aporte FROM donaciones where estado_id=1
		AND YEAR(fecha) = " . $_GET['agno'] . " AND
		MONTH(fecha)=" . $_GET['mes'] . " AND DAY(fecha )=" . $_GET['dia'] . ")cantidad
		FROM donaciones where estado_id=1  AND YEAR(fecha) = " . $_GET['agno'] . " AND
		MONTH(fecha)=" . $_GET['mes'] . " AND DAY(fecha )=" . $_GET['dia'] . "
		GROUP by dia ORDER BY dia";

/*	SELECT sum(aporte_total) aporte, dia_creado,vendedor,medio_pago,estado_envio_id,(SELECT sum(aporte_total) FROM v_ordenes where estado_envio_id=2 AND mes_creado=".$_GET['mes'].") total
	FROM v_ordenes where estado_envio_id=2 AND mes_creado=".$_GET['mes']." GROUP by dia_creado";*/

$result = mysqli_query($db, $sql);
$data = array();

foreach ($result as $row) {
	$data[] = $row;
}

mysqli_close($db);
echo json_encode($data);
?>