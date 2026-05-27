<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Notification extends Model
{
    public function create(int $userId, string $titulo, string $mensaje): bool
    {
        if ($userId <= 0 || trim($titulo) === '' || trim($mensaje) === '') {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, titulo, mensaje, leida, created_at)
             VALUES (:user_id, :titulo, :mensaje, 0, NOW())'
        );

        return $stmt->execute([
            'user_id' => $userId,
            'titulo' => trim($titulo),
            'mensaje' => trim($mensaje),
        ]);
    }


    public function paginateByUser(int $userId, int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(5, min(50, $perPage));
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->db->prepare(
            'SELECT COUNT(*)
            FROM notifications
            WHERE user_id = :user_id'
        );
        $countStmt->execute(['user_id' => $userId]);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare(
            'SELECT id, user_id, titulo, mensaje, leida, created_at
            FROM notifications
            WHERE user_id = :user_id
            ORDER BY created_at DESC, id DESC
            LIMIT :limit OFFSET :offset'
        );

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'rows' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => max(1, (int)ceil($total / $perPage)),
        ];
    }



    public function latestByUser(int $userId, int $limit = 50): array
    {
        $limit = max(1, min(100, $limit));

        $stmt = $this->db->prepare(
            'SELECT id, user_id, titulo, mensaje, leida, created_at
             FROM notifications
             WHERE user_id = :user_id
             ORDER BY created_at DESC, id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function unreadByUser(int $userId, int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));

        $stmt = $this->db->prepare(
            'SELECT id, user_id, titulo, mensaje, leida, created_at
             FROM notifications
             WHERE user_id = :user_id AND leida = 0
             ORDER BY created_at DESC, id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM notifications
             WHERE user_id = :user_id AND leida = 0'
        );
        $stmt->execute(['user_id' => $userId]);

        return (int)$stmt->fetchColumn();
    }

    public function markAsRead(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications
             SET leida = 1
             WHERE id = :id AND user_id = :user_id'
        );

        return $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public function markAllAsRead(int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications
             SET leida = 1
             WHERE user_id = :user_id AND leida = 0'
        );

        return $stmt->execute(['user_id' => $userId]);
    }
}
