<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../conexion/configuracion.php';
$sql = "SELECT * FROM v_detalle_donaciones ORDER BY id DESC";

$result = mysqli_query($db, $sql);
$data = array();

foreach ($result as $row) {
	$data[] = $row;
}

mysqli_close($db);
echo json_encode($data);
?>