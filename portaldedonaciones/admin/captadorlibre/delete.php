<?php

session_start();
date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/database.php';

if (!isset($_SESSION['userData']['cod_usuario'])) {
    header('Location: ../login/logout.php');
    exit;
}

$database = new Database();

$rut = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut = isset($_POST['rut']) ? trim($_POST['rut']) : '';
} else {
    $rut = isset($_GET['rut']) ? trim($_GET['rut']) : '';
}

if ($rut === '') {
    header('Location: index.php?mensaje=rut_invalido');
    exit;
}

$res = $database->delete($rut);

if ($res) {
    header('Location: index.php?mensaje=eliminado');
    exit;
}

header('Location: index.php?mensaje=error');
exit;