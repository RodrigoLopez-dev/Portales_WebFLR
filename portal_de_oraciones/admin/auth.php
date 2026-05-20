<?php
session_start();

if (!function_exists('random_bytes')) {
  function random_bytes($length)
  {
    if (function_exists('openssl_random_pseudo_bytes')) {
      return openssl_random_pseudo_bytes($length);
    }
    throw new Exception('No hay generador seguro disponible');
  }
}

function current_admin_user()
{
  return isset($_SESSION['userData']) && is_array($_SESSION['userData'])
    ? $_SESSION['userData']
    : null;
}

function is_logged_in()
{
  $user = current_admin_user();

  return $user !== null
    && isset($user['mail'])
    && $user['mail'] !== '';
}

function is_admin_authorized()
{
  $user = current_admin_user();

  return $user !== null
    && isset($user['cod_privilegio'])
    && (int)$user['cod_privilegio'] > 0
    && isset($user['estado'])
    && (int)$user['estado'] === 1;
}

function destroy_admin_session()
{
  $_SESSION = array();

  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
      session_name(),
      '',
      time() - 42000,
      $params['path'],
      $params['domain'],
      $params['secure'],
      $params['httponly']
    );
  }

  session_destroy();
}

function refresh_admin_user_from_db()
{
  $user = current_admin_user();

  if (!$user || empty($user['id'])) {
    return false;
  }

  require_once __DIR__ . '/db_admin.php';
  $pdo = admin_db();

  $stmt = $pdo->prepare("
    SELECT id, oauth_uid, mail, name, lastname, picture, cod_privilegio, estado
    FROM usuarios
    WHERE id = :id
    LIMIT 1
  ");
  $stmt->execute(array(':id' => (int)$user['id']));
  $freshUser = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$freshUser) {
    destroy_admin_session();
    return false;
  }

  $_SESSION['userData'] = array(
    'id' => (int)$freshUser['id'],
    'oauth_uid' => $freshUser['oauth_uid'],
    'mail' => $freshUser['mail'],
    'name' => $freshUser['name'],
    'lastname' => $freshUser['lastname'],
    'picture' => $freshUser['picture'],
    'cod_privilegio' => (int)$freshUser['cod_privilegio'],
    'estado' => (int)$freshUser['estado']
  );

  return true;
}

function require_login()
{
  if (!is_logged_in()) {
    header('Location: login.php');
    exit;
  }

  if (!refresh_admin_user_from_db()) {
    header('Location: login.php');
    exit;
  }

  if (!is_admin_authorized()) {
    header('Location: restriccion.php');
    exit;
  }
}

function csrf_token()
{
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['csrf'];
}

function require_csrf()
{
  $t = isset($_POST['csrf']) ? $_POST['csrf'] : '';

  if (!$t || !isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $t)) {
    http_response_code(403);
    exit('CSRF inválido');
  }
}