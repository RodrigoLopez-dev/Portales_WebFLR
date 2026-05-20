<?php

session_start();
date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/database.php';

if (!isset($_SESSION['userData']['cod_usuario'])) {
    header('Location: ../login/logout.php');
    exit;
}

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
        'Estado'
    ),
    $delimiter
);

while ($row = $result->fetch_assoc()) {

    fputcsv(
        $output,
        array(
            $row['codigo'],
            $row['cod_POS'],
            $row['rut'],
            $row['nombre'],
            $row['oficina'],
            $row['email'],
            $row['mes_ingreso'],
            $row['proyecto'],
            $row['estado']
        ),
        $delimiter
    );
}

fclose($output);
exit;