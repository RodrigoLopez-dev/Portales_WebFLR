<?php

date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Método no permitido');
}

$fecha_desde = isset($_POST['fecha_desde'])
    ? trim($_POST['fecha_desde'])
    : '';

$fecha_hasta = isset($_POST['fecha_hasta'])
    ? trim($_POST['fecha_hasta'])
    : '';

if ($fecha_desde === '' || $fecha_hasta === '') {
    exit('Fechas incompletas');
}

$fecha_hasta .= ' 23:59:59';

$db = db_connect();

$sql = "
SELECT
    don.id,
    don.rut,
    don.nombre,
    don.apellidos,
    don.email,
    don.telefono,
    don.monto,
    don.region,
    don.ciudad,
    don.pais,
    don.fecha,
    don.utm_source,
    don.utm_medium,
    don.utm_campaign,
    ep.estado,
    mp.nombre AS medio_pago
FROM donaciones_online don
LEFT JOIN estados_pago ep
    ON don.estado_pago_id = ep.id
LEFT JOIN medios_pago mp
    ON don.medio_pago_id = mp.id
WHERE don.fecha BETWEEN ? AND ?
ORDER BY don.fecha DESC
";

$stmt = $db->prepare($sql);

if (!$stmt) {
    error_log('Error preparando exportación CSV: ' . $db->error);
    exit('Error interno');
}

$stmt->bind_param(
    'ss',
    $fecha_desde,
    $fecha_hasta
);

if (!$stmt->execute()) {
    error_log('Error ejecutando exportación CSV: ' . $stmt->error);
    $stmt->close();
    exit('Error interno');
}

$result = $stmt->get_result();

$filename = 'descarga-portaldeconaciones.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv(
    $output,
    array(
        'Orden',
        'RUT',
        'Nombre',
        'Apellidos',
        'Email',
        'Telefono',
        'Monto',
        'Region',
        'Comuna',
        'Pais',
        'Fecha',
        'UTM Source',
        'UTM Medium',
        'UTM Campaign',
        'Estado Pago',
        'Medio de Pago'
    ),
    ';'
);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row, ';');
}

fclose($output);

$stmt->close();