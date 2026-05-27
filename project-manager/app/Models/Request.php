<?php
namespace App\Models;

use App\Core\Model;

class Request extends Model
{
    private ?int $lastCreatedId = null;

    public function lastCreatedId(): ?int
    {
        return $this->lastCreatedId;
    }

    public function paginate(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $where = " WHERE 1=1 ";
        $params = [];

        if (!empty($filters['q'])) {
            $where .= " AND (r.codigo LIKE :q OR r.titulo LIKE :q OR r.descripcion LIKE :q)";
            $params['q'] = '%' . trim((string) $filters['q']) . '%';
        }

        if (!empty($filters['estado_id'])) {
            $where .= " AND r.estado_id = :estado_id";
            $params['estado_id'] = (int) $filters['estado_id'];
        }

        if (!empty($filters['category_id'])) {
            $where .= " AND pt.category_id = :category_id";
            $params['category_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['tipo_id'])) {
            $where .= " AND r.tipo_id = :tipo_id";
            $params['tipo_id'] = (int) $filters['tipo_id'];
        }

        if (!empty($filters['prioridad_id'])) {
            $where .= " AND r.prioridad_id = :prioridad_id";
            $params['prioridad_id'] = (int) $filters['prioridad_id'];
        }

        if (!empty($filters['responsable_id'])) {
            $where .= " AND r.responsable_id = :responsable_id";
            $params['responsable_id'] = (int) $filters['responsable_id'];
        }

        if (!empty($filters['phase_id'])) {
            $where .= " AND r.phase_id = :phase_id";
            $params['phase_id'] = (int) $filters['phase_id'];
        }

        if (!empty($filters['bloqueadas'])) {
            $where .= " AND (LOWER(s.nombre) = 'bloqueada' OR NULLIF(TRIM(r.motivo_bloqueo), '') IS NOT NULL)";
        }

        if (!empty($filters['atrasadas'])) {
            $where .= " AND r.fecha_requerida IS NOT NULL
                        AND r.fecha_requerida < CURDATE()
                        AND COALESCE(s.es_final, 0) = 0";
        }

        if (!empty($filters['pendientes_doc'])) {
            $where .= " AND EXISTS (
                SELECT 1
                FROM request_attachments ra_p
                WHERE ra_p.request_id = r.id
                  AND COALESCE(ra_p.estado_documento, 'pendiente') = 'pendiente'
            )";
        }

        if (!empty($filters['rechazados_doc'])) {
            $where .= " AND EXISTS (
                SELECT 1
                FROM request_attachments ra_r
                WHERE ra_r.request_id = r.id
                  AND COALESCE(ra_r.estado_documento, '') = 'rechazado'
            )";
        }

        $from = " FROM requests r
                  LEFT JOIN project_types pt ON pt.id = r.tipo_id
                  LEFT JOIN project_categories pc ON pc.id = pt.category_id
                  LEFT JOIN priorities p ON p.id = r.prioridad_id
                  LEFT JOIN statuses s ON s.id = r.estado_id
                  LEFT JOIN users u ON u.id = r.responsable_id
                  LEFT JOIN project_phases ph ON ph.id = r.phase_id ";

        $countSql = "SELECT COUNT(*) AS total " . $from . $where;
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT
                    r.*,
                    pt.nombre AS tipo,
                    pc.nombre AS categoria,
                    p.nombre AS prioridad,
                    s.nombre AS estado,
                    s.color AS estado_color,
                    s.es_final,
                    u.nombre AS responsable,
                    ph.nombre AS fase,

                    (
                        SELECT COUNT(*)
                        FROM request_attachments ra
                        WHERE ra.request_id = r.id
                          AND COALESCE(ra.estado_documento, 'pendiente') = 'pendiente'
                    ) AS docs_pendientes,

                    (
                        SELECT COUNT(*)
                        FROM request_attachments ra
                        WHERE ra.request_id = r.id
                          AND COALESCE(ra.estado_documento, '') = 'rechazado'
                    ) AS docs_rechazados,

                    CASE
                        WHEN r.fecha_requerida IS NOT NULL
                         AND r.fecha_requerida < CURDATE()
                         AND COALESCE(s.es_final, 0) = 0
                        THEN DATEDIFF(CURDATE(), r.fecha_requerida)
                        ELSE 0
                    END AS dias_atraso,

