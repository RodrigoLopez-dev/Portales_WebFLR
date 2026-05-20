<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_admin.php';
require_once __DIR__ . '/helpers/log.php';

require_login();
require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: usuarios.php');
  exit;
}

$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

if ($userId <= 0) {
  $_SESSION['flash_error'] = 'Usuario inválido.';
  header('Location: usuarios.php');
  exit;
}

$currentUser = isset($_SESSION['userData']) ? $_SESSION['userData'] : null;
$currentUserId = isset($currentUser['id']) ? (int) $currentUser['id'] : 0;

try {
  $pdo = admin_db();

  $stmt = $pdo->prepare("
    SELECT id, mail, cod_privilegio, estado
    FROM usuarios
    WHERE id = :id
    LIMIT 1
  ");
  $stmt->execute(array(':id' => $userId));
  $targetUser = $stmt->fetch();

  if (!$targetUser) {
    $_SESSION['flash_error'] = 'El usuario no existe.';
    header('Location: usuarios.php');
    exit;
  }

  switch ($action) {
    case 'grant_admin':
      $stmt = $pdo->prepare("
        UPDATE usuarios
        SET cod_privilegio = 1,
            updated = NOW()
        WHERE id = :id
      ");
      $stmt->execute(array(':id' => $userId));

      $_SESSION['flash_ok'] = 'Permiso de administrador otorgado.';

      admin_log(
        'grant_admin',
        'usuario',
        $userId,
        'Otorgó permisos de administrador a ' . $targetUser['mail']
      );
      break;

    case 'revoke_admin':
      if ($userId === $currentUserId) {
        $_SESSION['flash_error'] = 'No puedes quitarte tu propio acceso admin.';
        header('Location: usuarios.php');
        exit;
      }

      $stmt = $pdo->prepare("
        UPDATE usuarios
        SET cod_privilegio = 0,
            updated = NOW()
        WHERE id = :id
      ");
      $stmt->execute(array(':id' => $userId));

      $_SESSION['flash_ok'] = 'Permiso de administrador removido.';

      admin_log(
        'revoke_admin',
        'usuario',
        $userId,
        'Quitó permisos de administrador a ' . $targetUser['mail']
      );
      break;

    case 'activate':
      $stmt = $pdo->prepare("
        UPDATE usuarios
        SET estado = 1,
            updated = NOW()
        WHERE id = :id
      ");
      $stmt->execute(array(':id' => $userId));

      $_SESSION['flash_ok'] = 'Usuario activado correctamente.';

      admin_log(
        'activate_user',
        'usuario',
        $userId,
        'Activó al usuario ' . $targetUser['mail']
      );
      break;

    case 'deactivate':
      if ($userId === $currentUserId) {
        $_SESSION['flash_error'] = 'No puedes desactivar tu propia cuenta.';
        header('Location: usuarios.php');
        exit;
      }

      $stmt = $pdo->prepare("
        UPDATE usuarios
        SET estado = 0,
            updated = NOW()
        WHERE id = :id
      ");
      $stmt->execute(array(':id' => $userId));

      $_SESSION['flash_ok'] = 'Usuario desactivado correctamente.';

      admin_log(
        'deactivate_user',
        'usuario',
        $userId,
        'Desactivó al usuario ' . $targetUser['mail']
      );
      break;

    default:
      $_SESSION['flash_error'] = 'Acción no válida.';
      break;
  }

} catch (Exception $e) {
  $_SESSION['flash_error'] = 'Error al procesar la acción: ' . $e->getMessage();
}

header('Location: usuarios.php');
exit;