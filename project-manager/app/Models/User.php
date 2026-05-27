<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.nombre, u.email, u.google_id, u.auth_provider, u.avatar_url, u.password_hash, u.estado, r.nombre AS rol, u.rol_id
             FROM users u
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.email = :email AND u.estado = 1
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmailAnyStatus(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.nombre, u.email, u.google_id, u.auth_provider, u.avatar_url, u.password_hash, u.estado, r.nombre AS rol, u.rol_id
             FROM users u
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function findByGoogleId(string $googleId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.nombre, u.email, u.google_id, u.auth_provider, u.avatar_url, u.password_hash, u.estado, r.nombre AS rol, u.rol_id
             FROM users u
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.google_id = :google_id AND u.estado = 1
             LIMIT 1'
        );
        $stmt->execute(['google_id' => $googleId]);
        return $stmt->fetch() ?: null;
    }
    public function findForSession(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.nombre, u.email, u.google_id, u.auth_provider, u.avatar_url, u.password_hash, u.estado, r.nombre AS rol, u.rol_id
             FROM users u
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.id = :id AND u.estado = 1
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }


    public function linkGoogleAccount(int $userId, string $googleId, ?string $avatarUrl = null): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET google_id = :google_id,
                 auth_provider = :auth_provider,
                 avatar_url = :avatar_url
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $userId,
            'google_id' => $googleId,
            'auth_provider' => 'google',
            'avatar_url' => $avatarUrl ?: null,
        ]);
    }

    public function allActive(): array
    {
        return $this->db
            ->query('SELECT id, nombre FROM users WHERE estado = 1 ORDER BY nombre')
            ->fetchAll();
    }

    public function findAllWithRoles(array $filters = []): array
    {
        $sql = 'SELECT 
                    u.id,
                    u.nombre,
                    u.email,
                    u.google_id,
                    u.auth_provider,
                    u.avatar_url,
                    u.password_hash,
                    u.estado,
                    u.rol_id,
                    r.nombre AS rol_principal,
                    COALESCE(
                        GROUP_CONCAT(DISTINCT r2.nombre ORDER BY r2.nombre SEPARATOR \', \'),
                        r.nombre
                    ) AS rol,
                    COALESCE(
                        GROUP_CONCAT(DISTINCT r2.nombre ORDER BY r2.nombre SEPARATOR \', \'),
                        r.nombre
                    ) AS roles
                FROM users u
                INNER JOIN roles r ON r.id = u.rol_id
                LEFT JOIN user_roles ur ON ur.user_id = u.id
                LEFT JOIN roles r2 ON r2.id = ur.role_id
                WHERE 1=1';

        $params = [];

        if (!empty($filters['q'])) {
            $sql .= ' AND (u.nombre LIKE :q OR u.email LIKE :q)';
            $params['q'] = '%' . trim((string)$filters['q']) . '%';
        }

        if (!empty($filters['rol_id'])) {
            $sql .= ' AND (ur.role_id = :rol_id OR u.rol_id = :rol_id)';
            $params['rol_id'] = (int)$filters['rol_id'];
        }

        if ($filters['estado'] !== null && $filters['estado'] !== '') {
            $sql .= ' AND u.estado = :estado';
            $params['estado'] = (int)$filters['estado'];
        }

        if (!empty($filters['access'])) {
            if ($filters['access'] === 'google') {
                $sql .= ' AND u.google_id IS NOT NULL AND u.google_id <> ""';
            }

            if ($filters['access'] === 'local') {
                $sql .= ' AND (u.google_id IS NULL OR u.google_id = "")';
            }
        }

        $sql .= ' GROUP BY
                    u.id,
                    u.nombre,
                    u.email,
                    u.google_id,
                    u.auth_provider,
                    u.avatar_url,
                    u.password_hash,
                    u.estado,
                    u.rol_id,
                    r.nombre
                  ORDER BY u.estado DESC, roles ASC, u.nombre ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function roles(): array
    {
        return $this->db
            ->query('SELECT id, nombre FROM roles ORDER BY nombre')
            ->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, email, google_id, auth_provider, avatar_url, rol_id, estado
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function existsByEmail(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return (bool)$stmt->fetch();
    }

    public function existsByEmailForAnotherUser(string $email, int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1'
        );
        $stmt->execute([
            'email' => $email,
            'id' => $id,
        ]);
        return (bool)$stmt->fetch();
    }

    public function createUser(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (nombre, email, google_id, auth_provider, avatar_url, password_hash, rol_id, estado)
             VALUES (:nombre, :email, :google_id, :auth_provider, :avatar_url, :password_hash, :rol_id, :estado)'
        );

        return $stmt->execute([
            'nombre' => trim($data['nombre']),
            'email' => trim($data['email']),
            'google_id' => $data['google_id'] ?? null,
            'auth_provider' => $data['auth_provider'] ?? 'local',
            'avatar_url' => $data['avatar_url'] ?? null,
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'rol_id' => (int)$data['rol_id'],
            'estado' => (int)$data['estado'],
        ]);
    }

    public function updateUser(array $data): bool
    {
        if (!empty($data['password'])) {
            $stmt = $this->db->prepare(
                'UPDATE users
                 SET nombre = :nombre,
                     email = :email,
                     password_hash = :password_hash,
                     rol_id = :rol_id,
                     estado = :estado
                 WHERE id = :id'
            );

            return $stmt->execute([
                'id' => (int)$data['id'],
                'nombre' => trim($data['nombre']),
                'email' => trim($data['email']),
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'rol_id' => (int)$data['rol_id'],
                'estado' => (int)$data['estado'],
            ]);
        }

        $stmt = $this->db->prepare(
            'UPDATE users
             SET nombre = :nombre,
                 email = :email,
                 rol_id = :rol_id,
                 estado = :estado
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => (int)$data['id'],
            'nombre' => trim($data['nombre']),
            'email' => trim($data['email']),
            'rol_id' => (int)$data['rol_id'],
            'estado' => (int)$data['estado'],
        ]);
    }

    public function toggleStatus(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET estado = CASE WHEN estado = 1 THEN 0 ELSE 1 END
             WHERE id = :id'
        );

        return $stmt->execute(['id' => $id]);
    }

    public function unlinkGoogle(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET google_id = NULL,
                 auth_provider = "local",
                 avatar_url = NULL
             WHERE id = :id'
        );

        return $stmt->execute(['id' => $id]);
    }

    public function rolesByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*
            FROM user_roles ur
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE ur.user_id = :user_id
            ORDER BY r.nombre ASC"
        );

        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function syncRoles(int $userId, array $roleIds): void
    {
        $this->db->prepare("DELETE FROM user_roles WHERE user_id = :user_id")
            ->execute(['user_id' => $userId]);

        $stmt = $this->db->prepare(
            "INSERT INTO user_roles (user_id, role_id)
            VALUES (:user_id, :role_id)"
        );

        foreach ($roleIds as $roleId) {
            $roleId = (int)$roleId;

            if ($roleId > 0) {
                $stmt->execute([
                    'user_id' => $userId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function lastCreatedId(): int
    {
        return (int)$this->db->lastInsertId();
    }

}
