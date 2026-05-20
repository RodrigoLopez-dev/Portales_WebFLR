<?php

function userCanAccessMenu($userPrivilege, $allowedPrivileges)
{
    $allowed = explode(',', $allowedPrivileges);

    foreach ($allowed as $priv) {
        if (intval(trim($priv)) === intval($userPrivilege)) {
            return true;
        }
    }

    return false;
}

function getSidebarMenus($db, $userPrivilege)
{
    $menus = array();

    $result = $db->query("
        SELECT menu_key, label, url, icon, enabled, allowed_privileges, sort_order
        FROM sidebar_menus
        WHERE enabled = 1
        ORDER BY sort_order ASC
    ");

    if (!$result) {
        return $menus;
    }

    while ($row = $result->fetch_assoc()) {
        if (userCanAccessMenu($userPrivilege, $row['allowed_privileges'])) {
            $menus[] = $row;
        }
    }

    return $menus;
}