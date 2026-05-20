<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/privilegios.php';
require_admin();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

$pageTitle = 'Gestión de Usuarios';
$currentPage = 'usuarios';

function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$usuarios = array();

if ($buscar !== '') {
    $like = '%' . $buscar . '%';

    $stmt = $db->prepare("
        SELECT cod_usuario, oauth_uid, name, lastname, mail, cod_privilegio, created
        FROM usuarios
        WHERE name LIKE ?
           OR lastname LIKE ?
           OR mail LIKE ?
        ORDER BY created DESC
    ");
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $stmt->bind_result($cod_usuario, $oauth_uid, $name, $lastname, $mail, $cod_privilegio, $created);

    while ($stmt->fetch()) {
        $usuarios[] = array(
            'cod_usuario' => $cod_usuario,
            'oauth_uid' => $oauth_uid,
            'name' => $name,
            'lastname' => $lastname,
            'mail' => $mail,
            'cod_privilegio' => $cod_privilegio,
            'created' => $created
        );
    }

    $stmt->close();
} else {
    $result = $db->query("
        SELECT cod_usuario, oauth_uid, name, lastname, mail, cod_privilegio, created
        FROM usuarios
        ORDER BY created DESC
    ");

    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
}

$currentOauthUid = isset($_SESSION['userData']['oauth_uid']) ? $_SESSION['userData']['oauth_uid'] : '';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-panel">

    <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top">
        <div class="container-fluid">
            <div class="navbar-wrapper">
                <a class="navbar-brand" href="#">Gestión de Usuarios</a>
            </div>

            <div class="collapse navbar-collapse justify-content-end">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" id="navbarDropdownProfile" data-toggle="dropdown">
                            <i class="material-icons">person</i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item"
                                href="<?php echo rtrim($appUrl, '/'); ?>/dashboard/login/logout.php">
                                Cerrar sesión
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="content">
        <div class="container-fluid">

            <?php if (isset($_GET['ok']) && $_GET['ok'] == 'updated'): ?>
                <div class="alert alert-success">
                    Usuario actualizado correctamente.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['ok']) && $_GET['ok'] == 'deleted'): ?>
                <div class="alert alert-success">
                    Usuario eliminado correctamente.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] == 'self_block'): ?>
                <div class="alert alert-warning">
                    No puedes bloquear tu propia cuenta.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] == 'self_delete'): ?>
                <div class="alert alert-warning">
                    No puedes eliminar tu propia cuenta.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid'): ?>
                <div class="alert alert-danger">
                    Los datos enviados no son válidos.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] == 'last_admin'): ?>
                <div class="alert alert-warning">
                    No puedes quitar o eliminar al último administrador del sistema.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] == 'csrf'): ?>
                <div class="alert alert-danger">
                    La solicitud no es válida o expiró. Intenta nuevamente.
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header card-header-primary">
                    <h4 class="card-title">Usuarios registrados</h4>
                    <p class="card-category">Administra accesos y privilegios del dashboard</p>
                </div>

                <div class="card-body">

                    <form method="GET" action="usuarios.php" class="form-inline" style="margin-bottom: 20px;">
                        <div class="form-group" style="width: 320px; margin-right: 10px;">
                            <input type="text" name="buscar" class="form-control"
                                placeholder="Buscar por nombre o correo" value="<?php echo h($buscar); ?>">
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm">
                            Buscar
                        </button>

                        <?php if ($buscar !== ''): ?>
                            <a href="usuarios.php" class="btn btn-default btn-sm">
                                Limpiar
                            </a>
                        <?php endif; ?>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Privilegio actual</th>
                                    <th>Fecha creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php if (count($usuarios) == 0): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            No se encontraron usuarios.
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($usuarios as $row): ?>
                                    <tr>
                                        <td>
                                            <?php echo h($row['name'] . ' ' . $row['lastname']); ?>
                                        </td>

                                        <td>
                                            <?php echo h($row['mail']); ?>
                                        </td>

                                        <td>
                                            <?php echo h(getPrivilegeLabel($row['cod_privilegio'])); ?>
                                        </td>

                                        <td>
                                            <?php echo h($row['created']); ?>
                                        </td>

                                        <td>
                                            <form method="POST" action="usuarios_crud.php" style="display:inline-block;">
                                                <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">
                                                <input type="hidden" name="id"
                                                    value="<?php echo h($row['cod_usuario']); ?>">

                                                <select name="privilegio" class="form-control"
                                                    style="width:160px; display:inline-block;">
                                                    <?php foreach (getPrivilegiosPermitidos() as $privilegio): ?>
                                                        <option value="<?php echo intval($privilegio); ?>"
                                                            <?php echo (intval($row['cod_privilegio']) === intval($privilegio) ? 'selected' : ''); ?>>
                                                            <?php echo h(getPrivilegeLabel($privilegio)); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>

                                                <button type="submit" class="btn btn-success btn-sm">
                                                    Guardar
                                                </button>
                                            </form>

                                            <form method="POST" action="usuarios_delete.php" style="display:inline-block;">
                                                <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">
                                                <input type="hidden" name="id" value="<?php echo h($row['oauth_uid']); ?>">

                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('¿Eliminar usuario? Esta acción no se puede deshacer.')">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>