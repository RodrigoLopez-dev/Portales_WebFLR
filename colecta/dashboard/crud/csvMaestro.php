<?php
session_start();

if (!isset($_SESSION['userData']['cod_usuario'])) {
  header("Location: ../login/logout.php");
  exit;
}

if (
  !isset($_SESSION['userData']['cod_privilegio']) ||
  !in_array(intval($_SESSION['userData']['cod_privilegio']), array(1, 2))
) {
  header("Location: ../login/restriccion.php");
  exit;
}

require_once __DIR__ . '/../../conexion/configuracion.php';

$delimiter = ";";
$filename = "donaciones_colecta_flr_" . date('d-m-Y') . ".csv";

$f = fopen('php://memory', 'w');

$fields = array(
  'id',
  'Nombre',
  'Email',
  utf8_decode('Teléfono'),
  'Monto',
  'Fecha',
  'Estado',
  'Metodo pago',
  'Ciudad',
  utf8_decode('Región'),
  'Pais',
  'utm_source',
  'utm_medium',
  'utm_campaign'
);

fputcsv($f, $fields, $delimiter);

$query = $db->query("
    SELECT
        dn.id,
        dn.nombre,
        dn.email,
        dn.telefono,
        dn.monto,
        DATE_FORMAT(dn.fecha, '%d-%m-%Y %H:%i') AS fecha_hora,
        es.estado,
        mp.institucion,
        dn.ip_ciudad,
        dn.ip_region,
        dn.ip_pais,
        dn.utm_source,
        dn.utm_medium,
        dn.utm_campaign
    FROM donaciones dn
    LEFT JOIN estados es ON dn.estado_id = es.id
    LEFT JOIN medios_pago mp ON dn.medio_pago_id = mp.id
    ORDER BY dn.id DESC
");

if (!$query) {
  die('Error SQL: ' . $db->error);
}

if ($query && $query->num_rows > 0) {
  while ($row = $query->fetch_assoc()) {
    $lineData = array(
      isset($row['id']) ? $row['id'] : '',
      utf8_decode(isset($row['nombre']) ? $row['nombre'] : ''),
      utf8_decode(isset($row['email']) ? $row['email'] : ''),
      isset($row['telefono']) ? $row['telefono'] : '',
      isset($row['monto']) ? $row['monto'] : '',
      isset($row['fecha_hora']) ? $row['fecha_hora'] : '',
      utf8_decode(isset($row['estado']) ? $row['estado'] : ''),
      utf8_decode(isset($row['institucion']) ? $row['institucion'] : ''),
      utf8_decode(isset($row['ip_ciudad']) ? $row['ip_ciudad'] : ''),
      utf8_decode(isset($row['ip_region']) ? $row['ip_region'] : ''),
      utf8_decode(isset($row['ip_pais']) ? $row['ip_pais'] : ''),
      utf8_decode(isset($row['utm_source']) ? $row['utm_source'] : ''),
      utf8_decode(isset($row['utm_medium']) ? $row['utm_medium'] : ''),
      utf8_decode(isset($row['utm_campaign']) ? $row['utm_campaign'] : '')
    );

    fputcsv($f, $lineData, $delimiter);
  }
}

fseek($f, 0);

header('Content-Type: text/csv; charset=ISO-8859-1');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

fpassthru($f);
fclose($f);
exit;