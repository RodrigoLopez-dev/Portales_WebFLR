<?php
$currentPage = 'velas';

$sharedCandleId = isset($_GET['candle']) ? trim($_GET['candle']) : '';
$shareToken = isset($_GET['share_token']) ? trim($_GET['share_token']) : '';
$hasSharedCandle = ($sharedCandleId !== '');

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$scheme = $isHttps ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'samboxflrosas.cl';

$scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/portal_de_oraciones_V3/velas.php';
$basePath = rtrim(dirname($scriptName), '/\\');

$currentUrl = $scheme . '://' . $host . $basePath . '/velas.php';

if ($sharedCandleId !== '') {
  $currentUrl .= '?candle=' . rawurlencode($sharedCandleId);

  if ($shareToken !== '') {
    $currentUrl .= '&share_token=' . rawurlencode($shareToken);
  }
}

$ogImage = $scheme . '://' . $host . $basePath . '/assets/img/share-vela.jpg';

$ogTitle = $hasSharedCandle
  ? 'Alguien encendió una vela por ti'
  : 'Velas encendidas | Fundación Las Rosas';

$ogDescription = $hasSharedCandle
  ? 'Revisa la vela que fue encendida y acompaña esta intención con tu oración.'
  : 'Revisa las velas encendidas y acompaña con tu oración a los adultos mayores de Fundación Las Rosas.';
?>



<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?php echo htmlspecialchars($ogTitle, ENT_QUOTES, 'UTF-8'); ?></title>

  <meta property="og:type" content="website">
  <meta property="og:title" content="<?php echo htmlspecialchars($ogTitle, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($ogDescription, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:image" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:url" content="<?php echo htmlspecialchars($currentUrl, ENT_QUOTES, 'UTF-8'); ?>">

  <meta name="twitter:card" content="summary_large_image">

  <link rel="stylesheet" href="./assets/style.css">
</head>

<body class="site-bg velas-page">
  <div class="site-shell main-content">

    <?php include './partials/navbar.php'; ?>

    <section class="formCard">

      <div class="formHd">
        <div class="velas-header-text">
          <strong>Velas encendidas</strong>
          <div class="cont">
            <span class="velas-subtext">
              Hasta ahora se han encendido <b id="totalVelas">0</b> velas.
            </span>
            <span class="velas-subtext">
              Velas actualmente encendidas: <b id="velasActivas">0</b>
            </span>
          </div>

        </div>

        <div class="ctaRow" style="margin:0;">
          <a class="btn-cta" href="./enciende.php">Encender nueva vela</a>
        </div>
      </div>

      <div class="formBd">
        <div class="kpis">
          <div class="kpi">
            <div class="v" id="countActive">0</div>
            <div class="l">Velas activas</div>
          </div>
          <div class="kpi">
            <div class="v" id="nextExpire">—</div>
            <div class="l">Próxima expiración</div>
          </div>
          <div class="kpi">
            <div class="v mono" id="clock">—</div>
            <div class="l">Hora local</div>
          </div>
        </div>

        <div class="candles" id="candles"></div>
        <div class="pagination" id="pagination"></div>

        <div class="footerNote">
          Click en una vela: si es tuya verás el detalle privado; si no, solo iniciales y fecha.
        </div>

        <div class="notice" id="notice"></div>
      </div>
    </section>
  </div>

  <div class="modalBack" id="modalBack">
    <div class="modal">
      <div class="modalHd">
        <strong>Detalle de vela</strong>
        <button class="closeBtn" id="closeModal">Cerrar</button>
      </div>
      <div class="modalBd">
        <div id="modalBody" class="muted" style="line-height:1.6;"></div>
        <div class="shareRow" id="shareRow" style="display:none;">
          <input id="shareLink" class="mono" readonly>
          <button class="btn-cta-sm" id="copyLink" type="button">Copiar link</button>
          <a id="whatsappShare" target="_blank" class="wa-btn" title="Compartir por WhatsApp">
            <img src="./assets/img/whatsapp.png" alt="WhatsApp">
          </a>
        </div>
        <div class="notice" id="copyNotice" style="margin-top:12px"></div>
      </div>
    </div>
  </div>

  <script>
    window.VELAS_CONFIG = {
      API_BASE: "./api",
      MY_TOKENS_KEY: "portal_oraciones_owner_tokens_v1",
      SHARE_TOKENS_KEY: "portal_oraciones_share_tokens_v1"
    };
  </script>

  <script src="./js/velas.js"></script>
  <script src="./js/navbar.js"></script>

  <?php include './partials/footer.php'; ?>

</body>

</html>