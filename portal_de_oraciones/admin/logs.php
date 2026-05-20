<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_admin.php';

require_login();

$currentPage = 'logs';
$pdo = admin_db();

/**
 * Helpers
 */
function qs_merge($overrides = array())
{
  $params = $_GET;

  foreach ($overrides as $key => $value) {
    if ($value === null) {
      unset($params[$key]);
    } else {
      $params[$key] = $value;
    }
  }

  return '?' . http_build_query($params);
}

function clamp_page($value)
{
  $page = (int)$value;
  return $page > 0 ? $page : 1;
}

$perPage = 20;

/**
 * Filtros logs admin
 */
$adminEmail = isset($_GET['admin_email']) ? trim($_GET['admin_email']) : '';
$adminAction = isset($_GET['admin_action']) ? trim($_GET['admin_action']) : '';
$adminPage = clamp_page(isset($_GET['admin_page']) ? $_GET['admin_page'] : 1);

/**
 * Filtros logs velas
 */
$candleIp = isset($_GET['candle_ip']) ? trim($_GET['candle_ip']) : '';
$candleEndpoint = isset($_GET['candle_endpoint']) ? trim($_GET['candle_endpoint']) : '';
$candleMessage = isset($_GET['candle_message']) ? trim($_GET['candle_message']) : '';
$candleDateFrom = isset($_GET['candle_date_from']) ? trim($_GET['candle_date_from']) : '';
$candleDateTo = isset($_GET['candle_date_to']) ? trim($_GET['candle_date_to']) : '';
$candlePage = clamp_page(isset($_GET['candle_page']) ? $_GET['candle_page'] : 1);

/**
 * Filtro por fecha por defecto para logs operativos
 * Si no se especifica rango, mostramos últimos 7 días.
 */
if ($candleDateFrom === '' && $candleDateTo === '') {
  $candleDateFrom = date('Y-m-d', strtotime('-7 days'));
  $candleDateTo = date('Y-m-d');
}

$adminLogs = array();
$candleLogs = array();
$adminError = '';
$candleError = '';

/**
 * Logs administrativos
 */
