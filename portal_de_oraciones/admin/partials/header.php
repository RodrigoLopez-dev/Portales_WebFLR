<?php
if (!isset($currentPage)) {
  $currentPage = '';
}

$user = isset($_SESSION['userData']) ? $_SESSION['userData'] : null;

$displayName = '';
if ($user) {
  $name = isset($user['name']) ? $user['name'] : '';
  $lastname = isset($user['lastname']) ? $user['lastname'] : '';
  $mail = isset($user['mail']) ? $user['mail'] : '';

  $fullName = trim($name . ' ' . $lastname);
  $displayName = $fullName !== '' ? $fullName : $mail;
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Panel</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>

  <div class="content">
    <div class="topbar">
      <div class="topbarTitle">
        <strong>Portal Admin</strong>
        <span>Fundación Las Rosas</span>
      </div>

      <div class="topbarUser">
        <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>
      </div>
    </div>