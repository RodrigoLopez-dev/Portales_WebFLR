<?php
namespace App\Models;

use App\Core\Model;

class Milestone extends Model
{
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO project_milestones (
                project_plan_id,
                nombre,
                descripcion,
                fecha_hito,
                estado,
                orden,
                created_at,
                updated_at
            ) VALUES (
                :project_plan_id,
                :nombre,
                :descripcion,
                :fecha_hito,
                :estado,
                :orden,
                NOW(),
                NOW()
            )"
        );

        return $stmt->execute([
            'project_plan_id' => (int)$data['project_plan_id'],
            'nombre' => trim($data['nombre'] ?? ''),
            'descripcion' => trim($data['descripcion'] ?? '') ?: null,
            'fecha_hito' => !empty($data['fecha_hito']) ? $data['fecha_hito'] : null,
            'estado' => $data['estado'] ?? 'pendiente',
            'orden' => (int)($data['orden'] ?? 0),
        ]);
    }
}