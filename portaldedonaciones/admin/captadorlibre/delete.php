<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/database.php';

captador_require_captadores();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?mensaje=metodo_invalido');
    exit;
}

captador_validate_csrf();

$rut = isset($_POST['rut']) ? trim($_POST['rut']) : '';

if ($rut === '') {
    header('Location: index.php?mensaje=rut_invalido');
    exit;
}

$database = new Database();

$res = $database->delete($rut);

if ($res) {
    header('Location: index.php?mensaje=eliminado');
    exit;
}

header('Location: index.php?mensaje=error');
exit;