<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_admin.php';
require_login();

$errorMsg = '';

try {
  $pdo = admin_db();

  $EXPIRING_HOURS = get_setting($pdo, 'expiring_hours', 6);
  $CANDLE_HOURS_SETTING = get_setting($pdo, 'candle_hours', 48);

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
}

$csrf = csrf_token();

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
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin — Portal de Oraciones</title>
  <link rel="stylesheet" href="../assets/style.css">
  <style>
    .adminTop {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      align-items: center;
      margin: 10px 0 16px
    }

    .adminTop h1 {
      margin: 0;
      font-size: 20px
    }

    .actionsRow {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center
    }

    .small {
      font-size: 12px;
      color: var(--muted)
    }

    .mono {
      font-family: ui-monospace, Menlo, Consolas, monospace
    }

    .grid3 {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 14px;
      margin-top: 14px
    }

    @media (max-width:1100px) {
      .grid3 {
        grid-template-columns: 1fr
      }
    }

    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 10px
    }

    th {
      font-size: 12px;
      color: var(--muted);
      text-align: left;
      padding: 0 10px
    }

    td {
      background: rgba(10, 14, 24, .45);
      border: 1px solid rgba(34, 48, 82, .7);
      padding: 10px;
      border-radius: 14px;
      font-size: 13px
    }

    .tag {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 10px;
      border-radius: 999px;
      border: 1px solid rgba(34, 48, 82, .7);
      background: rgba(10, 14, 24, .45);
      font-size: 12px;
      color: var(--muted)
    }

    .tag.warn {
      border-color: rgba(215, 179, 106, .55);
      color: #ffe8b8
    }

    .tag.bad {
      border-color: rgba(255, 93, 93, .55);
      color: #ffd1d1
    }

    .tag.ok {
      border-color: rgba(94, 242, 166, .55);
      color: #c9ffe6
    }

    .flash {
      margin: 0 0 14px;
      padding: 12px 14px;
      border-radius: 12px;
      border: 1px solid rgba(34, 48, 82, .7);
      font-size: 14px;
    }

    .flash.ok {
      background: rgba(94, 242, 166, .10);
      border-color: rgba(94, 242, 166, .35);
      color: #c9ffe6;
    }

    .flash.err {
      background: rgba(255, 93, 93, .10);
      border-color: rgba(255, 93, 93, .35);
      color: #ffd1d1;
    }
  </style>
</head>

<body>
  <div class="wrap">
    <div class="adminTop">
      <h1>Admin — Portal de Oraciones</h1>
      <div class="actionsRow">
        <span class="tag">BD:
          <span class="mono">portal_de_oraciones</span>
        </span>
        <span class="tag">Usuario:
          <span class="mono">
            <?php
            $adminUserLabel = '';
            if (isset($_SESSION['userData']) && is_array($_SESSION['userData'])) {
              $name = isset($_SESSION['userData']['name']) ? trim($_SESSION['userData']['name']) : '';
              $lastname = isset($_SESSION['userData']['lastname']) ? trim($_SESSION['userData']['lastname']) : '';
              $mail = isset($_SESSION['userData']['mail']) ? trim($_SESSION['userData']['mail']) : '';

              $fullName = trim($name . ' ' . $lastname);
              $adminUserLabel = $fullName !== '' ? $fullName : $mail;
            }
            ?>
            <?= htmlspecialchars($adminUserLabel, ENT_QUOTES, 'UTF-8') ?>
          </span>
        </span>
        <a class="btn secondary" href="../index.php">Ir al portal</a>
        <a class="btn secondary" href="logout.php">Salir</a>
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
    
    <section class="formCard" style="margin-bottom:14px;">
      <div class="formHd">
        <strong>Configuración</strong>
      </div>
      <div class="formBd">
        <form method="post" action="actions.php" style="display:grid; gap:12px; max-width:420px;">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="action" value="save_settings">

          <div>
            <label>Horas de duración de la vela</label>
            <input type="number" name="candle_hours" min="1" max="720"
              value="<?= htmlspecialchars($CANDLE_HOURS_SETTING, ENT_QUOTES, 'UTF-8') ?>" required>
          </div>

          <div>
            <label>Horas para considerar “por vencer”</label>
            <input type="number" name="expiring_hours" min="1" max="168"
              value="<?= htmlspecialchars($EXPIRING_HOURS, ENT_QUOTES, 'UTF-8') ?>" required>
          </div>

          <button class="btn" type="submit">Guardar configuración</button>
        </form>
      </div>
    </section>

    <section class="formCard">
      <div class="formHd">
        <strong>Resumen</strong>
        <span class="small">“Por vencer” = expira en ≤ <?= (int) $EXPIRING_HOURS ?> horas</span>
      </div>
      <div class="formBd">
        <div class="kpis">
          <div class="kpi">
            <div class="v"><?= $active ?></div>
            <div class="l">Activas</div>
          </div>
          <div class="kpi">
            <div class="v"><?= $expiring ?></div>
            <div class="l">Por vencer (≤ <?= (int) $EXPIRING_HOURS ?>h)</div>
          </div>
          <div class="kpi">
            <div class="v"><?= $expired ?></div>
            <div class="l">Expiradas</div>
          </div>
          <div class="kpi">
            <div class="v"><?= $total ?></div>
            <div class="l">Total en BD</div>
          </div>
        </div>

        <div class="ctaRow" style="margin-top:14px;">
          <form method="post" action="actions.php" style="margin:0;"
            onsubmit="return confirm('¿Eliminar todas las velas expiradas? Esta acción no se puede deshacer.');">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="delete_expired">
            <button class="btn" type="submit" <?= $expired == 0 ? 'disabled' : '' ?>>
              Borrar velas expiradas (<?= $expired ?>)
            </button>
          </form>
        </div>

        <div class="footerNote">
          El portal público no muestra expiradas porque filtra por <span class="mono">expires_at &gt; NOW()</span>.
        </div>
      </div>
    </section>

    <div class="grid3">
      <!-- ACTIVAS -->
      <section class="formCard">
        <div class="formHd">
          <strong>Velas activas (máx 200)</strong>
          <span class="tag ok"><?= $active ?></span>
        </div>
        <div class="formBd">
          <table>
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

      <!-- POR VENCER -->
      <section class="formCard">
        <div class="formHd">
          <strong>Velas por vencer (máx 200)</strong>
          <span class="tag warn"><?= $expiring ?></span>
        </div>
        <div class="formBd">
          <table>
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

      <!-- EXPIRADAS -->
      <section class="formCard">
        <div class="formHd">
          <strong>Velas expiradas (máx 200)</strong>
          <span class="tag bad"><?= $expired ?></span>
        </div>
        <div class="formBd">
          <table>
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
  </div>
</body>

</html>