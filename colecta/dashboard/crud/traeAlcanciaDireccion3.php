<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../conexion/configuracion.php';
$sql = "SELECT sum(do.monto) Total,
		co.codigo,
		co.gerencia1,
		co.gerencia2,
		co.gerencia3,
		co.nombre,
		co.apellido
		FROM colaboradores co
		LEFT JOIN donaciones do on co.codigo = do.utm_source
		WHERE estado_id = 1
		GROUP by co.nombre,
		co.apellido,
		co.gerencia1,
		co.gerencia2,
		co.gerencia3
		ORDER by co.gerencia1";
		
$result = mysqli_query($db, $sql);
$data = array();
foreach ($result as $row) {
	$data[] = $row;
}
mysqli_close($db);
echo json_encode($data);
?>