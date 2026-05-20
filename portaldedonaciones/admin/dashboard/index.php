<?php

session_start();
date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/../../config/database.php';

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

if ($cod_privilegio === 2) {
    header('Location: descargas.php');
    exit;
}

if ($cod_privilegio === 4) {
    header('Location: ../captadorlibre/');
    exit;
}

if ($cod_privilegio === 6) {
    header('Location: ../captadorlibre/servicios.php');
    exit;
}

$c_dia = isset($_GET['c_dia']) ? (int) $_GET['c_dia'] : (int) date('d');
$c_mes = isset($_GET['c_mes']) ? (int) $_GET['c_mes'] : (int) date('m');
$c_agno = isset($_GET['c_agno']) ? (int) $_GET['c_agno'] : (int) date('Y');

$db = db_connect();
$db->set_charset('utf8');

$userName = isset($_SESSION['userData']['name']) ? $_SESSION['userData']['name'] : '';
$userLastname = isset($_SESSION['userData']['lastname']) ? $_SESSION['userData']['lastname'] : '';

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta http-equiv="refresh" content="300">
  <meta charset="utf-8">
  <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../../assets/img/favicon.ico">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title>Dashboard Portal Donaciones</title>

  <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no" name="viewport">

  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">

  <link href="../../assets/css/material-dashboard.css" rel="stylesheet">

  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.js"></script>
</head>

