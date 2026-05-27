<?php
namespace App\Models;

use App\Core\Model;

class Task extends Model
{
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO project_tasks (
                project_plan_id,
                milestone_id,
                parent_task_id,
                nombre,
                descripcion,
                responsable_id,
                estado,
                prioridad,
                fecha_inicio_plan,
                fecha_fin_plan,
                fecha_inicio_real,
                fecha_fin_real,
                duracion_estimada_horas,
                duracion_real_horas,
                avance,
                es_hito,
                orden,
                created_at,
                updated_at
            ) VALUES (
                :project_plan_id,
                :milestone_id,
                :parent_task_id,
                :nombre,
                :descripcion,
                :responsable_id,
                :estado,
                :prioridad,
                :fecha_inicio_plan,
                :fecha_fin_plan,
                :fecha_inicio_real,
                :fecha_fin_real,
                :duracion_estimada_horas,
                :duracion_real_horas,
                :avance,
                :es_hito,
                :orden,
                NOW(),
                NOW()
            )"
        );

        return $stmt->execute([
            'project_plan_id' => (int)$data['project_plan_id'],
            'milestone_id' => !empty($data['milestone_id']) ? (int)$data['milestone_id'] : null,
            'parent_task_id' => !empty($data['parent_task_id']) ? (int)$data['parent_task_id'] : null,
            'nombre' => trim($data['nombre'] ?? ''),
            'descripcion' => trim($data['descripcion'] ?? '') ?: null,
            'responsable_id' => !empty($data['responsable_id']) ? (int)$data['responsable_id'] : null,
            'estado' => $data['estado'] ?? 'pendiente',
            'prioridad' => $data['prioridad'] ?? 'media',
            'fecha_inicio_plan' => !empty($data['fecha_inicio_plan']) ? $data['fecha_inicio_plan'] : null,
            'fecha_fin_plan' => !empty($data['fecha_fin_plan']) ? $data['fecha_fin_plan'] : null,
            'fecha_inicio_real' => !empty($data['fecha_inicio_real']) ? $data['fecha_inicio_real'] : null,
            'fecha_fin_real' => !empty($data['fecha_fin_real']) ? $data['fecha_fin_real'] : null,
            'duracion_estimada_horas' => (float)($data['duracion_estimada_horas'] ?? 0),
            'duracion_real_horas' => (float)($data['duracion_real_horas'] ?? 0),
            'avance' => (int)($data['avance'] ?? 0),
            'es_hito' => !empty($data['es_hito']) ? 1 : 0,
            'orden' => (int)($data['orden'] ?? 0),
        ]);
    }

    public function update(array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE project_tasks
             SET
                milestone_id = :milestone_id,
                nombre = :nombre,
                descripcion = :descripcion,
                responsable_id = :responsable_id,
                estado = :estado,
                prioridad = :prioridad,
                fecha_inicio_plan = :fecha_inicio_plan,
                fecha_fin_plan = :fecha_fin_plan,
                fecha_inicio_real = :fecha_inicio_real,
                fecha_fin_real = :fecha_fin_real,
                duracion_estimada_horas = :duracion_estimada_horas,
                duracion_real_horas = :duracion_real_horas,
                avance = :avance,
                updated_at = NOW()
             WHERE id = :id"
        );

        return $stmt->execute([
            'id' => (int)$data['id'],
            'milestone_id' => !empty($data['milestone_id']) ? (int)$data['milestone_id'] : null,
            'nombre' => trim($data['nombre'] ?? ''),
            'descripcion' => trim($data['descripcion'] ?? '') ?: null,
            'responsable_id' => !empty($data['responsable_id']) ? (int)$data['responsable_id'] : null,
            'estado' => $data['estado'] ?? 'pendiente',
            'prioridad' => $data['prioridad'] ?? 'media',
            'fecha_inicio_plan' => !empty($data['fecha_inicio_plan']) ? $data['fecha_inicio_plan'] : null,
            'fecha_fin_plan' => !empty($data['fecha_fin_plan']) ? $data['fecha_fin_plan'] : null,
            'fecha_inicio_real' => !empty($data['fecha_inicio_real']) ? $data['fecha_inicio_real'] : null,
            'fecha_fin_real' => !empty($data['fecha_fin_real']) ? $data['fecha_fin_real'] : null,
            'duracion_estimada_horas' => (float)($data['duracion_estimada_horas'] ?? 0),
            'duracion_real_horas' => (float)($data['duracion_real_horas'] ?? 0),
            'avance' => (int)($data['avance'] ?? 0),
        ]);
    }
}