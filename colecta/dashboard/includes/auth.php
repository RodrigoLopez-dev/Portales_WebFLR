<?php
require_once __DIR__ . '/../../conexion/configuracion.php';
require_once __DIR__ . '/../../includes/helpers.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userData']['cod_usuario'])) {
    redirect('dashboard/login/');
}

if ($_SESSION['userData']['cod_privilegio'] == 0) {
    redirect('dashboard/login/restriccion.php');
}

function require_admin()
{
    if (!isset($_SESSION['userData']['cod_privilegio']) || $_SESSION['userData']['cod_privilegio'] != 1) {
        redirect('dashboard/dashboard/index.php');
    }
}