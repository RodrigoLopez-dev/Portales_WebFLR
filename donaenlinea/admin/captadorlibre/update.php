<?php

session_start();
date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/database.php';

if (!isset($_SESSION['userData']['cod_usuario'])) {
    header('Location: ../login/logout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$database = new Database();

$codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
$cod_POS = isset($_POST['cod_POS']) ? trim($_POST['cod_POS']) : '';
$rut = isset($_POST['rut']) ? trim($_POST['rut']) : '';
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$oficina = isset($_POST['oficina']) ? trim($_POST['oficina']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$mes_ingreso = isset($_POST['mes_ingreso']) ? trim($_POST['mes_ingreso']) : '';
$proyecto = isset($_POST['proyecto']) ? trim($_POST['proyecto']) : '';
$estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';

if ($rut === '' || $codigo === '' || $nombre === '') {
    header('Location: index.php?mensaje=datos_incompletos');
    exit;
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php?mensaje=email_invalido');
    exit;
}

$res = $database->update(
    $codigo,
    $cod_POS,
    $rut,
    $nombre,
    $oficina,
    $email,
    $mes_ingreso,
    $proyecto,
    $estado
);

if ($res) {
    header('Location: index.php?mensaje=actualizado');
    exit;
}

header('Location: index.php?mensaje=error');
exit;