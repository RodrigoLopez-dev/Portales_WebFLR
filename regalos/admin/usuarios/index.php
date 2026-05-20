<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../config/database.php';

usuarios_require_admin();

$db = db_connect();
$db->set_charset('utf8');

$currentPage = 'usuarios';

$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "
    SELECT 
        cod_usuario,
        oauth_provider,
        oauth_uid,
        name,
        lastname,
        mail,
        picture,
        created,
        cod_privilegio
    FROM usuarios
";

$params = array();
$types = '';

if ($busqueda !== '') {
    $sql .= "
        WHERE 
            name LIKE ?
            OR lastname LIKE ?
            OR mail LIKE ?
    ";

    $like = '%' . $busqueda . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types = 'sss';
}

$sql .= " ORDER BY created DESC";

if ($busqueda !== '') {
    $stmt = $db->prepare($sql);

    if (!$stmt) {
        die('Error preparando consulta de usuarios.');
    }

    $stmt->bind_param($types, $params[0], $params[1], $params[2]);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $db->query($sql);

    if (!$result) {
        die('Error consultando usuarios.');
    }
}

$privilegios = usuarios_privilegios();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../../assets/img/favicon.ico">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Gestión de usuarios</title>

    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no"
        name="viewport">

    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">

    <link href="../../assets/css/material-dashboard.css" rel="stylesheet">

    <style>
        .usuarios-card-header {
            background: linear-gradient(60deg, #ab47bc, #8e24aa);
            color: #fff;
            padding: 20px;
            border-radius: 3px;
            margin: -20px 15px 20px;
            box-shadow: 0 4px 20px 0 rgba(0, 0, 0, .14), 0 7px 10px -5px rgba(156, 39, 176, .4);
        }

        .usuarios-card-header h4 {
            margin: 0;
            color: #fff;
        }

        .usuarios-card-header p {
            margin: 8px 0 0;
            color: rgba(255, 255, 255, .8);
        }

        .usuarios-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .usuarios-actions select {
            min-width: 190px;
        }

        .usuarios-table td {
            vertical-align: middle;
        }

        .usuario-picture {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 8px;
            object-fit: cover;
        }

        .form-inline-usuario {
            display: inline-block;
            margin: 0;
        }

        .action-buttons {
            display: flex;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-panel">
            <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <a class="navbar-brand" href="#">Gestión de usuarios</a>
                    </div>

                    <div class="collapse navbar-collapse justify-content-end">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="../login/logout.php">
                                    <i class="material-icons">logout</i>
                                    <p class="d-lg-none d-md-block">Cerrar sesión</p>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="content">
                <div class="container-fluid">

                    <?php if ($mensaje === 'actualizado'): ?>
                        <div class="alert alert-success">
                            Privilegio actualizado correctamente.
                        </div>
                    <?php endif; ?>

                    <?php if ($mensaje === 'eliminado'): ?>
                        <div class="alert alert-success">
                            Usuario eliminado correctamente.
                        </div>
                    <?php endif; ?>

                    <?php if ($mensaje === 'csrf_invalido'): ?>
                        <div class="alert alert-danger">
                            La solicitud no es válida. Intenta nuevamente.
                        </div>
                    <?php endif; ?>

                    <?php if ($mensaje === 'no_auto_degradar'): ?>
                        <div class="alert alert-warning">
                            No puedes quitarte a ti mismo el privilegio de administrador.
                        </div>
                    <?php endif; ?>

                    <?php if ($mensaje === 'no_auto_eliminar'): ?>
                        <div class="alert alert-warning">
                            No puedes eliminar tu propio usuario.
                        </div>
                    <?php endif; ?>

                    <?php if ($mensaje === 'minimo_un_admin'): ?>
                        <div class="alert alert-warning">
                            Debe existir al menos un usuario administrador en el sistema.
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="usuarios-card-header">
                            <h4>Usuarios registrados</h4>
                            <p>Administra accesos y privilegios del dashboard</p>
                        </div>

                        <div class="card-body">

                            <form method="get" action="index.php" class="mb-4">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="text" name="q" class="form-control"
                                                placeholder="Buscar por nombre o correo"
                                                value="<?php echo h($busqueda); ?>">
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">
                                            Buscar
                                        </button>
                                    </div>

                                    <?php if ($busqueda !== ''): ?>
                                        <div class="col-md-2">
                                            <a href="index.php" class="btn btn-default">
                                                Limpiar
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table usuarios-table">
                                    <thead class="text-primary">
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Correo</th>
                                            <th>Privilegio actual</th>
                                            <th>Fecha creación</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php while ($usuario = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($usuario['picture'])): ?>
                                                        <img src="<?php echo h($usuario['picture']); ?>" alt="Foto"
                                                            class="usuario-picture">
                                                    <?php endif; ?>

                                                    <?php echo h(trim($usuario['name'] . ' ' . $usuario['lastname'])); ?>
                                                </td>

                                                <td><?php echo h($usuario['mail']); ?></td>

                                                <td>
                                                    <?php echo h(usuarios_nombre_privilegio($usuario['cod_privilegio'])); ?>
                                                </td>

                                                <td><?php echo h($usuario['created']); ?></td>

                                                <td class="action-buttons">
                                                    <form method="post" action="actualizar.php" class="form-inline-usuario">
                                                        <input type="hidden" name="csrf_token"
                                                            value="<?php echo h(usuarios_csrf_token()); ?>">
                                                        <input type="hidden" name="accion" value="actualizar_privilegio">
                                                        <input type="hidden" name="cod_usuario"
                                                            value="<?php echo h($usuario['cod_usuario']); ?>">

                                                        <div class="usuarios-actions">
                                                            <select name="cod_privilegio" class="form-control">
                                                                <?php foreach ($privilegios as $codigo => $nombre): ?>
                                                                    <option value="<?php echo h($codigo); ?>" <?php echo ((int) $usuario['cod_privilegio'] === (int) $codigo) ? 'selected' : ''; ?>>
                                                                        <?php echo h($nombre); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>

                                                            <button type="submit" class="btn btn-success btn-sm">
                                                                Guardar
                                                            </button>
                                                        </div>
                                                    </form>

                                                    <form method="post" action="actualizar.php" class="form-inline-usuario"
                                                        onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
                                                        <input type="hidden" name="csrf_token"
                                                            value="<?php echo h(usuarios_csrf_token()); ?>">
                                                        <input type="hidden" name="accion" value="eliminar">
                                                        <input type="hidden" name="cod_usuario"
                                                            value="<?php echo h($usuario['cod_usuario']); ?>">

                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>

                                        <?php if ($result->num_rows === 0): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">
                                                    No se encontraron usuarios.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script src="../../assets/js/core/jquery.min.js"></script>
    <script src="../../assets/js/core/popper.min.js"></script>
    <script src="../../assets/js/core/bootstrap-material-design.min.js"></script>
    <script src="../../assets/js/material-dashboard.js"></script>
</body>

</html>