                    CASE
                        WHEN COALESCE(r.esfuerzo_estimado_horas, 0) > 0
                         AND COALESCE(r.esfuerzo_real_horas, 0) > COALESCE(r.esfuerzo_estimado_horas, 0)
                        THEN 1
                        ELSE 0
                    END AS horas_excedidas
                " . $from . $where . "
                ORDER BY r.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'rows' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function exportRows(array $filters = []): array
    {
        $result = $this->paginate($filters, 1, 100000);
        return $result['rows'];
    }

    public function kanbanData(array $filters = []): array
    {
        $phases = $this->db->query(
            "SELECT id, nombre, orden
            FROM project_phases
            WHERE activo = 1
            ORDER BY orden ASC, id ASC"
        )->fetchAll();

        $sql = "SELECT 
                    r.id,
                    r.codigo,
                    r.titulo,
                    r.descripcion,
                    r.phase_id,
                    r.estado_id,
                    r.tipo_id,
                    r.prioridad_id,
                    r.responsable_id,
                    r.porcentaje_avance,
                    r.fecha_fin_estimada,
                    r.fecha_requerida,
                    r.motivo_bloqueo,
                    r.esfuerzo_estimado_horas,
                    r.esfuerzo_real_horas,
                    p.nombre AS prioridad,
                    u.nombre AS responsable,
                    s.nombre AS estado,
                    ph.nombre AS fase
                FROM requests r
                LEFT JOIN priorities p ON p.id = r.prioridad_id
                LEFT JOIN users u ON u.id = r.responsable_id
                LEFT JOIN statuses s ON s.id = r.estado_id
                LEFT JOIN project_phases ph ON ph.id = r.phase_id
                WHERE 1 = 1";

        $params = [];

        if (!empty($filters['q'])) {
            $sql .= " AND (
                        r.codigo LIKE :q
                        OR r.titulo LIKE :q
                        OR r.descripcion LIKE :q
                    )";
            $params['q'] = '%' . trim((string) $filters['q']) . '%';
        }

        if (!empty($filters['tipo_id'])) {
            $sql .= " AND r.tipo_id = :tipo_id";
            $params['tipo_id'] = (int) $filters['tipo_id'];
        }

        if (!empty($filters['prioridad_id'])) {
            $sql .= " AND r.prioridad_id = :prioridad_id";
            $params['prioridad_id'] = (int) $filters['prioridad_id'];
        }

        if (!empty($filters['responsable_id'])) {
            $sql .= " AND r.responsable_id = :responsable_id";
            $params['responsable_id'] = (int) $filters['responsable_id'];
        }

        $sql .= " ORDER BY COALESCE(ph.orden, 999) ASC, r.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        $board = [];

        foreach ($phases as $phase) {
            $phaseId = (int) $phase['id'];

            $board[$phaseId] = [
                'phase' => $phase,
                'items' => [],
            ];
        }

        foreach ($items as $item) {
            $phaseId = (int) ($item['phase_id'] ?? 0);

            if (!isset($board[$phaseId])) {
                $board[$phaseId] = [
                    'phase' => [
                        'id' => $phaseId,
                        'nombre' => 'Sin fase',
                        'orden' => 999,
                    ],
                    'items' => [],
                ];
            }

            $board[$phaseId]['items'][] = $item;
        }

