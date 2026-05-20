<?php
require __DIR__ . '/auth.php';
require_login();
require_csrf();

require_once __DIR__ . '/db_admin.php';
require_once __DIR__ . '/helpers/log.php';

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

$redirect = 'dashboard.php';
if ($action === 'save_settings') {
    $redirect = 'settings.php';
}

try {
    $pdo = admin_db();

    if ($action === 'delete_expired') {
        $stmt = $pdo->prepare("
            DELETE FROM candles
            WHERE expires_at <= NOW()
        ");
        $stmt->execute();

        $deletedCount = (int)$stmt->rowCount();

        $_SESSION['flash_ok'] = 'Se eliminaron ' . $deletedCount . ' velas expiradas.';

        admin_log(
            'delete_expired',
            'candles',
            'expired_cleanup',
            'Eliminó ' . $deletedCount . ' velas expiradas.'
        );

    } elseif ($action === 'save_settings') {
        $candleHours = isset($_POST['candle_hours']) ? (int)$_POST['candle_hours'] : 0;
        $expiringHours = isset($_POST['expiring_hours']) ? (int)$_POST['expiring_hours'] : 0;

        if ($candleHours < 1 || $candleHours > 720 || $expiringHours < 1 || $expiringHours > 168) {
            $_SESSION['flash_error'] = 'Valores fuera de rango.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO settings (`key`, `value`)
                VALUES (:key, :value)
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
            ");

            $stmt->execute(array(
                ':key' => 'candle_hours',
                ':value' => (string)$candleHours
            ));

            $stmt->execute(array(
                ':key' => 'expiring_hours',
                ':value' => (string)$expiringHours
            ));

            $_SESSION['flash_ok'] = 'Configuración actualizada correctamente.';

            admin_log(
                'save_settings',
                'settings',
                'candle_hours,expiring_hours',
                'Actualizó configuración: candle_hours=' . $candleHours . ', expiring_hours=' . $expiringHours
            );
        }
    } else {
        $_SESSION['flash_error'] = 'Acción no válida.';
    }
} catch (Exception $e) {
    $_SESSION['flash_error'] = 'Error al ejecutar la acción: ' . $e->getMessage();
}

header('Location: ' . $redirect);
exit;