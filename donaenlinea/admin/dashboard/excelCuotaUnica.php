<?php

session_start();
date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['userData']['cod_usuario'])) {
    header('Location: ../login/logout.php');
    exit;
}

$cod_privilegio = isset($_SESSION['userData']['cod_privilegio'])
    ? (int) $_SESSION['userData']['cod_privilegio']
    : 0;

if ($cod_privilegio === 0) {
    header('Location: ../login/restriccion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Método no permitido');
}

$fecha_desde = isset($_POST['fecha_desde']) ? trim($_POST['fecha_desde']) : '';
$fecha_hasta = isset($_POST['fecha_hasta']) ? trim($_POST['fecha_hasta']) : '';

if ($fecha_desde === '' || $fecha_hasta === '') {
    exit('Fechas incompletas');
}

$fecha_hasta .= ' 23:59:59';

$db = db_connect();

$sql = "
    SELECT
        cu.nombre,
        cu.email,
        cu.rut,
        cu.monto,
        cu.fecha,
        cu.utm_source,
        cu.utm_medium,
        cu.utm_campaign
    FROM cuotas_unicas cu
    WHERE cu.fecha BETWEEN ? AND ?
    ORDER BY cu.fecha DESC
";

$stmt = $db->prepare($sql);

if (!$stmt) {
    error_log('Error preparando exportación cuotas únicas: ' . $db->error);
    exit('Error interno');
}

$stmt->bind_param('ss', $fecha_desde, $fecha_hasta);

if (!$stmt->execute()) {
    error_log('Error ejecutando exportación cuotas únicas: ' . $stmt->error);
    $stmt->close();
    exit('Error interno');
}

$resultado = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="listado_cuotas_unicas.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$fp = fopen('php://output', 'w');

fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

$delimiter = ';';

fputcsv(
    $fp,
    array(
        'Nombre',
        'Email',
        'Rut',
        'Aporte',
        'Fecha',
        'utm_source',
        'utm_medium',
        'utm_campaign'
    ),
    $delimiter
);

while ($row = $resultado->fetch_assoc()) {
    fputcsv(
        $fp,
        array(
            $row['nombre'],
            $row['email'],
            $row['rut'],
            $row['monto'],
            $row['fecha'],
            $row['utm_source'],
            $row['utm_medium'],
            $row['utm_campaign']
        ),
        $delimiter
    );
}

fclose($fp);

$stmt->close();
exit;