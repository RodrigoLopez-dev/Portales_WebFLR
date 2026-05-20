<?php
require_once 'gpConfig.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['token'])) {
    unset($_SESSION['token']);
}

if (isset($_SESSION['userData'])) {
    unset($_SESSION['userData']);
}

if (isset($gClient)) {
    try {
        $gClient->revokeToken();
    } catch (Exception $e) {
        // En PHP 5.6 podemos ignorar el error para no romper el logout
    }
}

session_destroy();

header("Location: ./");
exit;