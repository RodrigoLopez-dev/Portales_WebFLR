<?php
namespace App\Models;

use App\Core\Model;

class Plan extends Model
{
    public function all(array $filters = []): array
    {
        $where = " WHERE 1=1 ";
        $params = [];

        if (!empty($filters['q'])) {
            $where .= " AND (r.codigo LIKE :q OR r.titulo LIKE :q OR pp.nombre LIKE :q OR pp.descripcion LIKE :q)";
            $params['q'] = '%' . trim((string)$filters['q']) . '%';
        }

        if (!empty($filters['phase_id'])) {
            $where .= " AND r.phase_id = :phase_id";
            $params['phase_id'] = (int)$filters['phase_id'];
        }

        if (!empty($filters['estado'])) {
            $where .= " AND pp.estado = :estado";
            $params['estado'] = trim((string)$filters['estado']);
        }

        if (!empty($filters['responsable_id'])) {
            $where .= " AND EXISTS (
                SELECT 1
                FROM project_tasks pt_resp
                WHERE pt_resp.project_plan_id = pp.id
                  AND pt_resp.responsable_id = :responsable_id
            )";
            $params['responsable_id'] = (int)$filters['responsable_id'];
        }

        if (!empty($filters['atrasados'])) {
            $where .= " AND (
                (pp.fecha_fin_plan IS NOT NULL AND pp.fecha_fin_plan < CURDATE() AND pp.estado <> 'cerrado' AND COALESCE(pp.avance_general, 0) < 100)
                OR EXISTS (
                    SELECT 1
                    FROM project_tasks pt_late
                    WHERE pt_late.project_plan_id = pp.id
                      AND pt_late.fecha_fin_plan IS NOT NULL
                      AND pt_late.fecha_fin_plan < CURDATE()
                      AND pt_late.estado <> 'completada'
                )
            )";
        }

        if (!empty($filters['sin_tareas'])) {
            $where .= " AND NOT EXISTS (
                SELECT 1
                FROM project_tasks pt_empty
                WHERE pt_empty.project_plan_id = pp.id
            )";
        }

        $sql = "SELECT 
                    pp.*,
                    r.codigo,
                    r.titulo AS proyecto,
                    r.fecha_requerida,
                    r.responsable_id AS request_responsable_id,
                    COALESCE(req_u.nombre, 'Sin responsable') AS responsable_solicitud,
                    COALESCE(ph.nombre, 'Sin fase') AS fase_nombre,

                    COALESCE(task_stats.total_tareas, 0) AS total_tareas,
                    COALESCE(task_stats.tareas_pendientes, 0) AS tareas_pendientes,
                    COALESCE(task_stats.tareas_en_progreso, 0) AS tareas_en_progreso,
                    COALESCE(task_stats.tareas_completadas, 0) AS tareas_completadas,
                    COALESCE(task_stats.tareas_atrasadas, 0) AS tareas_atrasadas,
                    COALESCE(task_stats.horas_estimadas, 0) AS horas_estimadas,
                    COALESCE(task_stats.horas_reales, 0) AS horas_reales,
                    COALESCE(milestone_stats.total_hitos, 0) AS total_hitos,
                    COALESCE(milestone_stats.hitos_pendientes, 0) AS hitos_pendientes,
                    COALESCE(milestone_stats.hitos_atrasados, 0) AS hitos_atrasados,

                    CASE
                        WHEN pp.fecha_fin_plan IS NOT NULL
                         AND pp.fecha_fin_plan < CURDATE()
                         AND pp.estado <> 'cerrado'
                         AND COALESCE(pp.avance_general, 0) < 100
                        THEN DATEDIFF(CURDATE(), pp.fecha_fin_plan)
                        ELSE 0
                    END AS dias_atraso_plan,

                    CASE
                        WHEN pp.fecha_fin_plan IS NOT NULL
                         AND pp.fecha_fin_plan >= CURDATE()
                         AND COALESCE(pp.avance_general, 0) < 100
                        THEN DATEDIFF(pp.fecha_fin_plan, CURDATE())
                        ELSE NULL
                    END AS dias_restantes

                 FROM project_plans pp
                 INNER JOIN requests r ON r.id = pp.request_id
                 LEFT JOIN users req_u ON req_u.id = r.responsable_id
                 LEFT JOIN project_phases ph ON ph.id = r.phase_id
                 LEFT JOIN (
                    SELECT
                        project_plan_id,
                        COUNT(*) AS total_tareas,
                        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) AS tareas_pendientes,
                        SUM(CASE WHEN estado = 'en_progreso' THEN 1 ELSE 0 END) AS tareas_en_progreso,
                        SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) AS tareas_completadas,
                        SUM(CASE WHEN fecha_fin_plan IS NOT NULL AND fecha_fin_plan < CURDATE() AND estado <> 'completada' THEN 1 ELSE 0 END) AS tareas_atrasadas,
                        COALESCE(SUM(duracion_estimada_horas), 0) AS horas_estimadas,
                        COALESCE(SUM(duracion_real_horas), 0) AS horas_reales
                    FROM project_tasks
                    GROUP BY project_plan_id
                 ) task_stats ON task_stats.project_plan_id = pp.id
                 LEFT JOIN (
                    SELECT
                        project_plan_id,
                        COUNT(*) AS total_hitos,
                        SUM(CASE WHEN estado <> 'completado' THEN 1 ELSE 0 END) AS hitos_pendientes,
                        SUM(CASE WHEN fecha_hito IS NOT NULL AND fecha_hito < CURDATE() AND estado <> 'completado' THEN 1 ELSE 0 END) AS hitos_atrasados
                    FROM project_milestones
                    GROUP BY project_plan_id
                 ) milestone_stats ON milestone_stats.project_plan_id = pp.id
                 " . $where . "
                 ORDER BY
                    CASE WHEN COALESCE(task_stats.tareas_atrasadas, 0) > 0 THEN 1 ELSE 0 END DESC,
                    CASE WHEN pp.fecha_fin_plan IS NOT NULL AND pp.fecha_fin_plan < CURDATE() AND COALESCE(pp.avance_general, 0) < 100 THEN 1 ELSE 0 END DESC,
                    pp.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function summary(array $plans): array
    {
        $total = count($plans);
        $atrasados = 0;
        $sinTareas = 0;
        $tareasPendientes = 0;
        $tareasAtrasadas = 0;
        $horasEstimadas = 0.0;
        $horasReales = 0.0;
        $avanceTotal = 0.0;

        foreach ($plans as $plan) {
            $atrasado = (int)($plan['dias_atraso_plan'] ?? 0) > 0 || (int)($plan['tareas_atrasadas'] ?? 0) > 0 || (int)($plan['hitos_atrasados'] ?? 0) > 0;
            if ($atrasado) {
                $atrasados++;
            }

            if ((int)($plan['total_tareas'] ?? 0) === 0) {
                $sinTareas++;
            }

            $tareasPendientes += (int)($plan['tareas_pendientes'] ?? 0);
            $tareasAtrasadas += (int)($plan['tareas_atrasadas'] ?? 0);
            $horasEstimadas += (float)($plan['horas_estimadas'] ?? 0);
            $horasReales += (float)($plan['horas_reales'] ?? 0);
            $avanceTotal += (float)($plan['avance_general'] ?? 0);
        }

        return [
            'total_planes' => $total,
            'planes_atrasados' => $atrasados,
            'planes_sin_tareas' => $sinTareas,
            'tareas_pendientes' => $tareasPendientes,
            'tareas_atrasadas' => $tareasAtrasadas,
            'horas_estimadas' => $horasEstimadas,
            'horas_reales' => $horasReales,
            'avance_promedio' => $total > 0 ? round($avanceTotal / $total, 1) : 0,
        ];
    }

    public function phases(): array
    {
        return $this->db->query(
            "SELECT id, nombre
             FROM project_phases
             WHERE activo = 1
             ORDER BY orden ASC, id ASC"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                pp.*,
                r.codigo,
                r.titulo AS proyecto,
                r.id AS request_id,
                COALESCE(ph.nombre, 'Sin fase') AS fase_nombre
             FROM project_plans pp
             INNER JOIN requests r ON r.id = pp.request_id
             LEFT JOIN project_phases ph ON ph.id = r.phase_id
             WHERE pp.id = :id
             LIMIT 1"
        );

        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByRequest(int $requestId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM project_plans
             WHERE request_id = :request_id
             LIMIT 1"
        );

        $stmt->execute(['request_id' => $requestId]);
        return $stmt->fetch() ?: null;
    }

    public function requestsWithoutPlan(): array
    {
        $stmt = $this->db->query(
            "SELECT 
                r.id,
                r.codigo,
                r.titulo,
                r.descripcion,
                r.fecha_requerida,
                r.esfuerzo_estimado_horas,
                r.porcentaje_avance,
                COALESCE(ph.nombre, 'Sin fase') AS fase_nombre,
                COALESCE(u.nombre, 'Sin responsable') AS responsable_nombre
            FROM requests r
            LEFT JOIN project_plans pp ON pp.request_id = r.id
            LEFT JOIN project_phases ph ON ph.id = r.phase_id
            LEFT JOIN users u ON u.id = r.responsable_id
            WHERE pp.id IS NULL
            AND ph.activo = 1
            AND ph.orden >= (
                    SELECT orden
                    FROM project_phases
                    WHERE nombre = 'Desarrollo'
                    AND activo = 1
                    LIMIT 1
            )
            ORDER BY ph.orden ASC, r.id DESC"
        );

        return $stmt->fetchAll();
    }

    public function isClosedForChanges(int $planId): bool
    {
        $plan = $this->find($planId);
        if (!$plan) {
            return true;
        }

        $estado = mb_strtolower(trim((string)($plan['estado'] ?? '')));
        return in_array($estado, ['cerrado', 'completado'], true);
    }

    public function requestCanBePlanned(int $requestId): bool
    {
        if ($requestId <= 0 || $this->findByRequest($requestId)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM requests r
             INNER JOIN project_phases ph ON ph.id = r.phase_id
             WHERE r.id = :request_id
               AND ph.activo = 1
               AND ph.orden >= (
                    SELECT orden
                    FROM project_phases
                    WHERE nombre = 'Desarrollo'
                      AND activo = 1
                    LIMIT 1
               )"
        );

        $stmt->execute(['request_id' => $requestId]);
        return (int)$stmt->fetchColumn() > 0;
    }


    public function create(array $data, int $userId): bool
    {
        $requestId = (int)($data['request_id'] ?? 0);

        if (!$this->requestCanBePlanned($requestId)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO project_plans (
                request_id,
                nombre,
                descripcion,
                fecha_inicio_plan,
                fecha_fin_plan,
                avance_general,
                estado,
                created_at,
                updated_at
            ) VALUES (
                :request_id,
                :nombre,
                :descripcion,
                :fecha_inicio_plan,
                :fecha_fin_plan,
                0,
                :estado,
                NOW(),
                NOW()
            )"
        );

        $ok = $stmt->execute([
            'request_id' => $requestId,
            'nombre' => trim($data['nombre'] ?? ''),
            'descripcion' => trim($data['descripcion'] ?? '') ?: null,
            'fecha_inicio_plan' => !empty($data['fecha_inicio_plan']) ? $data['fecha_inicio_plan'] : null,
            'fecha_fin_plan' => !empty($data['fecha_fin_plan']) ? $data['fecha_fin_plan'] : null,
            'estado' => $data['estado'] ?? 'planificado',
        ]);

        if ($ok) {
            $planId = (int)$this->db->lastInsertId();
            $this->log($planId, $userId, 'Creación de planificación', 'Se creó la planificación del proyecto.');
        }

        return $ok;
    }

    public function recalculateProgress(int $planId): void
    {

        // 🔥 SINCRONIZAR HITOS CON SUS TAREAS

        // 1. Marcar hitos como COMPLETADOS si todas sus tareas están completas
        $this->db->prepare("
            UPDATE project_milestones pm
            SET estado = 'completado'
            WHERE pm.project_plan_id = :plan_id
            AND NOT EXISTS (
                SELECT 1
                FROM project_tasks pt
                WHERE pt.milestone_id = pm.id
                AND pt.estado <> 'completada'
            )
        ")->execute(['plan_id' => $planId]);

        // 2. Marcar hitos como ATRASADOS si están vencidos y no completados
        $this->db->prepare("
            UPDATE project_milestones pm
            SET estado = 'atrasado'
            WHERE pm.project_plan_id = :plan_id
            AND pm.estado <> 'completado'
            AND pm.fecha_hito IS NOT NULL
            AND pm.fecha_hito < CURDATE()
        ")->execute(['plan_id' => $planId]);


        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) AS total_tareas,
                ROUND(COALESCE(AVG(avance), 0), 0) AS avance_promedio,
                SUM(CASE WHEN estado <> 'completada' THEN 1 ELSE 0 END) AS tareas_no_completadas,
                SUM(
                    CASE 
                        WHEN fecha_fin_plan IS NOT NULL
                        AND fecha_fin_plan < CURDATE()
                        AND estado <> 'completada'
                        THEN 1 
                        ELSE 0 
                    END
                ) AS tareas_atrasadas
            FROM project_tasks
            WHERE project_plan_id = :plan_id"
        );

        $stmt->execute(['plan_id' => $planId]);
        $stats = $stmt->fetch() ?: [];

        $totalTareas = (int)($stats['total_tareas'] ?? 0);
        $avance = (int)($stats['avance_promedio'] ?? 0);
        $tareasNoCompletadas = (int)($stats['tareas_no_completadas'] ?? 0);
        $tareasAtrasadas = (int)($stats['tareas_atrasadas'] ?? 0);

        if ($totalTareas === 0) {
            $estado = 'planificado';
            $avance = 0;
        } elseif ($tareasNoCompletadas === 0) {
            $estado = 'completado';
            $avance = 100;
        } elseif ($tareasAtrasadas > 0) {
            $estado = 'atrasado';
        } else {
            $estado = 'en_ejecucion';
        }

        $upd = $this->db->prepare(
            "UPDATE project_plans
            SET avance_general = :avance,
                estado = :estado,
                updated_at = NOW()
            WHERE id = :id"
        );

        $upd->execute([
            'avance' => $avance,
            'estado' => $estado,
            'id' => $planId,
        ]);
    }

    public function milestones(int $planId): array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM project_milestones
             WHERE project_plan_id = :plan_id
             ORDER BY orden ASC, id ASC"
        );
        $stmt->execute(['plan_id' => $planId]);
        return $stmt->fetchAll();
    }

    public function tasks(int $planId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                pt.*,
                COALESCE(u.nombre, 'Sin responsable') AS responsable_nombre,
                COALESCE(pm.nombre, 'Sin hito') AS milestone_nombre
             FROM project_tasks pt
             LEFT JOIN users u ON u.id = pt.responsable_id
             LEFT JOIN project_milestones pm ON pm.id = pt.milestone_id
             WHERE pt.project_plan_id = :plan_id
             ORDER BY pt.orden ASC, pt.id ASC"
        );
        $stmt->execute(['plan_id' => $planId]);
        return $stmt->fetchAll();
    }

    public function stats(int $planId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total_tareas,
                SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) AS pendientes,
                SUM(CASE WHEN estado = 'en_progreso' THEN 1 ELSE 0 END) AS en_progreso,
                SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) AS completadas,
                SUM(CASE WHEN fecha_fin_plan IS NOT NULL AND fecha_fin_plan < CURDATE() AND estado <> 'completada' THEN 1 ELSE 0 END) AS atrasadas,
                COALESCE(SUM(duracion_estimada_horas), 0) AS horas_estimadas,
                COALESCE(SUM(duracion_real_horas), 0) AS horas_reales
             FROM project_tasks
             WHERE project_plan_id = :plan_id"
        );
        $stmt->execute(['plan_id' => $planId]);
        return $stmt->fetch() ?: [];
    }

    public function users(): array
    {
        return $this->db->query(
            "SELECT id, nombre
             FROM users
             WHERE estado = 1
             ORDER BY nombre ASC"
        )->fetchAll();
    }

    public function log(int $planId, int $userId, string $accion, ?string $detalle = null): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO project_plan_history (
                project_plan_id,
                user_id,
                accion,
                detalle,
                created_at
            ) VALUES (
                :project_plan_id,
                :user_id,
                :accion,
                :detalle,
                NOW()
            )"
        );

        $stmt->execute([
            'project_plan_id' => $planId,
            'user_id' => $userId,
            'accion' => $accion,
            'detalle' => $detalle
        ]);
    }

    public function history(int $planId): array
    {
        $stmt = $this->db->prepare(
            "SELECT pph.*, u.nombre
             FROM project_plan_history pph
             INNER JOIN users u ON u.id = pph.user_id
             WHERE pph.project_plan_id = :plan_id
             ORDER BY pph.id DESC"
        );
        $stmt->execute(['plan_id' => $planId]);
        return $stmt->fetchAll();
    }
}
