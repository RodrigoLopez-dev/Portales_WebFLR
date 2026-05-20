<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pageTitle = 'Configuración';
$currentPage = 'configuracion';

function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-panel">
    <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top">
        <div class="container-fluid">
            <div class="navbar-wrapper">
                <a class="navbar-brand" href="#">Configuración</a>
            </div>
        </div>
    </nav>

    <div class="content">
        <div class="container-fluid">

            <h3>Configuración</h3>

            <div class="row">

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header card-header-primary">
                            <h4 class="card-title">Inicio</h4>
                            <p class="card-category">Imagen principal y botones del portal</p>
                        </div>
                        <div class="card-body">
                            <a href="configuracion_inicio.php" class="btn btn-primary">
                                Configurar inicio
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header card-header-primary">
                            <h4 class="card-title">Sidebar / Menús</h4>
                            <p class="card-category">Visibilidad y permisos de los menús</p>
                        </div>
                        <div class="card-body">
                            <a href="configuracion_sidebar.php" class="btn btn-primary">
                                Configurar sidebar
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>