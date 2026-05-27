<?php
namespace App\Services;

use App\Core\Auth;
use App\Models\AuditLog;
use Throwable;

class AuditService
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_hash',
        'token',
        'access_token',
        'refresh_token',
        'client_secret',
        'secret',
        'api_key',
        'authorization',
        'csrf',
        '_csrf',
    ];

    public static function log(array $data): void
    {
        try {
            $user = Auth::user();

            $payload = array_merge([
                'user_id' => $user['id'] ?? null,
                'user_name' => $user['nombre'] ?? null,
                'user_role' => $user['rol'] ?? null,
                'ip_address' => self::ip(),
                'user_agent' => self::userAgent(),
                'url' => self::safeUrl(),
                'method' => $_SERVER['REQUEST_METHOD'] ?? null,
                'severity' => 'info',
                'status' => 'success',
            ], $data);

            $payload['old_values'] = self::sanitizeValue($payload['old_values'] ?? null);
            $payload['new_values'] = self::sanitizeValue($payload['new_values'] ?? null);

            (new AuditLog())->create($payload);
        } catch (Throwable $e) {
            error_log('[AUDIT_LOG_ERROR] ' . $e->getMessage());
        }
    }

    public static function businessEvent(
        string $action,
        string $module,
        string $entityType,
        $entityId,
        string $description,
        array $oldValues = [],
        array $newValues = [],
        string $severity = 'info'
    ): void {
        self::log([
            'action' => $action,
            'module' => $module,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'severity' => $severity,
            'status' => 'success',
        ]);
    }

    public static function logChanges(
        string $action,
        string $module,
        string $entityType,
        $entityId,
        string $descriptionPrefix,
        array $before,
        array $after,
        array $fields,
        string $severity = 'info'
    ): void {
        $oldValues = [];
        $newValues = [];

        foreach ($fields as $key => $label) {
            $old = $before[$key] ?? null;
            $new = $after[$key] ?? null;

            if ((string)$old !== (string)$new) {
                $oldValues[$label] = $old;
                $newValues[$label] = $new;
            }
        }

        if (!$oldValues && !$newValues) {
            return;
        }

        $changedLabels = implode(', ', array_keys($newValues));
        $description = trim($descriptionPrefix . ' Campos modificados: ' . $changedLabels);

        self::businessEvent(
            $action,
            $module,
            $entityType,
            $entityId,
            $description,
            $oldValues,
            $newValues,
            $severity
        );
    }

    private static function ip(): ?string
    {
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? null;

        if (is_string($remoteAddr) && filter_var($remoteAddr, FILTER_VALIDATE_IP)) {
            return $remoteAddr;
        }

        return null;
    }

    private static function userAgent(): ?string
    {
        $userAgent = trim((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));

        if ($userAgent === '') {
            return null;
        }

        return mb_substr($userAgent, 0, 255, 'UTF-8');
    }

    private static function safeUrl(): ?string
    {
        $uri = (string)($_SERVER['REQUEST_URI'] ?? '');

        if ($uri === '') {
            return null;
        }

        $path = parse_url($uri, PHP_URL_PATH);

        return $path ?: null;
    }

    private static function sanitizeValue($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        $clean = [];

        foreach ($value as $key => $item) {
            if (self::isSensitiveKey((string)$key)) {
                $clean[$key] = '[FILTERED]';
                continue;
            }

            if (is_array($item)) {
                $clean[$key] = self::sanitizeValue($item);
            } else {
                $clean[$key] = $item;
            }
        }

        return $clean;
    }

    private static function isSensitiveKey(string $key): bool
    {
        $key = mb_strtolower($key, 'UTF-8');

        foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
            if (strpos($key, $sensitiveKey) !== false) {
                return true;
            }
        }

        return false;
    }
}