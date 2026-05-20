<?php
require_once __DIR__ . '/../includes/auth.php';

$c_dia = isset($_GET["c_dia"]) ? $_GET["c_dia"] : date("d");
$c_mes = isset($_GET["c_mes"]) ? $_GET["c_mes"] : date("m");
$c_agno = isset($_GET["c_agno"]) ? $_GET["c_agno"] : date("Y");

$pageTitle = 'Dashboard Colecta';
$currentPage = 'dashboard';

$appBaseUrl = getenv('APP_BASE_URL');
$appName = getenv('APP_NAME');

if (!$appBaseUrl) {
  die('Falta APP_BASE_URL en .env');
}

if (!$appName) {
  die('Falta APP_NAME en .env');
}

$appUrl = rtrim($appBaseUrl, '/') . '/' . trim($appName, '/');

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
        <a class="navbar-brand" href="#">Donaciones digitales </a>
      </div>
      <button class="navbar-toggler" type="button" data-toggle="collapse" aria-controls="navigation-index"
        aria-expanded="false" aria-label="Toggle navigation">
        <span class="sr-only">Toggle navigation</span>
        <span class="navbar-toggler-icon icon-bar"></span>
        <span class="navbar-toggler-icon icon-bar"></span>
        <span class="navbar-toggler-icon icon-bar"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end">
        <form class="navbar-form">
          <div class="input-group no-border">
            <select id='c_dia' name='c_dia' class='form-control'>
              <?php
              $filter1 = (isset($_GET['c_dia']) ? strtolower($_GET['c_dia']) : date('d'));
              $sel1 = "";
              $valores1 = 1;
              //date('d');
              while ($valores1 < 32) {
                if ($filter1 == $valores1) {
                  $sel1 = "selected";
                }
                ;
                echo '<option value="' . $valores1 . '"' . $sel1 . '> Día : ' . $valores1 . ' &nbsp;&nbsp;&nbsp;</option>';
                $valores1++;
                $sel1 = "";
              }
              ?>
            </select>
            <select id='c_mes' name='c_mes' class='form-control'>
              <?php
              $filter = (isset($_GET['c_mes']) ? strtolower($_GET['c_mes']) : date('m'));
              $query = $db->query("SELECT DISTINCT DATE_FORMAT(fecha,'%m') mes FROM donaciones order by mes DESC");
              $sel = "";
              while ($valores = mysqli_fetch_array($query)) {
                if ($filter == $valores['mes']) {
                  $sel = "selected";
                }
                ;
                echo '<option value="' . $valores['mes'] . '"' . $sel . '> Mes : ' . $valores['mes'] . '&nbsp;&nbsp;&nbsp;</option>';
                $sel = "";
              }
              ?>
            </select>
            <select id='c_agno' name='c_agno' class='form-control'>
              <?php
              $filter2 = (isset($_GET['c_agno']) ? strtolower($_GET['c_agno']) : date('Y'));
              $query = $db->query("SELECT DISTINCT DATE_FORMAT(fecha,'%Y') agno FROM donaciones order by agno DESC");
              $sel = "";
              while ($valores = mysqli_fetch_array($query)) {
                if ($filter2 == $valores['agno']) {
                  $sel = "selected";
                }
                ;
                echo '<option value="' . $valores['agno'] . '"' . $sel . '> Año : ' . $valores['agno'] . ' &nbsp;&nbsp;&nbsp;</option>';
                $sel = "";
              }
              ?>
            </select><br><br>
            <button type="submit" class="btn btn-white btn-round btn-just-icon">
              <i class="material-icons">search</i>
              <div class="ripple-container"></div>
            </button>
          </div>
        </form>
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
              <a class="dropdown-item" href="<?php echo rtrim($appUrl, '/'); ?>/dashboard/login/logout.php">Cerrar
                sesión</a>
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
              <h6 class="card-title">
                <div id='totalwebpay'></div>
              </h6>
              <h6 class="card-title">
                <div id='totalmach'></div>
              </h6>
              <h6 class="card-title">
                <div id='totalfintoc'></div>
              </h6>
              <!-- <h6 class="card-title"><div id='totalpaypal'></div></h6>
                        <h6 class="card-title"><div id='totalbancoestado'></div></h6>
                        <h6 class="card-title"><div id='totaltransferencias'></div></h6>
                        <h6 class="card-title"><div id='cuotaunica'></div></h6>
                        <h6 class="card-title"><div id='total'></div></h6>-->
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
              <p class="card-category">Total de donaciones</p>
              <h4 class="card-title"><b>
                  <div id='cantidadgeneral'></div>
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
      <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
          <div class="card card-stats">
            <div class="card-header card-header-warning card-header-icon">
              <div class="card-icon">
                <i class="material-icons">trending_up</i>
              </div>
              <p class="card-category">Total mes</p>
              <h4 class="card-title">
                <div id='totalmes'></div>
              </h4>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">date_range</i>
                Mes : <div id='mes'></div>
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
              <h5 class="card-title">
                <div id='donaciones_dia'></div>
              </h5>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">date_range</i>
                <div id='dato_fecha'></div>
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
              <p class="card-category">Donaciones</p>
              <h4 class="card-title">
                <div id='cant_donaciones'></div>
              </h4>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">date_range</i>
                Mes : <div id='cant_mes'></div>
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
                <?php echo date("d") . "-" . date("m") . "-" . date("Y") ?>
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
                <i class="material-icons">explore</i> Santiago de chile
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
              <p class="card-category">
              <div id='dato_mes'></div>
              </p>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">access_time</i>última actualización <?php echo date("G:i"); ?>
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
              <h4 class="card-title">Por hora</h4>
              <p class="card-category">
              <div id='dato_fecha_diaHora'></div>
              </p>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">access_time</i>última actualización <?php echo date("G:i"); ?>
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
              <p class="card-category">
              <div id='dato_mes_donante'></div>
              </p>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">access_time</i>última actualización <?php echo date("G:i"); ?>
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
              <p class="card-category">
              <div id='dato_mes_region'></div>
              </p>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">access_time</i>última actualización <?php echo date("G:i"); ?>
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
              <p class="card-category">
              <div id='dato_mes_campana'></div>
              </p>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">access_time</i>última actualización <?php echo date("G:i"); ?>
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
              <p class="card-category">
              <div id='dato_mes_fuente'></div>
              </p>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">access_time</i>última actualización <?php echo date("G:i"); ?>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card card-chart">
            <div class="card-header card-header-warning">
              <canvas id="graphCanvasHogares"></canvas>
            </div>
            <div class="card-body">
              <h4 class="card-title">Alcancias Hogares</h4>
              <p class="card-category">
              <div id='alcanciasHogares'></div>
              </p>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">access_time</i>última actualización <?php echo date("G:i"); ?>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card card-chart">
            <div class="card-header card-header-success">
              <canvas id="graphCanvasEmpresas"></canvas>
            </div>
            <div class="card-body">
              <h4 class="card-title">Alcancias Empresas</h4>
              <p class="card-category">
              <div id='alcanciasEmpresas'></div>
              </p>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">access_time</i>última actualización <?php echo date("G:i"); ?>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card card-chart">
            <div class="card-body">
              <h4 class="card-title">Alcancias Direcciones</h4>
              <p class="card-category">
              <div id='alcanciasDireccion3'></div>
              </p>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>

<script src="' . asset('dashboard/dashboard/js/graficos.js') . '"></script>
<script src="' . asset('dashboard/dashboard/js/funciones.js') . '"></script>
<script>
$(document).ready(function () {
  showGraphDiario($("#e_mes").val(), $("#e_agno").val());
  showGraphDiaHora($("#e_dia").val(), $("#e_mes").val(), $("#e_agno").val());
  showGraphCampanas($("#e_mes").val(), $("#e_agno").val());
  showGraphFuentes($("#e_dia").val(), $("#e_mes").val(), $("#e_agno").val());
  showGraphRegiones($("#e_dia").val(), $("#e_mes").val(), $("#e_agno").val());
  showGraphDonantes($("#e_mes").val(), $("#e_agno").val());
  showGraphHogares();
  showGraphEmpresas();
  showGraphDireccion3();
});
</script>
';

require_once __DIR__ . '/../includes/footer.php';
?>