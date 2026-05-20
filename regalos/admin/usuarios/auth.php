<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Santiago');

function usuarios_require_admin()
{
    if (!isset($_SESSION['userData']['cod_usuario'])) {
        header('Location: ../login/logout.php');
        exit;
    }

    $cod_privilegio = isset($_SESSION['userData']['cod_privilegio'])
        ? (int) $_SESSION['userData']['cod_privilegio']
        : 0;

    /*
     * Gestión de usuarios solo para administrador.
     * Ajusta este número si tu administrador usa otro privilegio.
     */
    if ($cod_privilegio !== 1) {
        header('Location: ../login/restriccion.php');
        exit;
    }
}

function usuarios_csrf_token()
{
    if (
        !isset($_SESSION['usuarios_csrf_token']) ||
        $_SESSION['usuarios_csrf_token'] === ''
    ) {
        $_SESSION['usuarios_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['usuarios_csrf_token'];
}

function usuarios_validate_csrf()
{
    $sessionToken = isset($_SESSION['usuarios_csrf_token'])
        ? $_SESSION['usuarios_csrf_token']
        : '';

    $postToken = isset($_POST['csrf_token'])
        ? $_POST['csrf_token']
        : '';

    if ($sessionToken === '' || $postToken === '') {
        header('Location: index.php?mensaje=csrf_invalido');
        exit;
    }

    if (!hash_equals($sessionToken, $postToken)) {
        header('Location: index.php?mensaje=csrf_invalido');
        exit;
    }
}

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function usuarios_privilegios()
{
    return array(
        0 => 'Sin autorización',
        1 => 'Administrador',
        2 => 'Descargas',
        4 => 'Captador libre',
        6 => 'Área Servicios'
    );
}

function usuarios_nombre_privilegio($codigo)
{
    $codigo = (int) $codigo;
    $privilegios = usuarios_privilegios();

    return isset($privilegios[$codigo])
        ? $privilegios[$codigo]
        : 'Privilegio ' . $codigo;
}