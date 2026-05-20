<?php

session_start();
date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/database.php';

load_env(__DIR__ . '/../../.env');

header('Content-Type: application/json; charset=UTF-8');

$google_client_id = env_value('GOOGLE_CLIENT_ID', '');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('authenticated' => false, 'error' => 'Método no permitido'));
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['token']) || trim($input['token']) === '') {
    echo json_encode(array('authenticated' => false, 'error' => 'Token no recibido'));
    exit;
}

$tokenID = trim($input['token']);

$response = @file_get_contents('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($tokenID));

if ($response === false) {
    echo json_encode(array('authenticated' => false, 'error' => 'No fue posible validar el token con Google'));
    exit;
}

$googleData = json_decode($response);

if (!$googleData || !isset($googleData->aud) || $googleData->aud !== $google_client_id) {
    echo json_encode(array('authenticated' => false, 'error' => 'Token de cliente no válido'));
    exit;
}

if (!isset($googleData->sub) || !isset($googleData->email)) {
    echo json_encode(array('authenticated' => false, 'error' => 'Respuesta de Google incompleta'));
    exit;
}

$mysqli = db_connect();

$oauthUid = $googleData->sub;
$email = isset($googleData->email) ? $googleData->email : '';
$name = isset($googleData->given_name) ? $googleData->given_name : '';
$lastname = isset($googleData->family_name) ? $googleData->family_name : '';
$picture = isset($googleData->picture) ? $googleData->picture : '';

$stmt = $mysqli->prepare("
    SELECT oauth_uid, mail, name, lastname, picture, cod_privilegio
    FROM usuarios
    WHERE oauth_uid = ?
    LIMIT 1
");

if (!$stmt) {
    echo json_encode(array('authenticated' => false, 'error' => 'Error preparando consulta de usuario'));
    exit;
}

$stmt->bind_param('s', $oauthUid);

if (!$stmt->execute()) {
    echo json_encode(array('authenticated' => false, 'error' => 'Error consultando usuario'));
    $stmt->close();
    exit;
}

$stmt->bind_result($dbOauthUid, $dbMail, $dbName, $dbLastname, $dbPicture, $dbCodPrivilegio);

if ($stmt->fetch()) {
    $_SESSION['userData']['cod_privilegio'] = $dbCodPrivilegio;
    $_SESSION['userData']['cod_usuario'] = $dbOauthUid;
    $_SESSION['userData']['name'] = $dbName;
    $_SESSION['userData']['lastname'] = $dbLastname;
    $_SESSION['userData']['picture'] = $dbPicture;

    $stmt->close();
} else {
    $stmt->close();

    $cod_privilegio = 0;

    $insertStmt = $mysqli->prepare("
        INSERT INTO usuarios
        (
            oauth_uid,
            mail,
            name,
            lastname,
            picture,
            cod_privilegio,
            created
        )
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");

    if (!$insertStmt) {
        echo json_encode(array('authenticated' => false, 'error' => 'Error preparando creación de usuario'));
        exit;
    }

    $insertStmt->bind_param(
        'sssssi',
        $oauthUid,
        $email,
        $name,
        $lastname,
        $picture,
        $cod_privilegio
    );

    if (!$insertStmt->execute()) {
        echo json_encode(array('authenticated' => false, 'error' => 'Error al insertar usuario'));
        $insertStmt->close();
        exit;
    }

    $insertStmt->close();

    $_SESSION['userData']['cod_privilegio'] = 0;
    $_SESSION['userData']['cod_usuario'] = $oauthUid;
    $_SESSION['userData']['name'] = $name;
    $_SESSION['userData']['lastname'] = $lastname;
    $_SESSION['userData']['picture'] = $picture;
}

$_SESSION['access_token'] = $tokenID;

echo json_encode(array('authenticated' => true));
exit;