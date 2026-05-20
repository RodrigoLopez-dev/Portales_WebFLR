<?php

$nombre = "Rodrigo";
$monto = 15000;

$baseUrl = 'https://www.fundacionlasrosas.cl/colecta';

$montoFormateado = '$' . number_format($monto, 0, ',', '.');

$mensaje = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
</head>
<body style="margin:0; padding:0; background:#f4f4f4; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td align="center">

<table width="700" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;">

<tr>
<td>
<img src="' . $baseUrl . '/images/HEADER700x452.jpg"
     width="700"
     style="display:block; width:100%;">
</td>
</tr>

<tr>
<td style="padding:32px;">

<p><strong>Estimado/a ' . $nombre . ',</strong></p>

<p>
Te agradecemos profundamente tu donación de
<strong>' . $montoFormateado . '</strong>.
</p>

<p>
Gracias por cubrirnos con tu generosidad.
</p>

</td>
</tr>

<tr>
<td>
<img src="' . $baseUrl . '/images/FOOTER-700x90.jpg"
     width="700"
     style="display:block; width:100%;">
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>';

echo $mensaje;