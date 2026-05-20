<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../config/database.php';

usuarios_require_admin();
usuarios_validate_csrf();

$db = db_connect();
$db->set_charset('utf8');

$accion = isset($_POST['accion']) ? trim($_POST['accion']) : '';
$cod_usuario = isset($_POST['cod_usuario']) ? (int) $_POST['cod_usuario'] : 0;

if ($cod_usuario <= 0) {
    header('Location: index.php');
    exit;
}

$sessionOauthUid = isset($_SESSION['userData']['cod_usuario'])
    ? $_SESSION['userData']['cod_usuario']
    : '';

function contar_administradores($db)
{
    $total = 0;

    $stmt = $db->prepare("
        SELECT COUNT(*)
        FROM usuarios
        WHERE cod_privilegio = 1
    ");

    if (!$stmt) {
        die('Error preparando conteo de administradores.');
    }

    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();

    return (int) $total;
}

$stmtCheck = $db->prepare("
    SELECT oauth_uid, cod_privilegio
    FROM usuarios
    WHERE cod_usuario = ?
    LIMIT 1
");

if (!$stmtCheck) {
    die('Error preparando validación.');
}

$stmtCheck->bind_param('i', $cod_usuario);
$stmtCheck->execute();
$stmtCheck->bind_result($targetOauthUid, $targetPrivilegioActual);
$stmtCheck->fetch();
$stmtCheck->close();

$targetPrivilegioActual = (int) $targetPrivilegioActual;

if ($accion === 'actualizar_privilegio') {
    $cod_privilegio = isset($_POST['cod_privilegio']) ? (int) $_POST['cod_privilegio'] : 0;

    $privilegiosPermitidos = array(0, 1, 2, 4, 6);

    if (!in_array($cod_privilegio, $privilegiosPermitidos, true)) {
        header('Location: index.php?mensaje=privilegio_invalido');
        exit;
    }

    /*
     * No permitir que el usuario logeado se quite su propio privilegio admin.
     */
    if ($targetOauthUid === $sessionOauthUid && $cod_privilegio !== 1) {
        header('Location: index.php?mensaje=no_auto_degradar');
        exit;
    }

    /*
     * No permitir dejar el sistema sin administradores.
     * Aplica cuando el usuario editado actualmente es admin
     * y se intenta cambiar a otro privilegio.
     */
    if ($targetPrivilegioActual === 1 && $cod_privilegio !== 1) {
        $totalAdmins = contar_administradores($db);

        if ($totalAdmins <= 1) {
            header('Location: index.php?mensaje=minimo_un_admin');
            exit;
        }
    }

    $stmt = $db->prepare("
        UPDATE usuarios
        SET cod_privilegio = ?
        WHERE cod_usuario = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die('Error preparando actualización.');
    }

    $stmt->bind_param('ii', $cod_privilegio, $cod_usuario);

    if (!$stmt->execute()) {
        $stmt->close();
        die('Error actualizando privilegio.');
    }

    $stmt->close();

    header('Location: index.php?mensaje=actualizado');
    exit;
}

if ($accion === 'eliminar') {
    /*
     * No permitir que el usuario logeado se elimine a sí mismo.
     */
    if ($targetOauthUid === $sessionOauthUid) {
        header('Location: index.php?mensaje=no_auto_eliminar');
        exit;
    }

    /*
     * No permitir eliminar al último administrador.
     */
    if ($targetPrivilegioActual === 1) {
        $totalAdmins = contar_administradores($db);

        if ($totalAdmins <= 1) {
            header('Location: index.php?mensaje=minimo_un_admin');
            exit;
        }
    }

    $stmt = $db->prepare("
        DELETE FROM usuarios
        WHERE cod_usuario = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die('Error preparando eliminación.');
    }

    $stmt->bind_param('i', $cod_usuario);

    if (!$stmt->execute()) {
        $stmt->close();
        die('Error eliminando usuario.');
    }

    $stmt->close();

    header('Location: index.php?mensaje=eliminado');
    exit;
}

header('Location: index.php');
exit;