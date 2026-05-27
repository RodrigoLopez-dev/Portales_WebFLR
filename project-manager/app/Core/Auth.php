<?php
namespace App\Core;

class Auth
{
    private const PERMISSIONS = [
        'Administrador' => ['*'],

        'Jefe de Proyecto' => [
            'dashboard.view',

            'requests.view',
            'requests.create',
            'requests.edit',
            'requests.comment',
            'requests.change_status',
            'requests.upload',
            'requests.kanban',
            'requests.phase.view',
            'requests.phase.change',

            'planning.view',
            'planning.create',
            'planning.edit',
            'planning.export',

            'documents.view',
            'documents.upload.phase',
            'documents.review',

            'resources.view',
            'resources.create',
            'resources.edit',
            'resources.delete',
        ],

        'Analista' => [
            'dashboard.view',
            'requests.view',
            'requests.create',
            'requests.edit',
            'requests.comment',
            'requests.upload',
            'requests.kanban',
            'requests.phase.view',
            'documents.view',
            'documents.upload.phase',
            'resources.view',
            'resources.create',
            'resources.edit'
        ],

        'Desarrollador' => [
            'dashboard.view',
            'requests.view',
            'requests.edit',
            'requests.comment',
            'requests.upload',
            'requests.change_status',
            'requests.kanban',
            'requests.phase.view',
            'documents.view',
            'resources.view',
            'resources.create'
        ],

        'QA' => [
            'dashboard.view',
            'requests.view',
            'requests.comment',
            'requests.change_status',
            'requests.kanban',
            'requests.phase.view',
            'documents.view',
            'documents.review',
            'resources.view'
        ],

        'Solicitante' => [
            'dashboard.view',
            'requests.view',
            'requests.create',
            'requests.comment',
            'requests.phase.view',
            'documents.view',
            'resources.view',
            'resources.create'
        ],
    ];

    public static function attempt(array $user): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userModel = new \App\Models\User();
        $roles = $userModel->rolesByUser((int) $user['id']);

        if (empty($roles) && !empty($user['rol_id'])) {
            $roles = [
                [
                    'id' => $user['rol_id'],
                    'nombre' => $user['rol'] ?? $user['rol_nombre'] ?? '',
                ]
            ];
        }

        $user['roles'] = $roles;
        $user['role_names'] = array_column($roles, 'nombre');

        session_regenerate_id(true);

        $_SESSION['user'] = $user;

        //session_regenerate_id(true);
        //$_SESSION['user'] = $user;
    }

    public static function refreshUser(int $userId): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userModel = new \App\Models\User();
        $user = $userModel->findForSession($userId);

        if (!$user) {
            self::logout();
            return false;
        }

        self::attempt($user);
        return true;
    }

    public static function user(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return isset($_SESSION['user']);
    }

    public static function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?? '/',
                'domain' => $params['domain'] ?? '',
                'secure' => (bool) ($params['secure'] ?? false),
                'httponly' => (bool) ($params['httponly'] ?? true),
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();
    }

    public static function role(): ?string
    {
        $user = self::user();
        return $user['rol'] ?? null;
    }

    static function can(string $p): bool
    {
        $user = self::user();

        if (!$user) {
            return false;
        }

        // Roles múltiples nuevos
        $roles = $user['role_names'] ?? [];

        // Compatibilidad con sistema antiguo
        if (empty($roles) && !empty($user['rol'])) {
            $roles = [$user['rol']];
        }

        foreach ($roles as $roleName) {
            if (!isset(self::PERMISSIONS[$roleName])) {
                continue;
            }

            $permissions = self::PERMISSIONS[$roleName];

            if (in_array('*', $permissions, true) || in_array($p, $permissions, true)) {
                return true;
            }
        }

        return false;
    }

    public static function requirePermission(string $permission): void
    {
        if (!self::check()) {
            header('Location: ' . base_url('/login'));
            exit;
        }

        if (!self::can($permission)) {
            http_response_code(403);
            die('No tienes permisos para realizar esta acción.');
        }
    }
}
