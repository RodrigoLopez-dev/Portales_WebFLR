<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/database.php';

captador_require_admin();

$database = new Database();

$result = $database->read();

if (!$result) {
    exit('Error al generar exportación');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="captadores_libres.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

$delimiter = ';';

fputcsv(
    $output,
    array(
        'Codigo',
        'Cod POS',
        'Rut',
        'Nombre',
        'Oficina',
        'Email',
        'Mes Ingreso',
        'Proyecto',
        'Estado',
        'Fecha Actualizacion'
    ),
    $delimiter
);

while ($row = $result->fetch_assoc()) {
    fputcsv(
        $output,
        array(
            isset($row['codigo']) ? $row['codigo'] : '',
            isset($row['cod_POS']) ? $row['cod_POS'] : '',
            isset($row['rut']) ? $row['rut'] : '',
            isset($row['nombre']) ? $row['nombre'] : '',
            isset($row['oficina']) ? $row['oficina'] : '',
            isset($row['email']) ? $row['email'] : '',
            isset($row['mes_ingreso']) ? $row['mes_ingreso'] : '',
            isset($row['proyecto']) ? $row['proyecto'] : '',
            isset($row['estado']) ? $row['estado'] : '',
            isset($row['fecha_actualizacion']) ? $row['fecha_actualizacion'] : ''
        ),
        $delimiter
    );
}

fclose($output);
exit;