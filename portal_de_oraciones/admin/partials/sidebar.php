<div class="sidebar">
  <div class="sidebarBrand">
    <h2>Portal Admin</h2>
    <span>Navegación del módulo</span>
  </div>

  <div class="menu">
    <a href="dashboard.php" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
    <a href="usuarios.php" class="<?= $currentPage === 'usuarios' ? 'active' : '' ?>">Usuarios</a>
    <a href="settings.php" class="<?= $currentPage === 'settings' ? 'active' : '' ?>">Configuración</a>
    <a href="logs.php" class="<?= $currentPage === 'logs' ? 'active' : '' ?>">Logs</a>
  </div>

  <div class="sidebarFooter">
    <a href="logout.php">Cerrar sesión</a>
  </div>
</div>