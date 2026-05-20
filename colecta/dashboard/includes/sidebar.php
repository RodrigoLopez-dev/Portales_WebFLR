<?php
require_once __DIR__ . '/sidebar_menus.php';

$userPrivilege = isset($_SESSION['userData']['cod_privilegio'])
    ? intval($_SESSION['userData']['cod_privilegio'])
    : 0;

$dynamicMenus = getSidebarMenus($db, $userPrivilege);
?>

<div class="sidebar" data-color="purple" data-background-color="white"
    data-image="<?php echo asset('dashboard/assets/img/sidebar-3.jpg'); ?>">

    <div class="logo">
        <a href="<?php echo base_url('dashboard/dashboard/'); ?>" class="simple-text logo-normal">
            Dashboard
        </a>
    </div>

    <div class="logo">
        <a class="simple-text logo-normal">
            <img src="https://fundacionlasrosas.cl/imagen_corporativa/logos/FLR_cuadb.png" width="150px"><br>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <ul class="nav">

            <?php if (count($dynamicMenus) > 0): ?>

                <?php foreach ($dynamicMenus as $menu): ?>
                    <?php
                    $menuKey = isset($menu['menu_key']) ? $menu['menu_key'] : '';
                    $label = isset($menu['label']) ? $menu['label'] : '';
                    $url = isset($menu['url']) ? $menu['url'] : '#';
                    $icon = isset($menu['icon']) ? $menu['icon'] : 'circle';
                    $activeClass = (isset($currentPage) && $currentPage == $menuKey) ? 'active' : '';
                    ?>

                    <li class="nav-item <?php echo $activeClass; ?>">
                        <a class="nav-link" href="<?php echo base_url($url); ?>">
                            <i class="material-icons"><?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?></i>
                            <p><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></p>
                        </a>
                    </li>
                <?php endforeach; ?>

            <?php else: ?>

                <li class="nav-item <?php echo (isset($currentPage) && $currentPage == 'dashboard') ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo base_url('dashboard/dashboard/'); ?>">
                        <i class="material-icons">dashboard</i>
                        <p>Donaciones</p>
                    </a>
                </li>

                <li class="nav-item <?php echo (isset($currentPage) && $currentPage == 'rifa') ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo base_url('dashboard/dashboard/rifa.php'); ?>">
                        <i class="material-icons">confirmation_number</i>
                        <p>Rifa</p>
                    </a>
                </li>

                <?php if ($userPrivilege == 1): ?>
                    <li class="nav-item <?php echo (isset($currentPage) && $currentPage == 'usuarios') ? 'active' : ''; ?>">
                        <a class="nav-link" href="<?php echo base_url('dashboard/dashboard/usuarios.php'); ?>">
                            <i class="material-icons">people</i>
                            <p>Usuarios</p>
                        </a>
                    </li>

                    <li
                        class="nav-item <?php echo (isset($currentPage) && $currentPage == 'configuracion') ? 'active' : ''; ?>">
                        <a class="nav-link" href="<?php echo base_url('dashboard/dashboard/configuracion.php'); ?>">
                            <i class="material-icons">settings</i>
                            <p>Configuración</p>
                        </a>
                    </li>
                <?php endif; ?>

                <li
                    class="nav-item <?php echo (isset($currentPage) && $currentPage == 'descarga_maestro') ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo base_url('dashboard/crud/csvMaestro.php'); ?>">
                        <i class="material-icons">vertical_align_bottom</i>
                        <p>Descarga maestro</p>
                    </a>
                </li>

            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo base_url('dashboard/login/logout.php'); ?>"
                    onclick="return confirm('¿Seguro que deseas cerrar sesión?');">
                    <i class="material-icons">logout</i>
                    <p>Cerrar sesión</p>
                </a>
            </li>

        </ul>
    </div>
</div>