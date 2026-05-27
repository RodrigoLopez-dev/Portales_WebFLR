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

            // NUEVOS
            'requests.phase.view',
            'requests.phase.change',
            'documents.view',
            'documents.upload.phase',
            'documents.review'
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
            'documents.upload.phase'
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
            'documents.view'
        ],

        'QA' => [
            'dashboard.view',
            'requests.view',
            'requests.comment',
            'requests.change_status',
            'requests.kanban',

            'requests.phase.view',
            'documents.view',
            'documents.review'
        ],

        'Solicitante' => [
            'dashboard.view',
            'requests.view',
            'requests.create',
            'requests.comment',

            'requests.phase.view',
            'documents.view'
        ],
    ];

    public static function attempt(array $user): void
    {
        $_SESSION['user'] = $user;
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_destroy();
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['rol'] ?? null;
    }

    public static function can(string $permission): bool
    {
        $role = self::role();

        if (!$role || !isset(self::PERMISSIONS[$role])) {
            return false;
        }

        $permissions = self::PERMISSIONS[$role];

        if (in_array('*', $permissions, true)) {
            return true;
        }

        return in_array($permission, $permissions, true);
    }

    public static function requirePermission(string $permission): void
    {
        if (!self::check() || !self::can($permission)) {
            http_response_code(403);
            die('No tienes permisos para realizar esta acción.');
        }
    }
}
