<?php
require_once __DIR__ . '/../db_admin.php';

function admin_log($action, $entityType, $entityId, $description)
{
    if (!isset($_SESSION['userData']) || !is_array($_SESSION['userData'])) {
        return false;
    }

    $userId = isset($_SESSION['userData']['id']) ? (int)$_SESSION['userData']['id'] : 0;
    $userEmail = isset($_SESSION['userData']['mail']) ? trim($_SESSION['userData']['mail']) : '';

    if ($userId <= 0 || $userEmail === '') {
        return false;
    }

    try {
        $pdo = admin_db();

        $stmt = $pdo->prepare("
            INSERT INTO admin_logs (user_id, user_email, action, entity_type, entity_id, description)
            VALUES (:user_id, :user_email, :action, :entity_type, :entity_id, :description)
        ");

        $stmt->execute(array(
            ':user_id' => $userId,
            ':user_email' => $userEmail,
            ':action' => (string)$action,
            ':entity_type' => $entityType !== null ? (string)$entityType : null,
            ':entity_id' => $entityId !== null ? (string)$entityId : null,
            ':description' => (string)$description
        ));

        return true;
    } catch (Exception $e) {
        return false;
    }
}