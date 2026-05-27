<?php
namespace App\Models;

use App\Core\Model;

class Phase extends Model
{
    public function all(): array
    {
        return $this->db->query(
            "SELECT id, nombre, orden
             FROM project_phases
             WHERE activo = 1
             ORDER BY orden"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, nombre, orden
             FROM project_phases
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function next(int $currentId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT p2.*
             FROM project_phases p1
             JOIN project_phases p2 ON p2.orden = p1.orden + 1
             WHERE p1.id = :id"
        );
        $stmt->execute(['id' => $currentId]);
        return $stmt->fetch() ?: null;
    }
}