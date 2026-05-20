<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/privilegios.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard/dashboard/configuracion_sidebar.php');
}

$csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

if (
    empty($_SESSION['csrf_token']) ||
    $csrfToken === '' ||
    $csrfToken !== $_SESSION['csrf_token']
) {
    redirect('dashboard/dashboard/configuracion_sidebar.php?error=csrf');
}

$allowedPrivileges = getPrivilegiosPermitidos();

$result = $db->query("
    SELECT id, menu_key
    FROM sidebar_menus
    ORDER BY sort_order ASC
");

if (!$result) {
    redirect('dashboard/dashboard/configuracion_sidebar.php?error=query');
}

while ($menu = $result->fetch_assoc()) {
    $id = intval($menu['id']);
    $menuKey = $menu['menu_key'];

    $enabled = isset($_POST['enabled_' . $id]) ? 1 : 0;
    $sortOrder = isset($_POST['sort_order_' . $id]) ? intval($_POST['sort_order_' . $id]) : 0;

    $privilegesPost = isset($_POST['privileges_' . $id]) ? $_POST['privileges_' . $id] : array();
    $cleanPrivileges = array();

    foreach ($privilegesPost as $priv) {
        $priv = intval($priv);

        if (in_array($priv, $allowedPrivileges)) {
            $cleanPrivileges[] = $priv;
        }
    }

    /*
     * Seguridad: configuración siempre debe quedar activa para admin.
     */
    if ($menuKey === 'configuracion') {
        $enabled = 1;

        if (!in_array(1, $cleanPrivileges)) {
            $cleanPrivileges[] = 1;
        }
    }

    /*
     * Seguridad: usuarios siempre debe quedar disponible para admin.
     */
    if ($menuKey === 'usuarios') {
        if (!in_array(1, $cleanPrivileges)) {
            $cleanPrivileges[] = 1;
        }
    }

    if (count($cleanPrivileges) == 0) {
        $cleanPrivileges[] = 1;
    }

    $privilegesValue = implode(',', $cleanPrivileges);

    $stmt = $db->prepare("
        UPDATE sidebar_menus
        SET enabled = ?,
            allowed_privileges = ?,
            sort_order = ?,
            modified = NOW()
        WHERE id = ?
    ");

    $stmt->bind_param("isii", $enabled, $privilegesValue, $sortOrder, $id);
    $stmt->execute();
    $stmt->close();
}

redirect('dashboard/dashboard/configuracion_sidebar.php?ok=saved');