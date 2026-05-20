<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$textqr = isset($_POST['textqr']) ? trim($_POST['textqr']) : '';
$sizeqr = isset($_POST['sizeqr']) ? (int)$_POST['sizeqr'] : 300;

if ($textqr === '') {
    http_response_code(400);
    exit('QR vacío');
}

if ($sizeqr <= 0 || $sizeqr > 1000) {
    $sizeqr = 300;
}

$autoload = __DIR__ . '/vendor/autoload.php';

if (!file_exists($autoload)) {
    http_response_code(500);
    exit('Autoload no encontrado');
}

require_once $autoload;

use Endroid\QrCode\QrCode;

try {
    $qrCode = new QrCode($textqr);
    $qrCode->setSize($sizeqr);

    $image = $qrCode->writeString();
    $imageData = base64_encode($image);

    echo '<img src="data:image/png;base64,' . $imageData . '" alt="QR">';
    exit;

} catch (Exception $e) {
    error_log('ERROR QR MACH: ' . $e->getMessage());

    http_response_code(500);
    exit('No fue posible generar el QR');
}
?>