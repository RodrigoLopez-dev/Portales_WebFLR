<?php
require_once __DIR__ . '/../includes/auth.php';

$c_dia = isset($_GET["c_dia"]) ? $_GET["c_dia"] : date("d");
$c_mes = isset($_GET["c_mes"]) ? $_GET["c_mes"] : date("m");
$c_agno = isset($_GET["c_agno"]) ? $_GET["c_agno"] : date("Y");

$pageTitle = 'Dashboard Rifa';
$currentPage = 'rifa';

$extraHead = '
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.js"></script>
';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-panel">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top ">
    <div class="container-fluid">
      <div class="navbar-wrapper">
        <a class="navbar-brand" href="#">Compradores rifa</a>
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
            <a class="nav-link" href="#pablo" id="navbarDropdownProfile" data-toggle="dropdown" aria-haspopup="true"
              aria-expanded="false">
              <i class="material-icons">person</i>
              <p class="d-lg-none d-md-block">
                Account
              </p>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownProfile">
              <a class="dropdown-item" href="#">Prueba</a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="../login/logout.php">Cerrar sesión</a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- End Navbar -->
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-6">
          <div class="card card-stats">
            <div class="card-header card-header card-header-icon">
              <div class="card-icon">
                <i class="material-icons">attach_money</i>
              </div>
              <p class="card-category">Total recaudado rifa</p>
              <h4 class="card-title"><b>
                  <div id='totalrifa'></div>
                </b></h4>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">access_time</i>última actualización <?php echo date("G:i"); ?>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
          <div class="card card-stats">
            <div class="card-header card-header-success card-header-icon">
              <div class="card-icon">
                <i class="material-icons">supervisor_account</i>
              </div>
              <p class="card-category">Cantidad compradores</p>
              <h4 class="card-title"><b>
                  <div id='compradores'></div>
                </b></h4>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">access_time</i>última actualización <?php echo date("G:i"); ?>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
          <div class="card card-stats">
            <div class="card-header card-header-warning card-header-icon">
              <div class="card-icon">
                <i class="material-icons">trending_up</i>
              </div>
              <p class="card-category">Cantidad de números</p>
              <h4 class="card-title"><b>
                  <div id='numeros'></div>
                </b></h4>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">access_time</i>última actualización <?php echo date("G:i"); ?>
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
            Desarrollado por Innovación FLR
          </li>
        </ul>
      </nav>
      <input id="e_dia" name="e_dia" type="text" value="<?php echo $c_dia ?>" hidden>
      <input id="e_mes" name="e_mes" type="text" value="<?php echo $c_mes ?>" hidden>
      <input id="e_agno" name="e_agno" type="text" value="<?php echo $c_agno ?>" hidden>
    </div>
    <span id="siteseal">
      <script async type="text/javascript"
        src="https://seal.godaddy.com/getSeal?sealID=6bNd3wIinRB874kLvceF4JNjwsMbj9tVdHm35pFXMiRT5cWqWAL55BpW3AWH"></script>
    </span>
  </footer>
</div>

<?php
$extraScripts = '
<script src="' . asset('dashboard/dashboard/js/graficos.js') . '"></script>
<script src="' . asset('dashboard/dashboard/js/funciones.js') . '"></script>
<script>
$(document).ready(function () {
    traeRifa();
});
</script>
';

require_once __DIR__ . '/../includes/footer.php';
?>