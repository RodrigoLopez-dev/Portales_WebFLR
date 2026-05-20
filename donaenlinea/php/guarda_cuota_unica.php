<?php

require_once __DIR__ . '/../enigma.php';
require_once __DIR__ . '/../config/database.php';

date_default_timezone_set('America/Santiago');

$eni = new Enigma();
$conn = db_connect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST)) {
    header('Location: ../');
    exit;
}

$requiredFields = array('id', 'nombre', 'email', 'monto');

foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
        echo 'faltan_datos';
        exit;
    }
}

$id_amigo = trim($_POST['id']);
$nom = ucwords(strtolower(trim($_POST['nombre'])));
$mail = strtolower(trim($_POST['email']));
$monto = isset($_POST['monto']) ? (int)$_POST['monto'] : 0;

$utm_source = isset($_POST['utm_source']) ? trim($_POST['utm_source']) : '';
$utm_medium = isset($_POST['utm_medium']) ? trim($_POST['utm_medium']) : '';
$utm_campaign = isset($_POST['utm_campaign']) ? trim($_POST['utm_campaign']) : '';

if ($monto <= 0 || !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    echo 'datos_invalidos';
    exit;
}

$selectStmt = $conn->prepare("
    SELECT nombre, monto
    FROM cuotas_unicas
    WHERE email = ?
    LIMIT 1
");

if (!$selectStmt) {
    error_log('Error preparando SELECT cuotas_unicas: ' . $conn->error);
    echo 'error';
    exit;
}

$selectStmt->bind_param('s', $mail);

if (!$selectStmt->execute()) {
    error_log('Error ejecutando SELECT cuotas_unicas: ' . $selectStmt->error);
    $selectStmt->close();
    echo 'error';
    exit;
}

$selectStmt->bind_result($nombreExistente, $montoExistente);
$existe = $selectStmt->fetch();

$selectStmt->close();

if ($existe) {
    $nombreSafe = htmlspecialchars($nombreExistente, ENT_QUOTES, 'UTF-8');
    $montoSafe = number_format((int)$montoExistente, 0, ',', '.');

    echo '<script>
        alert("Estimado/a ' . $nombreSafe . ', recientemente ya aceptaste un cargo de cuota única por un monto de $' . $montoSafe . ', para más información llámanos al 800 720 111");
        window.location = "https://fundacionlasrosas.cl";
    </script>';
    exit;
}

$insertStmt = $conn->prepare("
    INSERT INTO cuotas_unicas
    (
        rut,
        nombre,
        email,
        monto,
        utm_source,
        utm_medium,
        utm_campaign
    )
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

if (!$insertStmt) {
    error_log('Error preparando INSERT cuotas_unicas: ' . $conn->error);
    echo 'error';
    exit;
}

$insertStmt->bind_param(
    'sssisss',
    $id_amigo,
    $nom,
    $mail,
    $monto,
    $utm_source,
    $utm_medium,
    $utm_campaign
);

if (!$insertStmt->execute()) {
    error_log('Error ejecutando INSERT cuotas_unicas: ' . $insertStmt->error);
    $insertStmt->close();
    echo 'error';
    exit;
}

$idRegistro = $insertStmt->insert_id;

$insertStmt->close();

$id = $eni->encode_this('&id=' . $idRegistro);

header('Location: ../gracias_amigo?' . $id);
exit;