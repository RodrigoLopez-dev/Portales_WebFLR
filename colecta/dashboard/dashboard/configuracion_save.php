<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard/dashboard/configuracion_inicio.php');
}

$csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

if (
    empty($_SESSION['csrf_token']) ||
    $csrfToken === '' ||
    $csrfToken !== $_SESSION['csrf_token']
) {
    redirect('dashboard/dashboard/configuracion_inicio.php?error=csrf');
}

function savePortalConfig($db, $key, $value)
{
    $stmt = $db->prepare("
        INSERT INTO portal_config (config_key, config_value, modified)
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            config_value = VALUES(config_value),
            modified = NOW()
    ");

    $stmt->bind_param("ss", $key, $value);
    $stmt->execute();
    $stmt->close();
}

/*
 * Montos de botones
 */
for ($i = 1; $i <= 4; $i++) {
    $amountKey = 'donation_amount_' . $i;
    $value = isset($_POST[$amountKey]) ? intval($_POST[$amountKey]) : 0;

    if ($value <= 0) {
        redirect('dashboard/dashboard/configuracion_inicio.php?error=amount');
    }

    savePortalConfig($db, $amountKey, strval($value));
}

/*
 * Textos secundarios de botones
 */
for ($i = 1; $i <= 4; $i++) {
    $textKey = 'donation_text_' . $i;
    $enabledKey = 'donation_text_enabled_' . $i;

    $textValue = isset($_POST[$textKey]) ? trim($_POST[$textKey]) : '';
    $enabledValue = isset($_POST[$enabledKey]) ? '1' : '0';

    savePortalConfig($db, $textKey, $textValue);
    savePortalConfig($db, $enabledKey, $enabledValue);
}

/*
 * Imagen existente seleccionada.
 */
$imageMode = isset($_POST['image_mode']) ? trim($_POST['image_mode']) : 'upload';
$existingImage = '';

if ($imageMode !== 'upload') {
    $existingImage = $imageMode;
}

if ($existingImage !== '') {
    $basePath = realpath(__DIR__ . '/../../uploads/config/');
    $realPath = realpath(__DIR__ . '/../../' . $existingImage);

    if ($basePath === false || $realPath === false || strpos($realPath, $basePath) !== 0) {
        redirect('dashboard/dashboard/configuracion_inicio.php?error=image_path');
    }

    $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'webp');

    if (!in_array($ext, $allowed)) {
        redirect('dashboard/dashboard/configuracion_inicio.php?error=format');
    }

    savePortalConfig($db, 'index_image', $existingImage);
}

/*
 * Nueva imagen subida.
 * Si se seleccionó imagen existente y además se sube una nueva,
 * la nueva imagen queda como definitiva.
 */
if (isset($_FILES['index_image']) && $_FILES['index_image']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['index_image']['tmp_name'];
    $fileName = $_FILES['index_image']['name'];
    $fileSize = intval($_FILES['index_image']['size']);

    if ($fileSize > 2097152) {
        redirect('dashboard/dashboard/configuracion_inicio.php?error=size');
    }

    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'webp');

    if (!in_array($ext, $allowed)) {
        redirect('dashboard/dashboard/configuracion_inicio.php?error=format');
    }

    $uploadDir = __DIR__ . '/../../uploads/config/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFileName = 'index_banner_' . date('YmdHis') . '.' . $ext;
    $destination = $uploadDir . $newFileName;

    if (!move_uploaded_file($tmpName, $destination)) {
        redirect('dashboard/dashboard/configuracion_inicio.php?error=upload');
    }

    $relativePath = 'uploads/config/' . $newFileName;

    savePortalConfig($db, 'index_image', $relativePath);
}

redirect('dashboard/dashboard/configuracion_inicio.php?ok=saved');