<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_admin.php';

require_login();

$currentPage = 'settings';

function get_setting($pdo, $key, $defaultValue)
{
  $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = :key LIMIT 1");
  $stmt->execute(array(':key' => $key));
  $row = $stmt->fetch();

  return $row && isset($row['value']) && $row['value'] !== ''
    ? (int)$row['value']
    : (int)$defaultValue;
}

$errorMsg = '';

try {
  $pdo = admin_db();
  $expiringHours = get_setting($pdo, 'expiring_hours', 6);
  $candleHours = get_setting($pdo, 'candle_hours', 48);
} catch (Exception $e) {
  $errorMsg = $e->getMessage();
  $expiringHours = 6;
  $candleHours = 48;
}

$csrf = csrf_token();

require __DIR__ . '/partials/header.php';
?>

<div class="pageHead">
  <div>
    <h1>Configuración</h1>
    <div class="pageSub">Ajustes generales del módulo admin</div>
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

<section class="formCard">
  <div class="formHd">
    <strong>Configuración de velas</strong>
    <span class="small">Parámetros operativos del portal</span>
  </div>

  <div class="formBd">
    <div class="small" style="margin-bottom:16px;">
      Desde aquí puedes ajustar el tiempo de duración de las velas y el umbral usado para clasificarlas como próximas a expirar dentro del dashboard.
    </div>

    <form method="post" action="actions.php" class="settingsForm">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="action" value="save_settings">

      <div class="settingsField">
        <label for="candle_hours">Horas de duración de la vela</label>
        <input
          id="candle_hours"
          type="number"
          name="candle_hours"
          min="1"
          max="720"
          value="<?= htmlspecialchars($candleHours, ENT_QUOTES, 'UTF-8') ?>"
          required
        >
        <div class="small settingsHelp">Tiempo total que una vela permanece activa en el portal.</div>
      </div>

      <div class="settingsField">
        <label for="expiring_hours">Horas para considerar “por vencer”</label>
        <input
          id="expiring_hours"
          type="number"
          name="expiring_hours"
          min="1"
          max="168"
          value="<?= htmlspecialchars($expiringHours, ENT_QUOTES, 'UTF-8') ?>"
          required
        >
        <div class="small settingsHelp">Se usa para alertas, clasificación y visualización dentro del dashboard.</div>
      </div>

      <div class="pageActions">
        <button class="btn" type="submit">Guardar configuración</button>
      </div>
    </form>
  </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>