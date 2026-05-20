<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_admin.php';
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if (ADMIN_GOOGLE_CLIENT_ID === '') {
    http_response_code(500);
    echo json_encode(array(
        'authenticated' => false,
        'error' => 'GOOGLE_CLIENT_ID no configurado en el entorno'
    ));
    exit;
}

function json_fail($msg, $httpCode)
{
    http_response_code($httpCode);
    echo json_encode(array(
        'authenticated' => false,
        'error' => $msg
    ));
    exit;
}

function json_restricted($msg)
{
    echo json_encode(array(
        'authenticated' => false,
        'restricted' => true,
        'error' => $msg
    ));
    exit;
}

function json_ok()
{
    echo json_encode(array(
        'authenticated' => true
    ));
    exit;
}

function google_tokeninfo($tokenID)
{
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($tokenID);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false) {
        return array(null, 'cURL error: ' . $err);
    }

    if ((int)$code !== 200) {
        return array(null, 'Google tokeninfo HTTP ' . $code . ': ' . substr($resp, 0, 200));
    }

    $data = json_decode($resp, true);
    if (!is_array($data)) {
        return array(null, 'Respuesta no válida desde Google');
    }

    return array($data, null);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_fail('Método no permitido', 405);
}

$raw = file_get_contents('php://input');
if ($raw === false || trim($raw) === '') {
    json_fail('Body vacío', 400);
}

$body = json_decode($raw, true);
if (!is_array($body) || empty($body['token'])) {
    json_fail('Token no recibido', 400);
}

$tokenID = $body['token'];

list($googleData, $googleErr) = google_tokeninfo($tokenID);

if ($googleErr) {
    json_fail('Error validando token con Google: ' . $googleErr, 401);
}

if (empty($googleData['aud']) || $googleData['aud'] !== ADMIN_GOOGLE_CLIENT_ID) {
    json_fail('Token con client_id inválido', 401);
}

if (empty($googleData['sub'])) {
    json_fail('Token inválido: sub ausente', 401);
}

try {
    $pdo = admin_db();

    $stmt = $pdo->prepare("
        SELECT id, oauth_uid, mail, name, lastname, picture, cod_privilegio, estado
        FROM usuarios
        WHERE oauth_uid = :oauth_uid
        LIMIT 1
    ");
    $stmt->execute(array(
        ':oauth_uid' => $googleData['sub']
    ));
    $user = $stmt->fetch();

    if (!$user && !empty($googleData['email'])) {
        $stmt = $pdo->prepare("
            SELECT id, oauth_uid, mail, name, lastname, picture, cod_privilegio, estado
            FROM usuarios
            WHERE mail = :mail
            LIMIT 1
        ");
        $stmt->execute(array(
            ':mail' => $googleData['email']
        ));
        $user = $stmt->fetch();

        if ($user) {
            $upd = $pdo->prepare("
                UPDATE usuarios
                SET oauth_uid = :oauth_uid,
                    name = :name,
                    lastname = :lastname,
                    picture = :picture,
                    updated = NOW()
                WHERE id = :id
            ");
            $upd->execute(array(
                ':oauth_uid' => $googleData['sub'],
                ':name' => isset($googleData['given_name']) ? $googleData['given_name'] : null,
                ':lastname' => isset($googleData['family_name']) ? $googleData['family_name'] : null,
                ':picture' => isset($googleData['picture']) ? $googleData['picture'] : null,
                ':id' => $user['id']
            ));

            $stmt = $pdo->prepare("
                SELECT id, oauth_uid, mail, name, lastname, picture, cod_privilegio, estado
                FROM usuarios
                WHERE id = :id
                LIMIT 1
            ");
            $stmt->execute(array(':id' => $user['id']));
            $user = $stmt->fetch();
        }
    }

    if (!$user) {
        $ins = $pdo->prepare("
            INSERT INTO usuarios (oauth_uid, mail, name, lastname, picture, cod_privilegio, estado, created, updated)
            VALUES (:oauth_uid, :mail, :name, :lastname, :picture, 0, 1, NOW(), NOW())
        ");
        $ins->execute(array(
            ':oauth_uid' => $googleData['sub'],
            ':mail' => isset($googleData['email']) ? $googleData['email'] : '',
            ':name' => isset($googleData['given_name']) ? $googleData['given_name'] : null,
            ':lastname' => isset($googleData['family_name']) ? $googleData['family_name'] : null,
            ':picture' => isset($googleData['picture']) ? $googleData['picture'] : null
        ));

        $id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            SELECT id, oauth_uid, mail, name, lastname, picture, cod_privilegio, estado
            FROM usuarios
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(array(':id' => $id));
        $user = $stmt->fetch();
    }

    if (!$user) {
        json_fail('No fue posible obtener el usuario', 500);
    }

    session_regenerate_id(true);

    $_SESSION['userData'] = array(
        'id' => (int)$user['id'],
        'oauth_uid' => $user['oauth_uid'],
        'mail' => $user['mail'],
        'name' => $user['name'],
        'lastname' => $user['lastname'],
        'picture' => $user['picture'],
        'cod_privilegio' => (int)$user['cod_privilegio'],
        'estado' => (int)$user['estado']
    );

    $_SESSION['access_token'] = $tokenID;

    if ((int)$user['estado'] !== 1 || (int)$user['cod_privilegio'] <= 0) {
        json_restricted('Usuario autenticado pero no autorizado');
    }

    json_ok();

} catch (Exception $e) {
    json_fail('Error interno: ' . $e->getMessage(), 500);
}