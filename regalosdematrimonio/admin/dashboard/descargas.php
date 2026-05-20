<?php

session_start();
date_default_timezone_set('America/Santiago');

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

$fecha_actual = date('Y-m-d');

$userName = isset($_SESSION['userData']['name']) ? $_SESSION['userData']['name'] : '';
$userLastname = isset($_SESSION['userData']['lastname']) ? $_SESSION['userData']['lastname'] : '';

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">

  <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../../assets/img/favicon.ico">

  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>Dashboard Portal Donaciones</title>

  <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no"
    name="viewport">

  <link rel="stylesheet" type="text/css"
    href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">

  <link href="../../assets/css/material-dashboard.css" rel="stylesheet">
</head>

<body onload="startTime()">
  <div class="wrapper">

    <?php
    $currentPage = 'descargas';
    require_once __DIR__ . '/../partials/sidebar.php';
    ?>

    <div class="main-panel">

      <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top">
        <div class="container-fluid">

          <div class="navbar-wrapper">
            <a class="navbar-brand" href="#">Portal De Donaciones | Descarga de archivos</a>
            <span id="clock" style="margin-left: 20px; font-weight: bold;"></span>
          </div>

          <button class="navbar-toggler" type="button" data-toggle="collapse" aria-controls="navigation-index"
            aria-expanded="false" aria-label="Toggle navigation">
            <span class="sr-only">Toggle navigation</span>
            <span class="navbar-toggler-icon icon-bar"></span>
            <span class="navbar-toggler-icon icon-bar"></span>
            <span class="navbar-toggler-icon icon-bar"></span>
          </button>

          <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">

              <li class="nav-item dropdown">
                <a class="nav-link" href="#" id="navbarDropdownProfile" data-toggle="dropdown" aria-haspopup="true"
                  aria-expanded="false">
                  <i class="material-icons">person</i>
                  <p class="d-lg-none d-md-block">Account</p>
                </a>

                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownProfile">
                  <a class="dropdown-item" href="#">
                    <?php echo htmlspecialchars($userName . ' ' . $userLastname, ENT_QUOTES, 'UTF-8'); ?>
                  </a>

                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="../login/logout.php">Cerrar sesión</a>
                </div>
              </li>

            </ul>
          </div>

        </div>
      </nav>

      <div class="content">
        <div class="container-fluid">

          <div class="row">

            <div class="col-lg-4 col-md-5 col-sm-5">
              <div class="card card-stats">

                <div class="card-header card-header-warning card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">vertical_align_bottom</i>
                  </div>
                  <h4 class="card-title">WebPay</h4>
                </div>

                <div class="card-body">
                  <form action="excelDonaWebPay.php" method="POST"
                    style="display: flex; flex-direction: column; align-items: flex-start;">
                    <div style="display: flex; gap: 20px;">
                      <label>
                        Fecha Desde:
                        <input type="date" name="fecha_desde" required>
                      </label>

                      <label>
                        Fecha Hasta:
                        <input type="date" name="fecha_hasta"
                          value="<?php echo htmlspecialchars($fecha_actual, ENT_QUOTES, 'UTF-8'); ?>" required>
                      </label>
                    </div>

                    <input type="submit" value="Descargar" style="margin-top: 15px;">
                  </form>
                </div>

                <div class="card-footer">
                  <div class="stats">
                    <i class="material-icons">note</i>
                    Base de datos
                  </div>
                </div>

              </div>
            </div>

            <div class="col-lg-4 col-md-5 col-sm-5">
              <div class="card card-stats">

                <div class="card-header card-header-success card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">vertical_align_bottom</i>
                  </div>
                  <h4 class="card-title">Cuota única</h4>
                </div>

                <div class="card-body">
                  <form action="excelCuotaUnica.php" method="POST"
                    style="display: flex; flex-direction: column; align-items: flex-start;">
                    <div style="display: flex; gap: 20px;">
                      <label>
                        Fecha Desde:
                        <input type="date" name="fecha_desde" required>
                      </label>

                      <label>
                        Fecha Hasta:
                        <input type="date" name="fecha_hasta"
                          value="<?php echo htmlspecialchars($fecha_actual, ENT_QUOTES, 'UTF-8'); ?>" required>
                      </label>
                    </div>

                    <input type="submit" value="Descargar" style="margin-top: 15px;">
                  </form>
                </div>

                <div class="card-footer">
                  <div class="stats">
                    <i class="material-icons">note</i>
                    Base de datos
                  </div>
                </div>

              </div>
            </div>

          </div>

        </div>
      </div>

      <footer class="footer">
        <div class="container-fluid">
          <nav class="float-left">
            <ul>
              <li>
                <a href="https://www.fundacionlasrosas.cl">
                  Fundación Las Rosas
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </footer>

    </div>
  </div>

  <script src="../../assets/js/core/jquery.min.js"></script>
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap-material-design.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>

  <script src="js/graficos.js"></script>
  <script src="js/funciones.js"></script>

  <script src="../../assets/js/material-dashboard.js" type="text/javascript"></script>
</body>

</html>