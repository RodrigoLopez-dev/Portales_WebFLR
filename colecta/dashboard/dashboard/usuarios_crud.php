<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/privilegios.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard/dashboard/usuarios.php');
}

$csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

if (
    empty($_SESSION['csrf_token']) ||
    $csrfToken === '' ||
    $csrfToken !== $_SESSION['csrf_token']
) {
    redirect('dashboard/dashboard/usuarios.php?error=csrf');
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$priv = isset($_POST['privilegio']) ? intval($_POST['privilegio']) : 0;

$privilegiosPermitidos = getPrivilegiosPermitidos();

if ($id <= 0 || !in_array($priv, $privilegiosPermitidos)) {
    redirect('dashboard/dashboard/usuarios.php?error=invalid');
}

$currentUserId = isset($_SESSION['userData']['cod_usuario']) ? intval($_SESSION['userData']['cod_usuario']) : 0;

if ($id === $currentUserId && $priv === 0) {
    redirect('dashboard/dashboard/usuarios.php?error=self_block');
}

/*
 * Validar si el usuario actual a modificar es admin.
 */
$stmt = $db->prepare("SELECT cod_privilegio FROM usuarios WHERE cod_usuario=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($currentPriv);
$exists = $stmt->fetch();
$stmt->close();

if (!$exists) {
    redirect('dashboard/dashboard/usuarios.php?error=invalid');
}

/*
 * Si se está quitando privilegio admin, verificar que no sea el último admin.
 */
if (intval($currentPriv) === 1 && $priv !== 1) {
    $result = $db->query("SELECT COUNT(*) AS total FROM usuarios WHERE cod_privilegio = 1");
    $row = $result->fetch_assoc();
    $totalAdmins = intval($row['total']);

    if ($totalAdmins <= 1) {
        redirect('dashboard/dashboard/usuarios.php?error=last_admin');
    }
}

$stmt = $db->prepare("UPDATE usuarios SET cod_privilegio=?, modified=NOW() WHERE cod_usuario=?");
$stmt->bind_param("ii", $priv, $id);
$stmt->execute();
$stmt->close();

redirect('dashboard/dashboard/usuarios.php?ok=updated');