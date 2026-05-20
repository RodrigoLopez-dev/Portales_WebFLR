<?php
if (!isset($currentPage)) {
    $currentPage = '';
}

?>
<header class="site-header">
    <div class="site-topbar">
        <a class="site-brand" href="./index.php" aria-label="Fundación Las Rosas - Inicio">
            <img src="./assets/img/home_logo_white.png" alt="Fundación Las Rosas" class="site-logo">
        </a>

        <nav class="site-nav" aria-label="Navegación principal">
            <a href="./index.php" class="<?= $currentPage === 'inicio' ? 'active' : '' ?>">Inicio</a>
            <a href="./enciende.php" class="<?= $currentPage === 'enciende' ? 'active' : '' ?>">Enciende tu vela</a>
            <a href="./velas.php" class="<?= $currentPage === 'velas' ? 'active' : '' ?>">Velas encendidas</a>
        </nav>

        <button class="site-menu-toggle" type="button" aria-label="Abrir menú" aria-expanded="false"
            aria-controls="siteMobileMenu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>

    <nav id="siteMobileMenu" class="site-mobile-nav" hidden aria-label="Navegación móvil">
        <a href="./index.php" class="<?= $currentPage === 'inicio' ? 'active' : '' ?>">Inicio</a>
        <a href="./enciende.php" class="<?= $currentPage === 'enciende' ? 'active' : '' ?>">Enciende tu vela</a>
        <a href="./velas.php" class="<?= $currentPage === 'velas' ? 'active' : '' ?>">Velas encendidas</a>
    </nav>
</header>