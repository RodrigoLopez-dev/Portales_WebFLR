<?php

$cod_privilegio = isset($_SESSION['userData']['cod_privilegio'])
    ? (int) $_SESSION['userData']['cod_privilegio']
    : 0;

$currentPage = isset($currentPage) ? $currentPage : '';

function active_menu($page, $currentPage)
{
    return $page === $currentPage ? 'active' : '';
}

?>
<div class="sidebar" data-color="purple" data-background-color="white" data-image="../../assets/img/sidebar-3.jpg">
    <div class="logo">
        <a href="../dashboard/" class="simple-text logo-normal">
            dashboard <br>
            <img src="../../assets/img/logofinal01.png" width="150px">
        </a>
    </div>

    <div class="sidebar-wrapper">
        <ul class="nav">

            <?php if ($cod_privilegio === 1): ?>
                <li class="nav-item <?php echo active_menu('dashboard', $currentPage); ?>">
                    <a class="nav-link" href="../dashboard/">
                        <i class="material-icons">dashboard</i>
                        <p>Portal de donaciones</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (in_array($cod_privilegio, array(1, 2), true)): ?>
                <li class="nav-item <?php echo active_menu('descargas', $currentPage); ?>">
                    <a class="nav-link" href="../dashboard/descargas.php">
                        <i class="material-icons">vertical_align_bottom</i>
                        <p>Descargas</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (in_array($cod_privilegio, array(1, 2, 4), true)): ?>
                <li class="nav-item <?php echo active_menu('captadorlibre', $currentPage); ?>">
                    <a class="nav-link" href="../captadorlibre/">
                        <i class="material-icons">add_circle_outline</i>
                        <p>Captadores libres</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (in_array($cod_privilegio, array(1, 2, 6), true)): ?>
                <li class="nav-item <?php echo active_menu('servicios', $currentPage); ?>">
                    <a class="nav-link" href="../captadorlibre/servicios.php">
                        <i class="material-icons">add_circle_outline</i>
                        <p>Área Servicios</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($cod_privilegio === 1): ?>
                <li class="nav-item <?php echo active_menu('usuarios', $currentPage); ?>">
                    <a class="nav-link" href="../usuarios/">
                        <i class="material-icons">people</i>
                        <p>Gestión de usuarios</p>
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link" href="../login/logout.php">
                    <i class="material-icons">logout</i>
                    <p>Cerrar sesión</p>
                </a>
            </li>

        </ul>
    </div>
</div>