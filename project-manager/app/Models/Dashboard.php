<?php
namespace App\Models;

use App\Core\Model;

class Dashboard extends Model
{
    public function totals(): array
    {
        $sql = "
            SELECT
                SUM(CASE WHEN s.es_final = 0 THEN 1 ELSE 0 END) AS activas,
                SUM(CASE WHEN LOWER(s.nombre) = 'en desarrollo' THEN 1 ELSE 0 END) AS desarrollo,
                SUM(CASE WHEN LOWER(s.nombre) = 'en pruebas' THEN 1 ELSE 0 END) AS pruebas,
                SUM(CASE WHEN LOWER(s.nombre) = 'bloqueada' OR NULLIF(TRIM(r.motivo_bloqueo), '') IS NOT NULL THEN 1 ELSE 0 END) AS bloqueadas,
                SUM(CASE WHEN r.fecha_requerida IS NOT NULL AND r.fecha_requerida < CURDATE() AND s.es_final = 0 THEN 1 ELSE 0 END) AS atrasadas,
                SUM(CASE WHEN LOWER(s.nombre) = 'cerrada'
                          AND MONTH(r.updated_at) = MONTH(CURDATE())
                          AND YEAR(r.updated_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS cerradas_mes,
                COALESCE(SUM(r.esfuerzo_estimado_horas), 0) AS horas_estimadas,
                COALESCE(SUM(r.esfuerzo_real_horas), 0) AS horas_reales,
                ROUND(COALESCE(AVG(r.porcentaje_avance), 0), 1) AS avance_promedio,
                SUM(CASE WHEN EXISTS (
                        SELECT 1
                        FROM request_attachments ra
                        WHERE ra.request_id = r.id
                          AND COALESCE(ra.estado_documento, 'pendiente') = 'pendiente'
                    ) THEN 1 ELSE 0 END) AS pendientes_doc,
                SUM(CASE WHEN EXISTS (
                        SELECT 1
                        FROM request_attachments ra
                        WHERE ra.request_id = r.id
                          AND COALESCE(ra.estado_documento, '') = 'rechazado'
                    ) THEN 1 ELSE 0 END) AS rechazados_doc,
                SUM(CASE WHEN COALESCE(r.requiere_formalizacion, 0) = 1
                          AND COALESCE(r.phase_id, 0) = 3 THEN 1 ELSE 0 END) AS formalizacion_pendiente
            FROM requests r
            INNER JOIN statuses s ON s.id = r.estado_id
        ";

        $row = $this->db->query($sql)->fetch() ?: [];

        $estimadas = (float)($row['horas_estimadas'] ?? 0);
        $reales = (float)($row['horas_reales'] ?? 0);

        $row['desviacion_horas_pct'] = $estimadas > 0
            ? round((($reales - $estimadas) / $estimadas) * 100, 1)
            : 0;

        return $row;
    }

    public function requestsByStatus(): array
    {
        return $this->db->query(
            "SELECT 
                s.nombre AS estado,
                s.color,
                COUNT(*) AS total
             FROM requests r
             INNER JOIN statuses s ON s.id = r.estado_id
             GROUP BY s.id, s.nombre, s.color, s.orden
             ORDER BY s.orden ASC"
        )->fetchAll();
    }

    public function requestsByPhase(): array
    {
        return $this->db->query(
            "SELECT 
                COALESCE(pp.nombre, 'Sin fase') AS fase,
                COUNT(*) AS total,
                ROUND(AVG(COALESCE(r.porcentaje_avance, 0)), 1) AS avance_promedio
             FROM requests r
             LEFT JOIN project_phases pp ON pp.id = r.phase_id
             GROUP BY pp.id, pp.nombre, pp.orden
             ORDER BY COALESCE(pp.orden, 999) ASC"
        )->fetchAll();
    }

    public function requestsByType(): array
    {
        return $this->db->query(
            "SELECT pt.nombre AS tipo, COUNT(*) AS total
             FROM requests r
             INNER JOIN project_types pt ON pt.id = r.tipo_id
             GROUP BY pt.id, pt.nombre
             ORDER BY total DESC, pt.nombre"
        )->fetchAll();
    }

    public function requestsByResponsible(): array
    {
        return $this->db->query(
            "SELECT
                COALESCE(u.nombre, 'Sin responsable') AS responsable,
                COUNT(*) AS total,
                COALESCE(SUM(r.esfuerzo_estimado_horas), 0) AS horas_estimadas,
                COALESCE(SUM(r.esfuerzo_real_horas), 0) AS horas_reales,
                SUM(CASE WHEN LOWER(s.nombre) = 'bloqueada' OR NULLIF(TRIM(r.motivo_bloqueo), '') IS NOT NULL THEN 1 ELSE 0 END) AS bloqueadas,
                SUM(CASE WHEN r.fecha_requerida IS NOT NULL
                          AND r.fecha_requerida < CURDATE()
                          AND s.es_final = 0 THEN 1 ELSE 0 END) AS atrasadas,
                ROUND(AVG(COALESCE(r.porcentaje_avance, 0)), 1) AS avance_promedio
             FROM requests r
             LEFT JOIN users u ON u.id = r.responsable_id
             LEFT JOIN statuses s ON s.id = r.estado_id
             GROUP BY responsable
             ORDER BY total DESC, responsable ASC"
        )->fetchAll();
    }

    public function topLate(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                r.id,
                r.codigo,
                r.titulo,
                r.fecha_requerida,
                DATEDIFF(CURDATE(), r.fecha_requerida) AS dias_atraso,
                COALESCE(u.nombre, 'Sin responsable') AS responsable,
                COALESCE(pp.nombre, 'Sin fase') AS fase
             FROM requests r
             LEFT JOIN users u ON u.id = r.responsable_id
             LEFT JOIN project_phases pp ON pp.id = r.phase_id
             LEFT JOIN statuses s ON s.id = r.estado_id
             WHERE r.fecha_requerida IS NOT NULL
               AND r.fecha_requerida < CURDATE()
               AND s.es_final = 0
             ORDER BY dias_atraso DESC, r.fecha_requerida ASC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function topBlocked(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                r.id,
                r.codigo,
                r.titulo,
                COALESCE(r.motivo_bloqueo, 'Sin detalle') AS motivo_bloqueo,
                COALESCE(u.nombre, 'Sin responsable') AS responsable,
                COALESCE(pp.nombre, 'Sin fase') AS fase
             FROM requests r
             LEFT JOIN users u ON u.id = r.responsable_id
             LEFT JOIN project_phases pp ON pp.id = r.phase_id
             INNER JOIN statuses s ON s.id = r.estado_id
             WHERE LOWER(s.nombre) = 'bloqueada' OR NULLIF(TRIM(r.motivo_bloqueo), '') IS NOT NULL
             ORDER BY r.updated_at DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function topPendingDocuments(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                r.id,
                r.codigo,
                r.titulo,
                COALESCE(pp.nombre, 'Sin fase') AS fase,
                COUNT(*) AS pendientes
             FROM request_attachments ra
             INNER JOIN requests r ON r.id = ra.request_id
             LEFT JOIN project_phases pp ON pp.id = r.phase_id
             WHERE COALESCE(ra.estado_documento, 'pendiente') = 'pendiente'
             GROUP BY r.id, r.codigo, r.titulo, pp.nombre, pp.orden
             ORDER BY pendientes DESC, r.codigo ASC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}