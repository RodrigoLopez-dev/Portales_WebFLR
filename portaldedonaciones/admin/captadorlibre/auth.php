<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Santiago');

function captador_get_privilegio()
{
    return isset($_SESSION['userData']['cod_privilegio'])
        ? (int) $_SESSION['userData']['cod_privilegio']
        : 0;
}

function captador_require_login()
{
    if (!isset($_SESSION['userData']['cod_usuario'])) {
        header('Location: ../login/logout.php');
        exit;
    }
}

function captador_require_privilegios($permitidos)
{
    captador_require_login();

    $cod_privilegio = captador_get_privilegio();

    if (!in_array($cod_privilegio, $permitidos, true)) {
        header('Location: ../login/restriccion.php');
        exit;
    }
}

function captador_require_admin()
{
    captador_require_privilegios(array(1));
}

function captador_require_captadores()
{
    captador_require_privilegios(array(1, 2, 4));
}

function captador_require_servicios()
{
    captador_require_privilegios(array(1, 2, 6));
}

function captador_csrf_token()
{
    if (
        !isset($_SESSION['captador_csrf_token']) ||
        $_SESSION['captador_csrf_token'] === ''
    ) {
        $_SESSION['captador_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['captador_csrf_token'];
}

function captador_validate_csrf()
{
    $sessionToken = isset($_SESSION['captador_csrf_token'])
        ? $_SESSION['captador_csrf_token']
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