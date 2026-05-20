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

if ($agno <= 0 || $mes <= 0 || $mes > 12) {
    echo json_encode(array());
    exit;
}

$db = db_connect();

$sql = "
    SELECT 
        COUNT(id) AS donantes,
        DATE_FORMAT(fecha, '%d') AS dia,
        DATE_FORMAT(fecha, '%m') AS mes,
        (
            SELECT COUNT(id)
            FROM donaciones_online
            WHERE estado_pago_id = 1
              AND YEAR(fecha) = ?
              AND MONTH(fecha) = ?
        ) AS cantidad
    FROM donaciones_online
    WHERE estado_pago_id = 1
      AND YEAR(fecha) = ?
      AND MONTH(fecha) = ?
    GROUP BY DATE_FORMAT(fecha, '%d-%m-%Y')
";

$stmt = $db->prepare($sql);

$stmt->bind_param(
    'iiii',
    $agno,
    $mes,
    $agno,
    $mes
);

$stmt->execute();

$result = $stmt->get_result();

$data = array();

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();

echo json_encode($data);