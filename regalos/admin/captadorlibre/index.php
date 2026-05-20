<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/database.php';

captador_require_admin();

$captador_libre = new Database();
$listado = $captador_libre->read();
$ultima_actualizacion = $captador_libre->get_last_update();
$mensajes = array(
    'creado' => 'Captador creado correctamente.',
    'actualizado' => 'Captador actualizado correctamente.',
    'eliminado' => 'Captador eliminado correctamente.',
    'error' => 'Ocurrió un error al procesar la solicitud.',
    'datos_incompletos' => 'Debe completar los campos obligatorios.',
    'email_invalido' => 'El email ingresado no es válido.',
    'rut_invalido' => 'El RUT informado no es válido.',
    'no_encontrado' => 'No se encontró el captador solicitado.',
    'metodo_invalido' => 'Método de solicitud no permitido.',
    'csrf_invalido' => 'La solicitud no es válida o expiró. Intente nuevamente.'
);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Mantenedor captadores libre</title>

    <link href="../../assets/css0/all.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <!--     <link href="../../assets/css0/sb-admin-2.min.css" rel="stylesheet">
    <link href="../../assets/css0/dataTables.bootstrap.min.css" rel="stylesheet"> -->
    <link href="../../assets/css/material-dashboard.css" rel="stylesheet">
    <link href="../../assets/css/custom.css" rel="stylesheet">

    <script src="../../assets/js/jquery-3.3.1.js"></script>
    <script src="../../assets/js/funciones/enigma.js"></script>
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
                        <a class="navbar-brand" href="#">Captadores libres</a>
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

                    <?php if (isset($_GET['mensaje'])): ?>
                        <?php
                        $mensajeKey = $_GET['mensaje'];
                        $mensajeTexto = isset($mensajes[$mensajeKey])
                            ? $mensajes[$mensajeKey]
                            : 'Mensaje no reconocido.';
                        ?>
                        <div class="alert alert-info">
                            <?php echo h($mensajeTexto); ?>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">

                                <div class="card-header card-header-primary">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h4 class="card-title">Listado de Captadores libres</h4>
                                            <p class="card-category">
                                                Última actualización:
                                                <?php echo htmlspecialchars($ultima_actualizacion, ENT_QUOTES, 'UTF-8'); ?>
                                            </p>
                                        </div>

                                        <div class="col-md-4 text-right">
                                            <a href="create.php" class="btn btn-info">
                                                <i class="material-icons">add</i> Agregar
                                            </a>

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
                                                    <th>Acciones</th>
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
                                                            <td>
                                                                <a href="update.php?rut=<?php echo urlencode($row->rut); ?>"
                                                                    class="btn btn-sm btn-warning">
                                                                    <i class="material-icons">edit</i>
                                                                </a>
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