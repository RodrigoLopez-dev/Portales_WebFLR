<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/privilegios.php';

$pageTitle = 'Configuración Sidebar';
$currentPage = 'configuracion';

function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$menus = array();

$result = $db->query("
    SELECT id, menu_key, label, url, icon, enabled, allowed_privileges, sort_order
    FROM sidebar_menus
    ORDER BY sort_order ASC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $menus[] = $row;
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-panel">

    <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top">
        <div class="container-fluid">
            <div class="navbar-wrapper">
                <a class="navbar-brand" href="#">Configuración Sidebar</a>
            </div>
        </div>
    </nav>

    <div class="content">
        <div class="container-fluid">

            <h3>Configuración Sidebar / Menús</h3>

            <?php if (isset($_GET['ok']) && $_GET['ok'] == 'saved'): ?>
                <div class="alert alert-success">
                    Configuración de menús guardada correctamente.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    Error: <?php echo h($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="configuracion_sidebar_save.php">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">

                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Menús del sidebar</h4>
                        <p class="card-category">
                            Activa/desactiva menús y define qué privilegios pueden verlos.
                        </p>
                    </div>

                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Orden</th>
                                        <th>Menú</th>
                                        <th>URL</th>
                                        <th>Icono</th>
                                        <th>Activo</th>
                                        <th>Privilegios permitidos</th>
                                    </tr>
                                </thead>
                                <tbody>

                                <?php foreach ($menus as $menu): ?>
                                    <?php
                                        $menuId = intval($menu['id']);
                                        $allowed = explode(',', $menu['allowed_privileges']);
                                    ?>

                                    <tr>
                                        <td style="width:80px;">
                                            <input type="number"
                                                   name="sort_order_<?php echo $menuId; ?>"
                                                   class="form-control"
                                                   value="<?php echo intval($menu['sort_order']); ?>">
                                        </td>

                                        <td>
                                            <strong><?php echo h($menu['label']); ?></strong><br>
                                            <small><?php echo h($menu['menu_key']); ?></small>
                                        </td>

                                        <td>
                                            <?php echo h($menu['url']); ?>
                                        </td>

                                        <td>
                                            <i class="material-icons"><?php echo h($menu['icon']); ?></i>
                                            <?php echo h($menu['icon']); ?>
                                        </td>

                                        <td>
                                            <label>
                                                <input type="checkbox"
                                                       name="enabled_<?php echo $menuId; ?>"
                                                       value="1"
                                                       <?php echo (intval($menu['enabled']) === 1 ? 'checked' : ''); ?>>
                                                Activo
                                            </label>
                                        </td>

                                        <td>
                                            <?php foreach (getPrivilegiosPermitidos() as $priv): ?>
                                                <label style="display:block;">
                                                    <input type="checkbox"
                                                           name="privileges_<?php echo $menuId; ?>[]"
                                                           value="<?php echo intval($priv); ?>"
                                                           <?php echo (in_array(strval($priv), $allowed) ? 'checked' : ''); ?>>
                                                    <?php echo h(getPrivilegeLabel($priv)); ?>
                                                </label>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (count($menus) == 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            No hay menús configurados.
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                </tbody>
                            </table>
                        </div>

                        <button type="submit" class="btn btn-success">
                            Guardar configuración
                        </button>

                        <a href="configuracion.php" class="btn btn-default">
                            Volver
                        </a>

                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>