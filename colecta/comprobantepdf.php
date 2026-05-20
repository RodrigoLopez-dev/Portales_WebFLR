<?php
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

if (!isset($_GET["cod"])) {
    header("Location: ./");
    exit(); // Agregamos exit para detener la ejecución si redirigimos
}

include "conexion/configuracion.php";

$query3 = $db->query("SELECT * from donaciones WHERE id=" . $_GET['cod']);
$row3 = $query3->fetch_assoc();
$nombreCliente = $row3['nombre'];
$rut = $row3['rut'];
$monto = $row3["monto"];

ob_start(); // Iniciamos el buffer de salida

echo '

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
   <link rel="stylesheet" type="text/css" href="css/estilopdf.css"> 
</head>

<body>
    <div class="rg-container">
    <table border="1" class="rg-table" summary="Hed">
            <tbody>
    <tr class="">
                     <img src="images/logo2.png" style="max-width:30%;" />
                       </tr>
                        </tbody>
        </table>
        <table border="1" class="rg-table" summary="Hed">
            <tbody>
                <tr class="">
                    <td class="text" colspan="2"><b>Comprobante de donación</b></td>
                </tr>
                <tr class="">
                    <td class="text">Por un monto de :</td>
                    <td class="text">$' . number_format($monto, 0, '', '.') . '</td>
                </tr>
                <tr class="">
                    <td class="text">Tu número de orden es :</td>
                    <td class="text">' . $_GET['cod'] . '</td>
                </tr>
                <tr class="">
                    <td class="text" colspan="2"><b>Datos donante</b></td>
                </tr>
                <tr class="">
                    <td class="text">Nombre :</td>
                    <td class="text">' . $nombreCliente . '</td>
                </tr>
                <tr class="">
                    <td class="text">Rut :</td>
                    <td class="text">' . $rut . '</td>
                </tr>
            </tbody>
        </table>
        <table border="0" class="rg-table" summary="Hed">
            <tbody>
                <tr>
                <img src="images/timbre.png" alt="" style="max-width:100%;width:auto;height:auto;" />
              </tr>
        </tbody>
        </table>
    </div>
</body>
</html>';

$content = ob_get_clean();
$dompdf = new Dompdf();
$dompdf->loadHtml($content);
$dompdf->render();
$filename = "comprobante_fundacion_las_rosas.pdf";
$font = $dompdf->getFontMetrics()->get_font('Arial, Helvetica, sans-serif', 'normal');
$dompdf->stream($filename);
?>