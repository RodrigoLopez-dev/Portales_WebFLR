<?php
header("Content-Type: text/html;charset=utf-8");
session_start();
if (!isset($_SESSION['userData']['cod_usuario'])) {
  header("Location: ../login/logout.php");
} else if ($_SESSION['userData']['cod_privilegio'] != 2) {
  header("Location: ../login/restriccion.php");
} else {
  require_once __DIR__ . '/../../conexion/configuracion.php';
  //get records from database
  $query = $db->query("SELECT * FROM v_rifa ORDER BY id DESC");
  if ($query->num_rows > 0) {
    $delimiter = ";";
    $filename = "rifa_colecta_mayor_" . date('d-m-Y') . ".csv";
    //create a file pointer
    $f = fopen('php://memory', 'w');
    //set column headers
    $fields = array('id', 'Nombre', 'Email', utf8_decode('Teléfono'), utf8_decode('Número de rifa'), 'Monto', 'Oportunidades', 'Fecha', );
    fputcsv($f, $fields, $delimiter);
    //output each row of the data, format line as csv and write to file pointer
    while ($row = $query->fetch_assoc()) {
      //    $status = ($row['status'] == '1')?'Active':'Inactive';
      $lineData = array(
        $row['id'],
        utf8_decode($row['nombre']),
        utf8_decode($row['email']),
        $row['telefono'],
        utf8_decode($row['numero'])
        ,
        $row['monto'],
        utf8_decode($row['oportunidad']),
        utf8_decode($row['fecha'])
      );
      fputcsv($f, $lineData, $delimiter);
    }
    //move back to beginning of file
    fseek($f, 0);
    //set headers to download file rather than displayed
    header('Content-Type: text/csv charset=iso-8859-1');
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    //output all remaining data on a file pointer
    fpassthru($f);
  }
  exit;
?>
  <?php
  }
?>