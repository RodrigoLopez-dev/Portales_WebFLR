<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/database.php';

captador_require_captadores();

$errors = array();

$codigo = '';
$cod_POS = '';
$rut = '';
$nombre = '';
$oficina = '';
$email = '';
$mes_ingreso = '';
$proyecto = '';
$estado = '';

$oficinasPermitidas = array(
    'Santiago',
    'La Serena',
    'Viña del Mar',
    'Talca',
    'Concepcion',
    'Osorno',
    'Valdivia',
    'Temuco',
    'Agencia Externa',
    'Telemarketing'
);

$proyectosPermitidos = array(
    'P1 - Reemplazo del Equipo',
    'P2 - Aumento Dotacion',
    'P3 - Temuco'
);

$estadosPermitidos = array(
    'Trabajando',
    'Licencia Médica',
    'Desvinculado'
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    captador_validate_csrf();

    $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
    $cod_POS = isset($_POST['cod_POS']) ? trim($_POST['cod_POS']) : '';
    $rut = isset($_POST['rut']) ? trim($_POST['rut']) : '';
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $oficina = isset($_POST['oficina']) ? trim($_POST['oficina']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mes_ingreso = isset($_POST['mes_ingreso']) ? trim($_POST['mes_ingreso']) : '';
    $proyecto = isset($_POST['proyecto']) ? trim($_POST['proyecto']) : '';
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';

    if ($codigo === '') {
        $errors[] = 'El código es obligatorio.';
    }

    if ($cod_POS === '') {
        $errors[] = 'El código POS es obligatorio.';
    }

    if ($rut === '') {
        $errors[] = 'El RUT es obligatorio.';
    }

    if (strlen($rut) > 11) {
        $errors[] = 'El RUT no puede superar los 11 caracteres.';
    }

    if ($nombre === '') {
        $errors[] = 'El nombre es obligatorio.';
    }

    if ($oficina === '') {
        $errors[] = 'La oficina es obligatoria.';
    }

    if ($proyecto === '') {
        $errors[] = 'El proyecto es obligatorio.';
    }

    if ($estado === '') {
        $errors[] = 'El estado es obligatorio.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email ingresado no es válido.';
    }

    if ($oficina !== '' && !in_array($oficina, $oficinasPermitidas, true)) {
        $errors[] = 'La oficina seleccionada no es válida.';
    }

    if ($proyecto !== '' && !in_array($proyecto, $proyectosPermitidos, true)) {
        $errors[] = 'El proyecto seleccionado no es válido.';
    }

    if ($estado !== '' && !in_array($estado, $estadosPermitidos, true)) {
        $errors[] = 'El estado seleccionado no es válido.';
    }

    if (count($errors) === 0) {
        $database = new Database();

        if ($database->rut_exists($rut)) {
            $errors[] = 'Ya existe un captador registrado con ese RUT.';
        } else {
            $res = $database->create(
                $codigo,
                $cod_POS,
                $rut,
                $nombre,
                $oficina,
                $email,
                $mes_ingreso,
                $proyecto,
                $estado
            );

            if ($res) {
                header('Location: index.php?mensaje=creado');
                exit;
            }

            $errors[] = 'No fue posible crear el captador.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Agregar captador libre</title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="../../assets/css0/all.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <link href="../../assets/css/material-dashboard.css" rel="stylesheet">
    <link href="../../assets/css/custom.css" rel="stylesheet">
</head>

<body>
    <div class="wrapper">

        <?php
        $currentPage = 'captadorlibre';
        require_once __DIR__ . '/../partials/sidebar.php';
        ?>

        <div class="main-panel">

            <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <a class="navbar-brand" href="#">Agregar captador libre</a>
                    </div>

                    <div class="collapse navbar-collapse justify-content-end">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="../login/logout.php">
                                    <i class="material-icons">logout</i>
                                    Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="content">
                <div class="container-fluid">

                    <?php if (count($errors) > 0): ?>
                        <div class="alert alert-danger">
                            <ul style="margin-bottom: 0;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo h($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">

                                <div class="card-header card-header-primary">
                                    <h4 class="card-title">Nuevo captador libre</h4>
                                    <p class="card-category">Complete los datos del captador.</p>
                                </div>

                                <div class="card-body">
                                    <form method="POST" action="create.php">
                                        <input type="hidden" name="csrf_token"
                                            value="<?php echo h(captador_csrf_token()); ?>">

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Código *</label>
                                                    <input type="text" name="codigo" class="form-control"
                                                        value="<?php echo h($codigo); ?>" required>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Cod POS</label>
                                                    <input type="text" name="cod_POS" class="form-control"
                                                        value="<?php echo h($cod_POS); ?>" required>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>RUT *</label>
                                                    <input type="text" name="rut" class="form-control"
                                                        value="<?php echo h($rut); ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label>Nombre *</label>
                                                    <input type="text" name="nombre" class="form-control"
                                                        value="<?php echo h($nombre); ?>" required>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Oficina *</label>
                                                    <select class="form-control" name="oficina" id="oficina" required>
                                                        <option value="">Elige</option>
                                                        <?php foreach ($oficinasPermitidas as $opcion): ?>
                                                            <option value="<?php echo h($opcion); ?>" <?php echo ($oficina === $opcion) ? 'selected' : ''; ?>>
                                                                <?php echo h($opcion); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input type="email" name="email" class="form-control"
                                                        value="<?php echo h($email); ?>">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Mes ingreso</label>
                                                    <input type="text" name="mes_ingreso" class="form-control"
                                                        value="<?php echo h($mes_ingreso); ?>">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Proyecto *</label>
                                                    <select class="form-control" name="proyecto" id="proyecto" required>
                                                        <option value="">Elige</option>
                                                        <?php foreach ($proyectosPermitidos as $opcion): ?>
                                                            <option value="<?php echo h($opcion); ?>" <?php echo ($proyecto === $opcion) ? 'selected' : ''; ?>>
                                                                <?php
                                                                if ($opcion === 'P1 - Reemplazo del Equipo') {
                                                                    echo 'Proyecto 1';
                                                                } elseif ($opcion === 'P2 - Aumento Dotacion') {
                                                                    echo 'Proyecto 2';
                                                                } elseif ($opcion === 'P3 - Temuco') {
                                                                    echo 'Proyecto 3';
                                                                } else {
                                                                    echo h($opcion);
                                                                }
                                                                ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Estado *</label>
                                                    <select class="form-control" name="estado" id="estado" required>
                                                        <option value="">Elige</option>
                                                        <?php foreach ($estadosPermitidos as $opcion): ?>
                                                            <option value="<?php echo h($opcion); ?>" <?php echo ($estado === $opcion) ? 'selected' : ''; ?>>
                                                                <?php echo h($opcion); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <a href="index.php" class="btn btn-secondary">
                                            Volver
                                        </a>

                                        <button type="submit" class="btn btn-primary">
                                            Guardar
                                        </button>
                                    </form>
                                </div>

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
    <script src="../../assets/js/material-dashboard.js" type="text/javascript"></script>
</body>

</html>