try {
  $adminWhere = array();
  $adminParams = array();

  if ($adminEmail !== '') {
    $adminWhere[] = 'user_email LIKE :admin_email';
    $adminParams[':admin_email'] = '%' . $adminEmail . '%';
  }

  if ($adminAction !== '') {
    $adminWhere[] = 'action = :admin_action';
    $adminParams[':admin_action'] = $adminAction;
  }

  $adminWhereSql = $adminWhere ? ('WHERE ' . implode(' AND ', $adminWhere)) : '';

  $countStmt = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM admin_logs
    $adminWhereSql
  ");
  $countStmt->execute($adminParams);
  $adminTotal = (int)$countStmt->fetchColumn();

  $adminTotalPages = max(1, (int)ceil($adminTotal / $perPage));
  if ($adminPage > $adminTotalPages) {
    $adminPage = $adminTotalPages;
  }

  $adminOffset = ($adminPage - 1) * $perPage;

  $sql = "
    SELECT id, user_email, action, entity_type, entity_id, description, created_at
    FROM admin_logs
    $adminWhereSql
    ORDER BY created_at DESC, id DESC
    LIMIT " . (int)$perPage . " OFFSET " . (int)$adminOffset;

  $stmt = $pdo->prepare($sql);
  $stmt->execute($adminParams);
  $adminLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $actionOptionsStmt = $pdo->query("
    SELECT DISTINCT action
    FROM admin_logs
    ORDER BY action ASC
  ");
  $adminActionOptions = $actionOptionsStmt->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
  $adminError = $e->getMessage();
  $adminTotal = 0;
  $adminTotalPages = 1;
  $adminActionOptions = array();
}

/**
 * Logs velas
 */
try {
  $candleWhere = array();
  $candleParams = array();

  if ($candleEndpoint !== '') {
    $candleWhere[] = 'endpoint = :candle_endpoint';
    $candleParams[':candle_endpoint'] = $candleEndpoint;
  }

  if ($candleIp !== '') {
    $candleWhere[] = 'ip LIKE :candle_ip';
    $candleParams[':candle_ip'] = '%' . $candleIp . '%';
  }

  if ($candleMessage !== '') {
    $candleWhere[] = 'message LIKE :candle_message';
    $candleParams[':candle_message'] = '%' . $candleMessage . '%';
  }

  if ($candleDateFrom !== '') {
    $candleWhere[] = 'created_at >= :candle_date_from';
    $candleParams[':candle_date_from'] = $candleDateFrom . ' 00:00:00';
  }

  if ($candleDateTo !== '') {
    $candleWhere[] = 'created_at <= :candle_date_to';
    $candleParams[':candle_date_to'] = $candleDateTo . ' 23:59:59';
  }

  $candleWhereSql = $candleWhere ? ('WHERE ' . implode(' AND ', $candleWhere)) : '';

  $countStmt = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM logs
    $candleWhereSql
  ");
  $countStmt->execute($candleParams);
  $candleTotal = (int)$countStmt->fetchColumn();

  $candleTotalPages = max(1, (int)ceil($candleTotal / $perPage));
  if ($candlePage > $candleTotalPages) {
    $candlePage = $candleTotalPages;
  }

  $candleOffset = ($candlePage - 1) * $perPage;

  $sql = "
    SELECT id, created_at, level, endpoint, ip, message
    FROM logs
    $candleWhereSql
    ORDER BY created_at DESC, id DESC
    LIMIT " . (int)$perPage . " OFFSET " . (int)$candleOffset;

  $stmt = $pdo->prepare($sql);
  $stmt->execute($candleParams);
  $candleLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $endpointOptionsStmt = $pdo->query("
    SELECT DISTINCT endpoint
    FROM logs
    ORDER BY endpoint ASC
  ");
  $endpointOptions = $endpointOptionsStmt->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
  $candleError = $e->getMessage();
  $candleTotal = 0;
  $candleTotalPages = 1;
  $endpointOptions = array();
}

require __DIR__ . '/partials/header.php';
?>

<div class="pageHead">
  <div>
    <h1>Logs</h1>
    <div class="pageSub">Registro de actividad administrativa y operacional</div>
  </div>
</div>

<section class="formCard sectionSpace">
  <div class="formHd">
    <strong>Logs administrativos</strong>
    <span class="small">Últimas acciones realizadas desde el panel</span>
  </div>

  <div class="formBd">
    <?php if ($adminError): ?>
      <div class="flash err">
        <?= htmlspecialchars('No fue posible cargar los logs administrativos: ' . $adminError, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <form method="get" class="logsFilters">
      <input type="hidden" name="candle_endpoint" value="<?= htmlspecialchars($candleEndpoint, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="candle_ip" value="<?= htmlspecialchars($candleIp, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="candle_message" value="<?= htmlspecialchars($candleMessage, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="candle_date_from" value="<?= htmlspecialchars($candleDateFrom, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="candle_date_to" value="<?= htmlspecialchars($candleDateTo, ENT_QUOTES, 'UTF-8') ?>">

      <div class="logsFilterGrid">
        <div>
          <label for="admin_email">Usuario</label>
          <input
            type="text"
            id="admin_email"
            name="admin_email"
            value="<?= htmlspecialchars($adminEmail, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="correo del admin"
          >
        </div>

        <div>
          <label for="admin_action">Acción</label>
          <select id="admin_action" name="admin_action">
            <option value="">Todas</option>
            <?php foreach ($adminActionOptions as $option): ?>
              <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" <?= $adminAction === $option ? 'selected' : '' ?>>
                <?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="pageActions" style="margin-top:12px;">
        <button type="submit" class="btn">Filtrar</button>
        <a class="btn secondary" href="logs.php?candle_endpoint=<?= urlencode($candleEndpoint) ?>&candle_date_from=<?= urlencode($candleDateFrom) ?>&candle_date_to=<?= urlencode($candleDateTo) ?>">Limpiar filtros admin</a>
      </div>
    </form>

    <div class="small logsSummary">
      <?= (int)$adminTotal ?> resultado(s) · página <?= (int)$adminPage ?> de <?= (int)$adminTotalPages ?>
    </div>

    <table class="adminTable">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Usuario</th>
          <th>Acción</th>
          <th>Entidad</th>
          <th>ID entidad</th>
          <th>Descripción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($adminLogs as $log): ?>
          <tr>
            <td><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($log['user_email'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($log['entity_type'] ? $log['entity_type'] : '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($log['entity_id'] ? $log['entity_id'] : '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($log['description'], ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
        <?php endforeach; ?>

        <?php if (!$adminLogs && !$adminError): ?>
          <tr>
            <td colspan="6">No hay logs administrativos para los filtros seleccionados.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php if ($adminTotalPages > 1): ?>
      <div class="pagination">
        <?php if ($adminPage > 1): ?>
          <a class="btn secondary" href="<?= htmlspecialchars(qs_merge(array('admin_page' => $adminPage - 1)), ENT_QUOTES, 'UTF-8') ?>">Anterior</a>
        <?php endif; ?>

        <span class="paginationInfo">Página <?= (int)$adminPage ?> / <?= (int)$adminTotalPages ?></span>

        <?php if ($adminPage < $adminTotalPages): ?>
          <a class="btn secondary" href="<?= htmlspecialchars(qs_merge(array('admin_page' => $adminPage + 1)), ENT_QUOTES, 'UTF-8') ?>">Siguiente</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="formCard">
  <div class="formHd">
    <strong>Logs de creación de velas</strong>
    <span class="small">Actividad registrada por el portal público</span>
  </div>

  <div class="formBd">
    <?php if ($candleError): ?>
      <div class="flash err">
        <?= htmlspecialchars('No fue posible cargar los logs de velas: ' . $candleError, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <form method="get" class="logsFilters">
      <input type="hidden" name="admin_email" value="<?= htmlspecialchars($adminEmail, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="admin_action" value="<?= htmlspecialchars($adminAction, ENT_QUOTES, 'UTF-8') ?>">

      <div class="logsFilterGrid logsFilterGrid--five">
        <div>
          <label for="candle_endpoint">Endpoint</label>
          <select id="candle_endpoint" name="candle_endpoint">
            <option value="">Todos</option>
            <?php foreach ($endpointOptions as $option): ?>
              <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" <?= $candleEndpoint === $option ? 'selected' : '' ?>>
                <?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label for="candle_ip">IP</label>
          <input
            type="text"
            id="candle_ip"
            name="candle_ip"
            value="<?= htmlspecialchars($candleIp, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="200.68.9.177"
          >
        </div>

        <div>
          <label for="candle_message">Texto en mensaje</label>
          <input
            type="text"
            id="candle_message"
            name="candle_message"
            value="<?= htmlspecialchars($candleMessage, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="created id="
          >
        </div>

        <div>
          <label for="candle_date_from">Desde</label>
          <input
            type="date"
            id="candle_date_from"
            name="candle_date_from"
            value="<?= htmlspecialchars($candleDateFrom, ENT_QUOTES, 'UTF-8') ?>"
          >
        </div>

        <div>
          <label for="candle_date_to">Hasta</label>
          <input
            type="date"
            id="candle_date_to"
            name="candle_date_to"
            value="<?= htmlspecialchars($candleDateTo, ENT_QUOTES, 'UTF-8') ?>"
          >
        </div>
      </div>

      <div class="pageActions" style="margin-top:12px;">
        <button type="submit" class="btn">Filtrar</button>
        <a class="btn secondary" href="logs.php?admin_email=<?= urlencode($adminEmail) ?>&admin_action=<?= urlencode($adminAction) ?>&candle_endpoint=create_candle">Limpiar filtros velas</a>
      </div>
    </form>

    <div class="small logsSummary">
      <?= (int)$candleTotal ?> resultado(s) · página <?= (int)$candlePage ?> de <?= (int)$candleTotalPages ?>
    </div>

    <table class="adminTable">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Nivel</th>
          <th>Endpoint</th>
          <th>IP</th>
          <th>Mensaje</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($candleLogs as $log): ?>
          <tr>
            <td><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($log['level'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($log['endpoint'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($log['ip'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="mono logMessage"><?= htmlspecialchars($log['message'], ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
        <?php endforeach; ?>

        <?php if (!$candleLogs && !$candleError): ?>
          <tr>
            <td colspan="5">No hay logs de velas para los filtros seleccionados.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php if ($candleTotalPages > 1): ?>
      <div class="pagination">
        <?php if ($candlePage > 1): ?>
          <a class="btn secondary" href="<?= htmlspecialchars(qs_merge(array('candle_page' => $candlePage - 1)), ENT_QUOTES, 'UTF-8') ?>">Anterior</a>
        <?php endif; ?>

        <span class="paginationInfo">Página <?= (int)$candlePage ?> / <?= (int)$candleTotalPages ?></span>

        <?php if ($candlePage < $candleTotalPages): ?>
          <a class="btn secondary" href="<?= htmlspecialchars(qs_merge(array('candle_page' => $candlePage + 1)), ENT_QUOTES, 'UTF-8') ?>">Siguiente</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>