<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_admin.php';
require_login();

$currentPage = 'dashboard';
$errorMsg = '';

function short_id($id)
{
  return substr($id, 0, 8) . "…";
}

function get_setting($pdo, $key, $defaultValue)
{
  $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = :key LIMIT 1");
  $stmt->execute(array(':key' => $key));
  $row = $stmt->fetch();

  return $row && isset($row['value']) && $row['value'] !== ''
    ? (int) $row['value']
    : (int) $defaultValue;
}

try {
  $pdo = admin_db();

  $EXPIRING_HOURS = get_setting($pdo, 'expiring_hours', 6);

  $totalRow = $pdo->query("SELECT COUNT(*) c FROM candles")->fetch();
  $total = isset($totalRow['c']) ? (int) $totalRow['c'] : 0;

  $activeRow = $pdo->query("SELECT COUNT(*) c FROM candles WHERE expires_at > NOW()")->fetch();
  $active = isset($activeRow['c']) ? (int) $activeRow['c'] : 0;

  $expiredRow = $pdo->query("SELECT COUNT(*) c FROM candles WHERE expires_at <= NOW()")->fetch();
  $expired = isset($expiredRow['c']) ? (int) $expiredRow['c'] : 0;

  $expiringRow = $pdo->query("
      SELECT COUNT(*) c
      FROM candles
      WHERE expires_at > NOW()
        AND expires_at <= (NOW() + INTERVAL $EXPIRING_HOURS HOUR)
    ")->fetch();
  $expiring = isset($expiringRow['c']) ? (int) $expiringRow['c'] : 0;

  $activeList = $pdo->query("
      SELECT id, initials, public_date, created_at, expires_at
      FROM candles
      WHERE expires_at > NOW()
      ORDER BY created_at DESC
      LIMIT 200
    ")->fetchAll();

  $expiringList = $pdo->query("
      SELECT id, initials, public_date, created_at, expires_at
      FROM candles
      WHERE expires_at > NOW()
        AND expires_at <= (NOW() + INTERVAL $EXPIRING_HOURS HOUR)
      ORDER BY expires_at ASC
      LIMIT 200
    ")->fetchAll();

  $expiredList = $pdo->query("
      SELECT id, initials, public_date, created_at, expires_at
      FROM candles
      WHERE expires_at <= NOW()
      ORDER BY expires_at DESC
      LIMIT 200
    ")->fetchAll();

} catch (Exception $e) {
  $errorMsg = $e->getMessage();
  $total = 0;
  $active = 0;
  $expired = 0;
  $expiring = 0;
  $activeList = array();
  $expiringList = array();
  $expiredList = array();
  $EXPIRING_HOURS = 6;
}

$csrf = csrf_token();

require __DIR__ . '/partials/header.php';
?>

<div class="pageHead">
  <div>
    <h1>Dashboard</h1>
    <div class="pageSub">Resumen general del Portal de Oraciones</div>
  </div>

  <div class="pageActions">
    <span class="tag">
      Base de datos:
      <span class="mono">portal_de_oraciones</span>
    </span>
    <a class="btn secondary" href="../index.php">Ir al portal</a>
  </div>
</div>

<?php if ($errorMsg): ?>
  <div class="flash err">
    <?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_ok'])): ?>
  <div class="flash ok">
    <?= htmlspecialchars($_SESSION['flash_ok'], ENT_QUOTES, 'UTF-8') ?>
  </div>
  <?php unset($_SESSION['flash_ok']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="flash err">
    <?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<section class="formCard sectionSpace">
  <div class="formHd">
    <strong>Resumen</strong>
    <span class="small">“Por vencer” = expira en ≤ <?= (int) $EXPIRING_HOURS ?> horas</span>
  </div>

  <div class="formBd">
    <div class="kpis">
      <div class="kpi">
        <div class="v"><?= $active ?></div>
        <div class="l">Velas activas</div>
      </div>

      <div class="kpi">
        <div class="v"><?= $expiring ?></div>
        <div class="l">Por vencer</div>
      </div>

      <div class="kpi">
        <div class="v"><?= $expired ?></div>
        <div class="l">Velas expiradas</div>
      </div>

      <div class="kpi">
        <div class="v"><?= $total ?></div>
        <div class="l">Total registradas</div>
      </div>
    </div>

    <div class="pageActions" style="margin-top:16px;">
      <form
        method="post"
        action="actions.php"
        class="inlineForm"
        onsubmit="return confirm('¿Eliminar todas las velas expiradas? Esta acción no se puede deshacer.');"
      >
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="delete_expired">
        <button class="btn" type="submit" <?= $expired == 0 ? 'disabled' : '' ?>>
          Borrar velas expiradas (<?= $expired ?>)
        </button>
      </form>

      <a class="btn secondary" href="settings.php">Ir a configuración</a>
    </div>

    <div class="footerNote">
      El portal público solo muestra velas vigentes, filtrando por
      <span class="mono">expires_at &gt; NOW()</span>.
    </div>
  </div>
</section>

<div class="grid3">
  <section class="formCard">
    <div class="formHd">
      <strong>Velas activas</strong>
      <span class="tag ok"><?= $active ?></span>
    </div>

    <div class="formBd">
      <table class="adminTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Iniciales</th>
            <th>Fecha</th>
            <th>Expira</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($activeList as $c): ?>
            <tr>
              <td class="mono"><?= htmlspecialchars(short_id($c['id']), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c['initials'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c['public_date'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c['expires_at'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>

          <?php if (!$activeList): ?>
            <tr>
              <td colspan="4">No hay velas activas.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="formCard">
    <div class="formHd">
      <strong>Velas por vencer</strong>
      <span class="tag warn"><?= $expiring ?></span>
    </div>

    <div class="formBd">
      <table class="adminTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Iniciales</th>
            <th>Fecha</th>
            <th>Expira</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($expiringList as $c): ?>
            <tr>
              <td class="mono"><?= htmlspecialchars(short_id($c['id']), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c['initials'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c['public_date'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c['expires_at'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>

          <?php if (!$expiringList): ?>
            <tr>
              <td colspan="4">No hay velas por vencer.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="formCard">
    <div class="formHd">
      <strong>Velas expiradas</strong>
      <span class="tag bad"><?= $expired ?></span>
    </div>

    <div class="formBd">
      <table class="adminTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Iniciales</th>
            <th>Fecha</th>
            <th>Expiró</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($expiredList as $c): ?>
            <tr>
              <td class="mono"><?= htmlspecialchars(short_id($c['id']), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c['initials'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c['public_date'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c['expires_at'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>

          <?php if (!$expiredList): ?>
            <tr>
              <td colspan="4">No hay velas expiradas.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>