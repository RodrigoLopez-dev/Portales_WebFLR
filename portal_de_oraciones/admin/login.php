<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

if (is_logged_in() && is_admin_authorized()) {
    header('Location: dashboard.php');
    exit;
}

$googleClientId = ADMIN_GOOGLE_CLIENT_ID;

if ($googleClientId === '') {
    die('GOOGLE_CLIENT_ID no configurado en el entorno.');
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login</title>
<link rel="stylesheet" href="../assets/style.css">
<script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
<div class="wrap">
  <div class="formCard">
    <div class="formHd"><strong>Admin Portal</strong></div>
    <div class="formBd">
      <p>Ingresa con tu cuenta de Google autorizada.</p>

      <div
        id="g_id_onload"
        data-client_id="<?= htmlspecialchars($googleClientId, ENT_QUOTES, 'UTF-8') ?>"
        data-callback="handleCredentialResponse">
      </div>

      <div
        class="g_id_signin"
        data-type="standard"
        data-theme="filled_blue"
        data-shape="rectangular"
        data-size="large"
        data-text="signin_with"
        data-logo_alignment="left">
      </div>

      <div id="loginError" class="notice err" style="display:none; margin-top:12px;"></div>
    </div>
  </div>
</div>

<script>
function showLoginError(message) {
  var el = document.getElementById('loginError');
  el.textContent = message;
  el.style.display = 'block';
}

function handleCredentialResponse(response) {
  fetch('validate.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      token: response.credential
    })
  })
  .then(function(res) {
    return res.json();
  })
  .then(function(data) {
    if (data.authenticated) {
      window.location.href = 'dashboard.php';
      return;
    }

    if (data.restricted) {
      window.location.href = 'restriccion.php';
      return;
    }

    showLoginError(data.error || 'No fue posible iniciar sesión.');
  })
  .catch(function() {
    showLoginError('Ocurrió un error al validar el acceso.');
  });
}
</script>
</body>
</html>