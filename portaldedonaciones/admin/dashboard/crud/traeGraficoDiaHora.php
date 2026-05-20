<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['userData']['cod_usuario'])) {
	http_response_code(401);
	echo json_encode(array());
	exit;
}

$agno = isset($_GET['agno']) ? (int) $_GET['agno'] : 0;
$mes = isset($_GET['mes']) ? (int) $_GET['mes'] : 0;
$dia = isset($_GET['dia']) ? (int) $_GET['dia'] : 0;

if ($agno <= 0 || $mes <= 0 || $mes > 12 || $dia <= 0 || $dia > 31) {
	echo json_encode(array());
	exit;
}

$db = db_connect();

$sql = "
    SELECT 
        monto,
        CONCAT(utm_source, ' ', DATE_FORMAT(fecha, '%H:%i')) AS dia,
        DATE_FORMAT(fecha, '%d-%m-%Y') AS fecha,
        (
            SELECT SUM(monto)
            FROM donaciones_online
            WHERE estado_pago_id = 1
              AND YEAR(fecha) = ?
              AND MONTH(fecha) = ?
              AND DAY(fecha) = ?
        ) AS total,
        (
            SELECT COUNT(id)
            FROM donaciones_online
            WHERE estado_pago_id = 1
              AND YEAR(fecha) = ?
              AND MONTH(fecha) = ?
              AND DAY(fecha) = ?
        ) AS cantidad
    FROM donaciones_online
    WHERE estado_pago_id = 1
      AND YEAR(fecha) = ?
      AND MONTH(fecha) = ?
      AND DAY(fecha) = ?
    ORDER BY DATE_FORMAT(fecha, '%H:%i')
";

$stmt = $db->prepare($sql);

$stmt->bind_param(
	'iiiiiiiii',
	$agno,
	$mes,
	$dia,
	$agno,
	$mes,
	$dia,
	$agno,
	$mes,
	$dia
);

$stmt->execute();

$result = $stmt->get_result();

$data = array();

while ($row = $result->fetch_assoc()) {
	$data[] = $row;
}

$stmt->close();

echo json_encode($data);