<body onload="startTime()">
  <div class="wrapper">

    <?php
    $currentPage = 'dashboard';
    require_once __DIR__ . '/../partials/sidebar.php';
    ?>

    <div class="main-panel">
      <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top">
        <div class="container-fluid">
          <div class="navbar-wrapper">
            <a class="navbar-brand" href="#">Portal De Donaciones | WebPay</a>
          </div>

          <button class="navbar-toggler" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
            <span class="sr-only">Toggle navigation</span>
            <span class="navbar-toggler-icon icon-bar"></span>
            <span class="navbar-toggler-icon icon-bar"></span>
            <span class="navbar-toggler-icon icon-bar"></span>
          </button>

          <div class="collapse navbar-collapse justify-content-end">
            <form class="navbar-form" method="get">
              <div class="input-group no-border">

                <select id="c_dia" name="c_dia" class="form-control">
                  <?php
                  for ($dia = 1; $dia <= 31; $dia++) {
                      $selected = ($c_dia === $dia) ? 'selected' : '';
                      echo '<option value="' . $dia . '" ' . $selected . '>Día : ' . $dia . '</option>';
                  }
                  ?>
                </select>

                <select id="c_mes" name="c_mes" class="form-control">
                  <?php
                  $queryMes = $db->query("SELECT DISTINCT DATE_FORMAT(fecha,'%m') AS mes FROM donaciones_online ORDER BY mes DESC");

                  if ($queryMes) {
                      while ($rowMes = $queryMes->fetch_assoc()) {
                          $mes = (int) $rowMes['mes'];
                          $selected = ($c_mes === $mes) ? 'selected' : '';
                          echo '<option value="' . $mes . '" ' . $selected . '>Mes : ' . $mes . '</option>';
                      }
                  }
                  ?>
                </select>

                <select id="c_agno" name="c_agno" class="form-control">
                  <?php
                  $queryAgno = $db->query("SELECT DISTINCT DATE_FORMAT(fecha,'%Y') AS agno FROM donaciones_online ORDER BY agno DESC");

                  if ($queryAgno) {
                      while ($rowAgno = $queryAgno->fetch_assoc()) {
                          $agno = (int) $rowAgno['agno'];
                          $selected = ($c_agno === $agno) ? 'selected' : '';
                          echo '<option value="' . $agno . '" ' . $selected . '>Año : ' . $agno . '</option>';
                      }
                  }
                  ?>
                </select>

                <button type="submit" class="btn btn-white btn-round btn-just-icon">
                  <i class="material-icons">search</i>
                  <div class="ripple-container"></div>
                </button>

              </div>
            </form>

            <ul class="navbar-nav">
              <li class="nav-item dropdown">
                <a class="nav-link" href="#" id="navbarDropdownProfile" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
            <div class="col-lg-3 col-md-6 col-sm-6">
              <div class="card card-stats">
                <div class="card-header card-header-warning card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">trending_up</i>
                  </div>
                  <p class="card-category">Total mes</p>
                  <h4 class="card-title"><div id="totalmes"></div></h4>
                </div>
                <div class="card-footer">
                  <div class="stats">
                    <i class="material-icons">date_range</i>
                    Mes : <div id="mes"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6">
              <div class="card card-stats">
                <div class="card-header card-header-info card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">attach_money</i>
                  </div>
                  <p class="card-category">Total día</p>
                  <h5 class="card-title"><div id="donaciones_dia"></div></h5>
                </div>
                <div class="card-footer">
                  <div class="stats">
                    <i class="material-icons">date_range</i>
                    <div id="dato_fecha"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6">
              <div class="card card-stats">
                <div class="card-header card-header-success card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">perm_identity</i>
                  </div>
                  <p class="card-category">Donantes</p>
                  <h4 class="card-title"><div id="cant_donaciones"></div></h4>
                </div>
                <div class="card-footer">
                  <div class="stats">
                    <i class="material-icons">date_range</i>
                    Mes : <div id="cant_mes"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6">
              <div class="card card-stats">
                <div class="card-header card-header-danger card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">date_range</i>
                  </div>
                  <p class="card-category">Fecha de hoy</p>
                  <h6 class="card-title">
                    <?php echo date('d-m-Y'); ?>
                    <div id="clockdate">
                      <div class="clockdate-wrapper">
                        <div id="clock"></div>
                        <div id="date"></div>
                      </div>
                    </div>
                  </h6>
                </div>
                <div class="card-footer">
                  <div class="stats">
                    <i class="material-icons">explore</i> Santiago de Chile
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">

            <div class="col-md-6">
              <div class="card card-chart">
                <div class="card-header card-header-info">
                  <canvas id="graphCanvasDiario"></canvas>
                </div>
                <div class="card-body">
                  <h4 class="card-title">Donaciones en el mes</h4>
                  <div id="dato_mes" class="card-category"></div>
                </div>
                <div class="card-footer">
                  <div class="stats">
                    <i class="material-icons">access_time</i> última actualización <?php echo date('G:i'); ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card card-chart">
                <div class="card-header card-header-warning">
                  <canvas id="graphCanvasDiaHora"></canvas>
                </div>
                <div class="card-body">
                  <h4 class="card-title">Por hora y fuente</h4>
                  <div id="dato_fecha_diaHora" class="card-category"></div>
                </div>
                <div class="card-footer">
                  <div class="stats">
                    <i class="material-icons">access_time</i> última actualización <?php echo date('G:i'); ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card card-chart">
                <div class="card-header card-header-danger">
                  <canvas id="graphCanvasDonantes"></canvas>
                </div>
                <div class="card-body">
                  <h4 class="card-title">Donantes por día</h4>
                  <div id="dato_mes_donante" class="card-category"></div>
                </div>
                <div class="card-footer">
                  <div class="stats">
                    <i class="material-icons">access_time</i> última actualización <?php echo date('G:i'); ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card card-chart">
                <div class="card-header card-header-primary">
                  <canvas id="graphCanvasRegion"></canvas>
                </div>
                <div class="card-body">
                  <h4 class="card-title">Regiones</h4>
                  <div id="dato_mes_region" class="card-category"></div>
                </div>
                <div class="card-footer">
                  <div class="stats">
                    <i class="material-icons">access_time</i> última actualización <?php echo date('G:i'); ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card card-chart">
                <div class="card-header card-header-success">
                  <canvas id="graphCanvasCampana"></canvas>
                </div>
                <div class="card-body">
                  <h4 class="card-title">Campañas</h4>
                  <div id="dato_mes_campana" class="card-category"></div>
                </div>
                <div class="card-footer">
                  <div class="stats">
                    <i class="material-icons">access_time</i> última actualización <?php echo date('G:i'); ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card card-chart">
                <div class="card-header card-header-warning">
                  <canvas id="graphCanvasFuentes"></canvas>
                </div>
                <div class="card-body">
                  <h4 class="card-title">Fuentes</h4>
                  <div id="dato_mes_fuente" class="card-category"></div>
                </div>
                <div class="card-footer">
                  <div class="stats">
                    <i class="material-icons">access_time</i> última actualización <?php echo date('G:i'); ?>
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
                <a href="https://www.fundacionlasrosas.cl">Fundación Las Rosas</a>
              </li>
            </ul>
          </nav>

          <input id="e_dia" name="e_dia" type="hidden" value="<?php echo htmlspecialchars($c_dia, ENT_QUOTES, 'UTF-8'); ?>">
          <input id="e_mes" name="e_mes" type="hidden" value="<?php echo htmlspecialchars($c_mes, ENT_QUOTES, 'UTF-8'); ?>">
          <input id="e_agno" name="e_agno" type="hidden" value="<?php echo htmlspecialchars($c_agno, ENT_QUOTES, 'UTF-8'); ?>">
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

  <script>
    $(document).ready(function () {
      showGraphDiario($("#e_mes").val(), $("#e_agno").val());
      showGraphDiaHora($("#e_dia").val(), $("#e_mes").val(), $("#e_agno").val());
      showGraphCampanas($("#e_mes").val(), $("#e_agno").val());
      showGraphFuentes($("#e_mes").val(), $("#e_agno").val());
      showGraphRegiones($("#e_mes").val(), $("#e_agno").val());
      showGraphDonantes($("#e_mes").val(), $("#e_agno").val());
    });
  </script>
</body>

</html>