        return $board;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, 
                    a.nombre AS area,
                    pt.nombre AS tipo,
                    pc.nombre AS categoria,
                    pt.category_id AS category_id,
                    p.nombre AS prioridad,
                    s.nombre AS estado,
                    s.es_final AS estado_es_final,
                    u.nombre AS responsable,
                    ph.nombre AS fase_nombre
             FROM requests r
             LEFT JOIN areas a ON a.id = r.area_id
             LEFT JOIN project_types pt ON pt.id = r.tipo_id
             LEFT JOIN project_categories pc ON pc.id = pt.category_id
             LEFT JOIN priorities p ON p.id = r.prioridad_id
             LEFT JOIN statuses s ON s.id = r.estado_id
             LEFT JOIN users u ON u.id = r.responsable_id
             LEFT JOIN project_phases ph ON ph.id = r.phase_id
             WHERE r.id = :id
             LIMIT 1"
        );

        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function comments(int $requestId): array
    {
        $stmt = $this->db->prepare(
            "SELECT rc.*, u.nombre
             FROM request_comments rc
             JOIN users u ON u.id = rc.user_id
             WHERE rc.request_id = :id
             ORDER BY rc.id DESC"
        );

        $stmt->execute(['id' => $requestId]);
        return $stmt->fetchAll();
    }

    public function history(int $requestId): array
    {
        $stmt = $this->db->prepare(
            "SELECT rh.*, u.nombre
             FROM request_history rh
             JOIN users u ON u.id = rh.user_id
             WHERE rh.request_id = :id
             ORDER BY rh.id DESC"
        );

        $stmt->execute(['id' => $requestId]);
        return $stmt->fetchAll();
    }

    public function phaseHistory(int $requestId): array
    {
        $stmt = $this->db->prepare(
            "SELECT rph.*, u.nombre
             FROM request_phase_history rph
             JOIN users u ON u.id = rph.cambiado_por
             WHERE rph.request_id = :id
             ORDER BY rph.id DESC"
        );

        $stmt->execute(['id' => $requestId]);
        return $stmt->fetchAll();
    }

    public function attachments(int $requestId): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                ra.*,
                u.nombre AS subido_por_nombre,
                COALESCE(pp_directa.nombre, pp_tipo.nombre, 'Sin fase') AS fase_nombre
            FROM request_attachments ra
            JOIN users u ON u.id = ra.subido_por
            LEFT JOIN document_types dt ON dt.id = ra.document_type_id
            LEFT JOIN project_phases pp_directa ON pp_directa.id = ra.phase_id
            LEFT JOIN project_phases pp_tipo ON pp_tipo.id = dt.fase_id
            WHERE ra.request_id = :id
            ORDER BY ra.id DESC"
        );

        $stmt->execute(['id' => $requestId]);
        return $stmt->fetchAll();
    }

    public function catalogs(): array
    {
        return [
            'areas' => $this->db->query("SELECT id, nombre FROM areas ORDER BY nombre ASC")->fetchAll(),
            'types' => $this->db->query("SELECT id, nombre FROM project_types ORDER BY nombre ASC")->fetchAll(),

            'categories' => $this->db->query("
                SELECT id, nombre
                FROM project_categories
                ORDER BY nombre ASC
            ")->fetchAll(),

            'types' => $this->db->query("
                SELECT 
                    pt.id,
                    pt.nombre,
                    pt.category_id,
                    pc.nombre AS categoria
                FROM project_types pt
                LEFT JOIN project_categories pc ON pc.id = pt.category_id
                ORDER BY pc.nombre ASC, pt.nombre ASC
            ")->fetchAll(),

            'statuses' => $this->db->query("SELECT * FROM statuses ORDER BY orden ASC, id ASC")->fetchAll(),
            'priorities' => $this->db->query("SELECT * FROM priorities ORDER BY nivel ASC, id ASC")->fetchAll(),
            'users' => $this->db->query("SELECT id, nombre FROM users WHERE estado = 1 ORDER BY nombre ASC")->fetchAll(),
            'phases' => $this->db->query("SELECT id, nombre, orden FROM project_phases WHERE activo = 1 ORDER BY orden ASC")->fetchAll(),
        ];
    }

    public function update(int $id, array $data, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE requests SET
                titulo = :titulo,
                descripcion = :descripcion,
                area_id = :area_id,
                tipo_id = :tipo_id,
                prioridad_id = :prioridad_id,
                impacto = :impacto,
                urgencia = :urgencia,
                complejidad = :complejidad,
                riesgo = :riesgo,
                esfuerzo_estimado_horas = :esfuerzo_estimado_horas,
                esfuerzo_real_horas = :esfuerzo_real_horas,
                fecha_requerida = :fecha_requerida,
                fecha_inicio_estimada = :fecha_inicio_estimada,
                fecha_fin_estimada = :fecha_fin_estimada,
                requiere_formalizacion = :requiere_formalizacion,
                fecha_inicio_real = :fecha_inicio_real,
                fecha_fin_real = :fecha_fin_real,
                responsable_id = :responsable_id,
                porcentaje_avance = :porcentaje_avance,
                motivo_bloqueo = :motivo_bloqueo,
                dependencia_externa = :dependencia_externa,
                updated_at = NOW()
             WHERE id = :id"
        );

        return $stmt->execute([
            'id' => $id,
            'titulo' => trim($data['titulo'] ?? ''),
            'descripcion' => trim($data['descripcion'] ?? ''),
            'area_id' => (int) ($data['area_id'] ?? 0),
            'tipo_id' => (int) ($data['tipo_id'] ?? 0),
            'prioridad_id' => (int) ($data['prioridad_id'] ?? 0),
            'impacto' => $data['impacto'] ?? 'Medio',
            'urgencia' => $data['urgencia'] ?? 'Media',
            'complejidad' => $data['complejidad'] ?? 'Media',
            'riesgo' => $data['riesgo'] ?? 'Medio',
            'esfuerzo_estimado_horas' => (float) ($data['esfuerzo_estimado_horas'] ?? 0),
            'esfuerzo_real_horas' => (float) ($data['esfuerzo_real_horas'] ?? 0),
            'fecha_requerida' => !empty($data['fecha_requerida']) ? $data['fecha_requerida'] : null,
            'fecha_inicio_estimada' => !empty($data['fecha_inicio_estimada']) ? $data['fecha_inicio_estimada'] : null,
            'fecha_fin_estimada' => !empty($data['fecha_fin_estimada']) ? $data['fecha_fin_estimada'] : null,
            'requiere_formalizacion' => !empty($data['requiere_formalizacion']) ? 1 : 0,
            'fecha_inicio_real' => !empty($data['fecha_inicio_real']) ? $data['fecha_inicio_real'] : null,
            'fecha_fin_real' => !empty($data['fecha_fin_real']) ? $data['fecha_fin_real'] : null,
            'responsable_id' => !empty($data['responsable_id']) ? (int) $data['responsable_id'] : null,
            'porcentaje_avance' => (int) ($data['porcentaje_avance'] ?? 0),
            'motivo_bloqueo' => trim($data['motivo_bloqueo'] ?? '') ?: null,
            'dependencia_externa' => trim($data['dependencia_externa'] ?? '') ?: null,
        ]);
    }


    public function stateGuardMessage(int $id, bool $blockClosed = true): ?string
    {
        $request = $this->find($id);

        if (!$request) {
            return 'Solicitud no encontrada.';
        }

        $estado = mb_strtolower(trim((string) ($request['estado'] ?? '')));

        if (!empty($request['motivo_bloqueo']) || $estado === 'bloqueada') {
            return 'La solicitud está bloqueada. Debe desbloquearse antes de modificarla o avanzarla.';
        }

        if ($blockClosed && ($estado === 'cerrada' || (int) ($request['estado_es_final'] ?? 0) === 1)) {
            return 'La solicitud está cerrada. No se puede modificar ni avanzar.';
        }

        return null;
    }


    private function statusIdByPhaseId(int $phaseId): int
    {
        switch ($phaseId) {
            case 1:
                return 1;

            case 2:
                return 3;

            case 3:
                return 5;

            case 4:
                return 4;

            case 5:
                return 7;

            case 6:
                return 8;

            case 7:
            case 8:
                return 11;

            default:
                return 1;
        }
    }

    public function updatePhase(int $requestId, int $newPhaseId, int $userId, ?string $observation = null): bool
    {
        $current = $this->find($requestId);
        if (!$current) {
            return false;
        }

        if ($this->stateGuardMessage($requestId, true) !== null) {
            return false;
        }

        $newStatusId = $this->statusIdByPhaseId($newPhaseId);

        $stmt = $this->db->prepare(
            "UPDATE requests
            SET phase_id = :phase_id,
                estado_id = :estado_id,
                updated_at = NOW()
            WHERE id = :id"
        );

        $ok = $stmt->execute([
            'phase_id' => $newPhaseId,
            'estado_id' => $newStatusId,
            'id' => $requestId
        ]);

        if ($ok) {
            $log = $this->db->prepare(
                "INSERT INTO request_phase_history
                (request_id, fase_origen_id, fase_destino_id, cambiado_por, observacion, created_at)
                VALUES
                (:request_id, :fase_origen_id, :fase_destino_id, :cambiado_por, :observacion, NOW())"
            );

            $log->execute([
                'request_id' => $requestId,
                'fase_origen_id' => $current['phase_id'] ?: null,
                'fase_destino_id' => $newPhaseId,
                'cambiado_por' => $userId,
                'observacion' => $observation
            ]);
        }

        return $ok;
    }

    public function updateProgress(int $requestId, int $progress): bool
    {
        $request = $this->find($requestId);
        $phaseId = $request ? (int) ($request['phase_id'] ?? 0) : 0;
        $progress = $this->normalizeProgressByPhase($requestId, $phaseId, $progress);

        $stmt = $this->db->prepare(
            "UPDATE requests
             SET porcentaje_avance = :porcentaje_avance
             WHERE id = :id"
        );

        return $stmt->execute([
            'porcentaje_avance' => $progress,
            'id' => $requestId
        ]);
    }

    public function setPhaseAndProgress(int $requestId, int $phaseId, int $progress): bool
    {
        $progress = $this->normalizeProgressByPhase($requestId, $phaseId, $progress);

        $statusId = $this->statusIdByPhaseId($phaseId);

        $stmt = $this->db->prepare(
            "UPDATE requests
            SET phase_id = :phase_id,
                estado_id = :estado_id,
                porcentaje_avance = :porcentaje_avance,
                updated_at = NOW()
            WHERE id = :id"
        );

        return $stmt->execute([
            'phase_id' => $phaseId,
            'estado_id' => $statusId,
            'porcentaje_avance' => $progress,
            'id' => $requestId
        ]);
    }

    private function normalizeProgressByPhase(int $requestId, int $phaseId, int $progress): int
    {
        $progress = max(0, min(100, $progress));

        if ($phaseId <= 0 || !$this->isLastPhase($phaseId)) {
            return $progress;
        }

        if ($this->allRequiredDocumentsApproved($requestId, $phaseId)) {
            return 100;
        }

        return min($progress, 95);
    }

    private function isLastPhase(int $phaseId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id
             FROM project_phases
             WHERE activo = 1
             ORDER BY orden DESC, id DESC
             LIMIT 1"
        );
        $stmt->execute();

        return (int) $stmt->fetchColumn() === $phaseId;
    }

    private function allRequiredDocumentsApproved(int $requestId, int $phaseId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM document_types dt
             WHERE dt.fase_id = :phase_id
               AND dt.activo = 1
               AND dt.obligatorio = 1
               AND NOT EXISTS (
                   SELECT 1
                   FROM request_attachments ra
                   WHERE ra.request_id = :request_id
                     AND ra.phase_id = dt.fase_id
                     AND ra.document_type_id = dt.id
                     AND ra.aprobado = 1
                     AND COALESCE(ra.estado_documento, '') = 'aprobado'
               )"
        );
        $stmt->execute([
            'request_id' => $requestId,
            'phase_id' => $phaseId,
        ]);

        return (int) $stmt->fetchColumn() === 0;
    }

    public function create(array $data, int $userId): bool
    {
        $codigo = $this->nextCode();

        $stmt = $this->db->prepare(
            "INSERT INTO requests (
                codigo,
                solicitante_id,
                titulo,
                descripcion,
                area_id,
                tipo_id,
                prioridad_id,
                estado_id,
                phase_id,
                impacto,
                urgencia,
                complejidad,
                riesgo,
                esfuerzo_estimado_horas,
                esfuerzo_real_horas,
                requiere_formalizacion,
                fecha_requerida,
                responsable_id,
                porcentaje_avance,
                motivo_bloqueo,
                dependencia_externa,
                created_at,
                updated_at
            ) VALUES (
                :codigo,
                :solicitante_id,
                :titulo,
                :descripcion,
                :area_id,
                :tipo_id,
                :prioridad_id,
                :estado_id,
                :phase_id,
                :impacto,
                :urgencia,
                :complejidad,
                :riesgo,
                :esfuerzo_estimado_horas,
                :esfuerzo_real_horas,
                :requiere_formalizacion,
                :fecha_requerida,
                :responsable_id,
                :porcentaje_avance,
                :motivo_bloqueo,
                :dependencia_externa,
                NOW(),
                NOW()
            )"
        );

        $ok = $stmt->execute([
            'codigo' => $codigo,
            'solicitante_id' => $userId,
            'titulo' => trim($data['titulo'] ?? ''),
            'descripcion' => trim($data['descripcion'] ?? ''),
            'area_id' => (int) ($data['area_id'] ?? 0),
            'tipo_id' => (int) ($data['tipo_id'] ?? 0),
            'prioridad_id' => (int) ($data['prioridad_id'] ?? 0),
            /*'estado_id' => (int)($data['estado_id'] ?? 0), */
            'estado_id' => 1,
            /* 'phase_id' => (int)($data['phase_id'] ?? 1),*/
            'phase_id' => 1,
            'impacto' => $data['impacto'] ?? 'Medio',
            'urgencia' => $data['urgencia'] ?? 'Media',
            'complejidad' => $data['complejidad'] ?? 'Media',
            'riesgo' => $data['riesgo'] ?? 'Medio',
            'esfuerzo_estimado_horas' => (float) ($data['esfuerzo_estimado_horas'] ?? 0),
            'esfuerzo_real_horas' => 0,
            'requiere_formalizacion' => !empty($data['requiere_formalizacion']) ? 1 : 0,
            'fecha_requerida' => !empty($data['fecha_requerida']) ? $data['fecha_requerida'] : null,
            'responsable_id' => !empty($data['responsable_id']) ? (int) $data['responsable_id'] : null,
            'porcentaje_avance' => 0,
            'motivo_bloqueo' => null,
            'dependencia_externa' => null,
        ]);

        if ($ok) {
            $this->lastCreatedId = (int) $this->db->lastInsertId();
        }

        return $ok;
    }

    public function closeAsCompleted(int $requestId, int $userId): bool
    {
        $current = $this->find($requestId);
        if (!$current) {
            return false;
        }

        $previousStatus = $current['estado_id'] ?? null;
        $previousProgress = $current['porcentaje_avance'] ?? null;

        $stmt = $this->db->prepare(
            "UPDATE requests
             SET estado_id = 12,
                 porcentaje_avance = 100,
                 fecha_fin_real = COALESCE(fecha_fin_real, CURDATE()),
                 updated_at = NOW()
             WHERE id = :id"
        );

        $ok = $stmt->execute(['id' => $requestId]);

        if ($ok) {
            if ((string) $previousStatus !== '12') {
                $this->logHistory($requestId, $userId, 'estado_id', $previousStatus, 12);
            }
            if ((string) $previousProgress !== '100') {
                $this->logHistory($requestId, $userId, 'porcentaje_avance', $previousProgress, 100);
            }
        }

        return $ok;
    }

    public function changeStatus(int $id, int $estadoId, int $userId, ?string $motivo = null): bool
    {
        $current = $this->find($id);
        if (!$current || $estadoId <= 0) {
            return false;
        }

        $previous = $current['estado_id'] ?? null;

        $stmt = $this->db->prepare(
            "UPDATE requests
             SET estado_id = :estado_id,
                 motivo_bloqueo = :motivo_bloqueo,
                 updated_at = NOW()
             WHERE id = :id"
        );

        $ok = $stmt->execute([
            'id' => $id,
            'estado_id' => $estadoId,
            'motivo_bloqueo' => trim((string) $motivo) ?: ($current['motivo_bloqueo'] ?? null),
        ]);

        if ($ok) {
            $this->logHistory($id, $userId, 'estado_id', $previous, $estadoId);
        }

        return $ok;
    }

    public function block(int $id, string $motivo, int $userId): bool
    {
        $current = $this->find($id);
        if (!$current) {
            return false;
        }

        $previous = $current['motivo_bloqueo'] ?? null;

        $stmt = $this->db->prepare(
            "UPDATE requests
             SET motivo_bloqueo = :motivo_bloqueo,
                 updated_at = NOW()
             WHERE id = :id"
        );

        $ok = $stmt->execute([
            'id' => $id,
            'motivo_bloqueo' => trim($motivo),
        ]);

        if ($ok) {
            $this->logHistory($id, $userId, 'motivo_bloqueo', $previous, trim($motivo));
        }

        return $ok;
    }

    public function unblock(int $id, int $userId): bool
    {
        $current = $this->find($id);
        if (!$current) {
            return false;
        }

        $previous = $current['motivo_bloqueo'] ?? null;

        $stmt = $this->db->prepare(
            "UPDATE requests
             SET motivo_bloqueo = NULL,
                 updated_at = NOW()
             WHERE id = :id"
        );

        $ok = $stmt->execute(['id' => $id]);

        if ($ok) {
            $this->logHistory($id, $userId, 'motivo_bloqueo', $previous, null);
        }

        return $ok;
    }

    private function logHistory(int $requestId, int $userId, string $field, $oldValue, $newValue): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO request_history
                (request_id, user_id, campo_modificado, valor_anterior, valor_nuevo, created_at)
             VALUES
                (:request_id, :user_id, :campo_modificado, :valor_anterior, :valor_nuevo, NOW())"
        );

        $stmt->execute([
            'request_id' => $requestId,
            'user_id' => $userId,
            'campo_modificado' => $field,
            'valor_anterior' => $oldValue,
            'valor_nuevo' => $newValue,
        ]);
    }

    private function nextCode(): string
    {
        $stmt = $this->db->query("SELECT MAX(id) AS max_id FROM requests");
        $row = $stmt->fetch();

        $next = ((int) ($row['max_id'] ?? 0)) + 1;

        return 'REQ-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

}