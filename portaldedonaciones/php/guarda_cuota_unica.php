<?php

require_once __DIR__ . '/../enigma.php';
require_once __DIR__ . '/../config/database.php';

date_default_timezone_set('America/Santiago');

$eni = new Enigma();
$conn = db_connect();

function post_value($key, $default = '')
{
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : $default;
}

function redirect_to($url)
{
    header('Location: ' . $url);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método no permitido.';
    exit;
}

$id_amigo = post_value('id', 'vacio');
$nom = post_value('nombre', 'vacio');
$mail = post_value('email', '');
$monto = post_value('monto', '');
$utm_source = post_value('utm_source', '');
$utm_medium = post_value('utm_medium', '');
$utm_campaign = post_value('utm_campaign', '');

$nom = ucwords(strtolower($nom));
$mail = strtolower($mail);

if ($mail === '' || !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo 'Email inválido.';
    exit;
}

if ($monto === '' || !ctype_digit($monto) || (int) $monto <= 0) {
    http_response_code(400);
    echo 'Monto inválido.';
    exit;
}

$monto = (int) $monto;

/*
|--------------------------------------------------------------------------
| Validar si el email ya aceptó cuota única
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare(
    'SELECT nombre, monto, fecha
     FROM cuotas_unicas
     WHERE email = ?
       AND fecha >= DATE_SUB(NOW(), INTERVAL 120 DAY)
     ORDER BY fecha DESC
     LIMIT 1'
);

if (!$stmt) {
    error_log('Error preparando SELECT cuotas_unicas: ' . $conn->error);
    http_response_code(500);
    echo 'Error interno.';
    exit;
}

$stmt->bind_param('s', $mail);
$stmt->execute();
$result = $stmt->get_result();
$row = $result ? $result->fetch_assoc() : null;
$stmt->close();

if ($row && !empty($row['nombre'])) {
    $nombreExistente = htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8');
    $montoExistente = htmlspecialchars($row['monto'], ENT_QUOTES, 'UTF-8');

    echo '<script>
        alert("Estimado/a ' . $nombreExistente . ', recientemente ya aceptaste un cargo de cuota única por un monto de $' . $montoExistente . ', para más información llámanos al 800 720 111");
        window.location = "https://fundacionlasrosas.cl";
    </script>';
    exit;
}

/*
|--------------------------------------------------------------------------
| Insertar nueva cuota única
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare(
    'INSERT INTO cuotas_unicas 
    (rut, nombre, email, monto, utm_source, utm_medium, utm_campaign) 
    VALUES (?, ?, ?, ?, ?, ?, ?)'
);

if (!$stmt) {
    error_log('Error preparando INSERT cuotas_unicas: ' . $conn->error);
    http_response_code(500);
    echo 'Error interno.';
    exit;
}

$stmt->bind_param(
    'sssisss',
    $id_amigo,
    $nom,
    $mail,
    $monto,
    $utm_source,
    $utm_medium,
    $utm_campaign
);

if (!$stmt->execute()) {
    error_log('Error ejecutando INSERT cuotas_unicas: ' . $stmt->error);
    $stmt->close();

    http_response_code(500);
    echo 'Error interno.';
    exit;
}

$idRegistro = $conn->insert_id;
$stmt->close();
$conn->close();

$id = $eni->encode_this('&id=' . $idRegistro);

redirect_to('../gracias_amigo?' . $id);