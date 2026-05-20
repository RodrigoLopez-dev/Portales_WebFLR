<?php

session_start();
date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/database.php';

if (!isset($_SESSION['userData']['cod_usuario'])) {
    header('Location: ../login/logout.php');
    exit;
}

$cod_privilegio = isset($_SESSION['userData']['cod_privilegio'])
    ? (int) $_SESSION['userData']['cod_privilegio']
    : 0;

if ($cod_privilegio === 0) {
    header('Location: ../login/restriccion.php');
    exit;
}

$captador_libre = new Database();
$listado = $captador_libre->read();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../../assets/img/favicon.ico">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Área Servicios</title>

    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no"
        name="viewport">

    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">

    <link href="../../assets/css/material-dashboard.css" rel="stylesheet">
</head>

<body>
    <div class="wrapper">

        <?php
        $currentPage = 'servicios';
        require_once __DIR__ . '/../partials/sidebar.php';
        ?>

        <div class="main-panel">

            <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <a class="navbar-brand" href="#">Área Servicios</a>
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

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">

                                <div class="card-header card-header-primary">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h4 class="card-title">Listado de Captadores libres</h4>
                                            <p class="card-category">Vista de consulta para Área Servicios</p>
                                        </div>

                                        <div class="col-md-4 text-right">
                                            <a href="export_csv.php" class="btn btn-success">
                                                <i class="material-icons">file_download</i> Descargar
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="tabla_completa" class="table table-striped table-hover">
                                            <thead class="text-primary">
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Cod POS</th>
                                                    <th>RUT</th>
                                                    <th>Nombre</th>
                                                    <th>Oficina</th>
                                                    <th>Email</th>
                                                    <th>Mes ingreso</th>
                                                    <th>Proyecto</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php if ($listado): ?>
                                                    <?php while ($row = mysqli_fetch_object($listado)): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($row->codigo, ENT_QUOTES, 'UTF-8'); ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($row->cod_POS, ENT_QUOTES, 'UTF-8'); ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($row->rut, ENT_QUOTES, 'UTF-8'); ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($row->nombre, ENT_QUOTES, 'UTF-8'); ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($row->oficina, ENT_QUOTES, 'UTF-8'); ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($row->email, ENT_QUOTES, 'UTF-8'); ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($row->mes_ingreso, ENT_QUOTES, 'UTF-8'); ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($row->proyecto, ENT_QUOTES, 'UTF-8'); ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($row->estado, ENT_QUOTES, 'UTF-8'); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
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

        </div>
    </div>

    <script src="../../assets/js/core/jquery.min.js"></script>
    <script src="../../assets/js/core/popper.min.js"></script>
    <script src="../../assets/js/core/bootstrap-material-design.min.js"></script>
    <script src="../../assets/js/plugins/jquery.dataTables.min.js"></script>
    <script src="../../assets/js/material-dashboard.js" type="text/javascript"></script>

    <script>
        $(document).ready(function () {
            $('#tabla_completa').DataTable({
                order: [[0, 'desc']],
                pageLength: 25
            });
        });
    </script>
</body>

</html>