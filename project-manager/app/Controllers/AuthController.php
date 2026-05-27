<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;
use App\Services\AuditService;

class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login', [
            'user' => null,
        ]);
    }

    public function authenticate(): void
    {
        verify_csrf();

        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            flash('error', 'Debe ingresar correo y contraseña.');
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            flash('error', 'Credenciales inválidas.');
            $this->redirect('/login');
        }

        unset($user['password_hash']);
        Auth::attempt($user);

        AuditService::log([
            'action' => 'login',
            'module' => 'auth',
            'entity_type' => 'users',
            'entity_id' => (string) $user['id'],
            'description' => 'Inicio de sesión exitoso',
        ]);

        flash('success', 'Bienvenido, ' . $user['nombre'] . '.');
        $this->redirect('/dashboard');
    }

    public function googleRedirect(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $google = require __DIR__ . '/../../config/google.php';

        if (empty($google['client_id']) || empty($google['client_secret']) || empty($google['redirect_uri'])) {
            flash('error', 'La configuración de Google Login está incompleta.');
            $this->redirect('/login');
        }

        $state = bin2hex(random_bytes(32));
        $_SESSION['google_oauth_state'] = $state;

        $params = [
            'client_id' => $google['client_id'],
            'redirect_uri' => $google['redirect_uri'],
            'response_type' => 'code',
            'scope' => $google['scopes'],
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ];

        $url = $google['authorization_endpoint'] . '?' . http_build_query($params);

        header('Location: ' . $url);
        exit;
    }

    public function googleCallback(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $google = require __DIR__ . '/../../config/google.php';

        $state = (string) ($_GET['state'] ?? '');
        $code = (string) ($_GET['code'] ?? '');
        $error = (string) ($_GET['error'] ?? '');

        if ($error !== '') {
            flash('error', 'Google Login fue cancelado o rechazado.');
            $this->redirect('/login');
        }

        if (
            empty($_SESSION['google_oauth_state']) ||
            $state === '' ||
            !hash_equals($_SESSION['google_oauth_state'], $state)
        ) {
            unset($_SESSION['google_oauth_state']);
            flash('error', 'No fue posible validar la sesión de Google.');
            $this->redirect('/login');
        }

        unset($_SESSION['google_oauth_state']);

        if ($code === '') {
            flash('error', 'Google no devolvió un código de autorización.');
            $this->redirect('/login');
        }

        $tokenResponse = $this->httpPostForm($google['token_endpoint'], [
            'code' => $code,
            'client_id' => $google['client_id'],
            'client_secret' => $google['client_secret'],
            'redirect_uri' => $google['redirect_uri'],
            'grant_type' => 'authorization_code',
        ]);

        if (($tokenResponse['http_code'] ?? 0) !== 200 || empty($tokenResponse['json']['access_token'])) {
            flash('error', 'No fue posible obtener el token de Google.');
            $this->redirect('/login');
        }

        $accessToken = (string) $tokenResponse['json']['access_token'];

        $userInfoResponse = $this->httpGetJson(
            $google['userinfo_endpoint'],
            ['Authorization: Bearer ' . $accessToken]
        );

        if (($userInfoResponse['http_code'] ?? 0) !== 200 || empty($userInfoResponse['json'])) {
            flash('error', 'No fue posible obtener la información del usuario desde Google.');
            $this->redirect('/login');
        }

        $googleUser = $userInfoResponse['json'];

        $googleId = trim((string) ($googleUser['sub'] ?? ''));
        $email = trim((string) ($googleUser['email'] ?? ''));
        $emailVerified = (bool) ($googleUser['email_verified'] ?? false);
        $picture = trim((string) ($googleUser['picture'] ?? ''));

        if ($googleId === '' || $email === '' || !$emailVerified) {
            flash('error', 'La cuenta de Google no entregó un correo válido y verificado.');
            $this->redirect('/login');
        }

        $allowedDomain = trim((string) ($google['allowed_domain'] ?? ''));

        if ($allowedDomain !== '') {
            $emailDomain = substr(strrchr($email, '@') ?: '', 1);

            if ($emailDomain === '' || strcasecmp($emailDomain, $allowedDomain) !== 0) {
                flash('error', 'Solo se permiten correos autorizados para ingresar al sistema.');
                $this->redirect('/login');
            }
        }

        $userModel = new User();

        $user = $userModel->findByGoogleId($googleId);

        if (!$user) {
            $user = $userModel->findByEmailAnyStatus($email);

            if (!$user) {
                flash('error', 'Tu cuenta no está autorizada en este sistema.');
                $this->redirect('/login');
            }

            if ((int) ($user['estado'] ?? 0) !== 1) {
                flash('error', 'Tu usuario existe, pero está inactivo.');
                $this->redirect('/login');
            }

            $userModel->linkGoogleAccount((int) $user['id'], $googleId, $picture);
            $user = $userModel->findByGoogleId($googleId);
        }

        if (!$user) {
            flash('error', 'No fue posible vincular tu cuenta de Google.');
            $this->redirect('/login');
        }

        if ((int) ($user['estado'] ?? 0) !== 1) {
            flash('error', 'Tu usuario está inactivo.');
            $this->redirect('/login');
        }

        unset($user['password_hash']);

        Auth::attempt($user);

        AuditService::log([
            'action' => 'google_login',
            'module' => 'auth',
            'entity_type' => 'users',
            'entity_id' => (string) $user['id'],
            'description' => 'Inicio de sesión exitoso con Google',
        ]);

        flash('success', 'Bienvenido, ' . $user['nombre'] . '.');
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        AuditService::log([
            'action' => 'logout',
            'module' => 'auth',
            'description' => 'Cierre de sesión',
        ]);

        flash('success', 'Sesión cerrada correctamente.');
        $this->redirect('/login');
    }

    private function httpPostForm(string $url, array $data): array
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
        ]);

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        return [
            'http_code' => $httpCode,
            'body' => $body,
            'json' => json_decode((string) $body, true),
            'error' => $error,
        ];
    }

    private function httpGetJson(string $url, array $headers = []): array
    {
        $ch = curl_init($url);

        $baseHeaders = array_merge([
            'Accept: application/json',
        ], $headers);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => $baseHeaders,
        ]);

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        return [
            'http_code' => $httpCode,
            'body' => $body,
            'json' => json_decode((string) $body, true),
            'error' => $error,
        ];
    }
}