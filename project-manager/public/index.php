<?php
$app = require __DIR__ . '/../config/app.php';
$target = rtrim($app['base_url'], '/') . '/';
header('Location: ' . $target, true, 302);
exit;
