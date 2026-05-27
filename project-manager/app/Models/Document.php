<?php
namespace App\Models;

use App\Core\Model;

class Document extends Model
{
    public function requiredByPhase(int $requestId, int $phaseId): array
    {
        $categoryId = $this->requestCategoryId($requestId);
        $stmt = $this->db->prepare(
            "SELECT 
                dt.id,
                dt.nombre,
                dt.obligatorio,
                COUNT(ra.id) AS cargados,
                MAX(CASE WHEN ra.aprobado = 1 THEN 1 ELSE 0 END) AS aprobado
             FROM document_types dt
             LEFT JOIN request_attachments ra 
               ON ra.document_type_id = dt.id 
              AND ra.request_id = :request_id
              AND COALESCE(ra.estado_documento, '') <> 'inactivo'

             WHERE dt.fase_id = :fase_id
             AND (
                dt.category_id IS NULL
                OR dt.category_id = :category_id
             )
             AND (
                dt.exclude_category_id IS NULL
                OR dt.exclude_category_id <> :exclude_category_id
             )

             /* WHERE dt.fase_id = :fase_id */
             GROUP BY dt.id, dt.nombre, dt.obligatorio
             ORDER BY dt.obligatorio DESC, dt.nombre ASC"
        );

        $stmt->execute([
            'request_id' => $requestId,
            'fase_id' => $phaseId,
            'category_id' => $categoryId,
            'exclude_category_id' => $categoryId,
        ]);
        return $stmt->fetchAll();
    }

    private function requestCategoryId(int $requestId): ?int
    {
        $stmt = $this->db->prepare(
            "SELECT pt.category_id
            FROM requests r
            LEFT JOIN project_types pt ON pt.id = r.tipo_id
            WHERE r.id = :request_id
            LIMIT 1"
        );

        $stmt->execute(['request_id' => $requestId]);

        $categoryId = $stmt->fetchColumn();

        return $categoryId ? (int) $categoryId : null;
    }


    public function typesByPhase(int $phaseId, bool $onlyActive = true, ?int $categoryId = null): array
    {
        $sql = "SELECT id, nombre, fase_id, category_id, exclude_category_id, obligatorio, descripcion, activo
        FROM document_types
        WHERE fase_id = :phase_id";

        if ($onlyActive) {
            $sql .= " AND activo = 1";
        }

        if ($categoryId !== null) {
            $sql .= " AND (category_id IS NULL OR category_id = :category_id)";
            $sql .= " AND (exclude_category_id IS NULL OR exclude_category_id <> :exclude_category_id)";
        }

        $sql .= " ORDER BY obligatorio DESC, id ASC";

        $stmt = $this->db->prepare($sql);
        $params = ['phase_id' => $phaseId];

        if ($categoryId !== null) {
            $params['category_id'] = $categoryId;
            $params['exclude_category_id'] = $categoryId;
        }

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function documentsByRequest(int $requestId): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                ra.*,
                dt.nombre AS tipo_documento,
                dt.obligatorio,
                COALESCE(pp.nombre, 'Sin fase') AS fase_nombre,
                u.nombre AS subido_por_nombre,
                ap.nombre AS aprobado_por_nombre
             FROM request_attachments ra
             LEFT JOIN document_types dt ON dt.id = ra.document_type_id
             LEFT JOIN project_phases pp ON pp.id = ra.phase_id
             LEFT JOIN users u ON u.id = ra.subido_por
             LEFT JOIN users ap ON ap.id = ra.aprobado_por
             WHERE ra.request_id = :request_id
             ORDER BY pp.orden ASC, ra.id DESC"
        );
        $stmt->execute(['request_id' => $requestId]);
        return $stmt->fetchAll();
    }

    public function groupedDocumentsByRequest(int $requestId): array
    {
        $rows = $this->documentsByRequest($requestId);
        $grouped = [];

        foreach ($rows as $row) {
            $phase = $row['fase_nombre'] ?? 'Sin fase';
            if (!isset($grouped[$phase])) {
                $grouped[$phase] = [];
            }
            $grouped[$phase][] = $row;
        }

        return $grouped;
    }

    public function upload(int $requestId, int $phaseId, int $typeId, int $userId, array $file): bool
    {
        $upload = $this->storeUploadedFile($requestId, $file, 'requests/' . $requestId . '/documents');
        if (!$upload) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO request_attachments
            (request_id, phase_id, document_type_id, nombre_original, ruta_archivo, tipo_mime, peso, estado_documento, aprobado, subido_por, created_at)
            VALUES (:r,:p,:t,:n,:ruta,:mime,:peso,'pendiente',0,:u,NOW())"
        );

        return $stmt->execute([
            'r' => $requestId,
            'p' => $phaseId,
            't' => $typeId,
            'n' => $upload['original_name'],
            'ruta' => $upload['file_path'],
            'mime' => $upload['mime_type'],
            'peso' => $upload['file_size'],
            'u' => $userId
        ]);
    }


    private function storeUploadedFile(int $requestId, array $file, string $subdir): ?array
    {
        $originalName = basename((string) ($file['name'] ?? ''));
        $tmpPath = (string) ($file['tmp_name'] ?? '');
        $fileSize = (int) ($file['size'] ?? 0);
        $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($uploadError !== UPLOAD_ERR_OK || $originalName === '' || $tmpPath === '' || !is_uploaded_file($tmpPath)) {
            $this->uploadDebug('REJECT reason=invalid_upload original=' . $originalName . ' error=' . $uploadError);
            return null;
        }

        $maxBytes = 20 * 1024 * 1024;

        if ($fileSize <= 0 || $fileSize > $maxBytes) {
            $this->uploadDebug('REJECT reason=invalid_size original=' . $originalName . ' size=' . $fileSize);
            return null;
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($extension === '') {
            $this->uploadDebug('REJECT reason=no_extension original=' . $originalName);
            return null;
        }

        $blockedExtensions = [
            'php',
            'phtml',
            'php3',
            'php4',
            'php5',
            'phar',
            'exe',
            'bat',
            'cmd',
            'sh',
            'js',
            'html',
            'htm',
            'svg',
            'jar',
            'msi',
            'com',
            'scr',
        ];

        if (in_array($extension, $blockedExtensions, true)) {
            $this->uploadDebug('REJECT reason=blocked_extension original=' . $originalName . ' extension=' . $extension);
            return null;
        }

        $allowedExtensions = [
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'ppt',
            'pptx',
            'txt',
            'csv',
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',
        ];

        if (!in_array($extension, $allowedExtensions, true)) {
            $this->uploadDebug('REJECT reason=invalid_extension original=' . $originalName . ' extension=' . $extension);
            return null;
        }

        $allowedMimeTypes = [
            'pdf' => ['application/pdf'],
            'doc' => [
                'application/msword',
                'application/octet-stream',
            ],
            'docx' => [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip',
                'application/octet-stream',
            ],
            'xls' => [
                'application/vnd.ms-excel',
                'application/octet-stream',
            ],
            'xlsx' => [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/zip',
                'application/octet-stream',
            ],
            'ppt' => [
                'application/vnd.ms-powerpoint',
                'application/octet-stream',
            ],
            'pptx' => [
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/zip',
                'application/octet-stream',
            ],
            'txt' => [
                'text/plain',
                'application/octet-stream',
            ],
            'csv' => [
                'text/plain',
                'text/csv',
                'application/csv',
                'application/vnd.ms-excel',
                'application/octet-stream',
            ],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if (!$finfo) {
            $this->uploadDebug('REJECT reason=finfo_open_failed original=' . $originalName);
            return null;
        }

        $mime = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        if (!$mime) {
            $this->uploadDebug('REJECT reason=no_mime original=' . $originalName);
            return null;
        }

        $mime = trim((string) $mime);
        $clientMime = trim((string) ($file['type'] ?? ''));

        if (isset($allowedMimeTypes[$extension])) {
            foreach ($allowedMimeTypes[$extension] as $allowedMime) {
                if (strpos($mime, $allowedMime) === 0) {
                    $mime = $allowedMime;
                    break;
                }
            }
        }

        $this->uploadDebug(
            'STORE original=' . $originalName .
            ' extension=' . $extension .
            ' client_mime=' . $clientMime .
            ' detected_mime=' . $mime .
            ' size=' . $fileSize
        );

        if (!isset($allowedMimeTypes[$extension]) || !in_array($mime, $allowedMimeTypes[$extension], true)) {
            $this->uploadDebug(
                'REJECT reason=invalid_mime extension=' . $extension .
                ' detected_mime=' . $mime .
                ' client_mime=' . $clientMime
            );

            return null;
        }

        if (strpos($mime, 'image/') === 0 && @getimagesize($tmpPath) === false) {
            $this->uploadDebug('REJECT reason=invalid_image original=' . $originalName . ' mime=' . $mime);
            return null;
        }

        $dir = __DIR__ . '/../../public/uploads/' . trim($subdir, '/');

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            $this->uploadDebug('REJECT reason=mkdir_failed dir=' . $dir);
            return null;
        }

        try {
            $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
        } catch (\Throwable $e) {
            $storedName = uniqid('document_', true) . '.' . $extension;
        }

        $dest = $dir . '/' . $storedName;

        if (!move_uploaded_file($tmpPath, $dest)) {
            $this->uploadDebug('REJECT reason=move_failed tmp=' . $tmpPath . ' dest=' . $dest);
            return null;
        }

        return [
            'file_path' => 'uploads/' . trim($subdir, '/') . '/' . $storedName,
            'original_name' => $originalName,
            'mime_type' => $mime,
            'file_size' => $fileSize,
        ];
    }

    public function approve(int $id, int $userId, string $estado, ?string $observacion = null): bool
    {
        if (!in_array($estado, ['aprobado', 'rechazado'], true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE request_attachments
         SET estado_documento = :e,
             aprobado = :a,
             aprobado_por = :u,
             observacion_revision = :o,
             fecha_aprobacion = NOW()
         WHERE id = :id
           AND COALESCE(estado_documento, 'pendiente') = 'pendiente'"
        );

        $stmt->execute([
            'e' => $estado,
            'a' => $estado === 'aprobado' ? 1 : 0,
            'u' => $userId,
            'o' => $observacion,
            'id' => $id
        ]);

        $ok = $stmt->rowCount() > 0;

        if ($ok) {
            $this->syncFinalPhaseProgressAfterReview($id);
            $this->skipUxUiRequestToDevelopment($id);
        }

        return $ok;
    }

    private function syncFinalPhaseProgressAfterReview(int $attachmentId): void
    {
        $stmt = $this->db->prepare(
            "SELECT request_id, phase_id
             FROM request_attachments
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $attachmentId]);
        $attachment = $stmt->fetch();

        if (!$attachment) {
            return;
        }

        $requestId = (int) ($attachment['request_id'] ?? 0);
        $phaseId = (int) ($attachment['phase_id'] ?? 0);

        if ($requestId <= 0 || $phaseId <= 0 || !$this->isLastPhase($phaseId)) {
            return;
        }

        $progress = $this->allRequiredDocumentsApproved($requestId, $phaseId) ? 100 : 95;

        $update = $this->db->prepare(
            "UPDATE requests
             SET porcentaje_avance = :progress
             WHERE id = :request_id
               AND phase_id = :phase_id"
        );
        $update->execute([
            'progress' => $progress,
            'request_id' => $requestId,
            'phase_id' => $phaseId,
        ]);

        if ($progress === 100) {
            $this->markProjectCompletedForNotification($requestId);
        }
    }

    private function skipUxUiRequestToDevelopment(int $attachmentId): void
    {
        $stmt = $this->db->prepare(
            "SELECT 
                ra.request_id,
                ra.phase_id,
                ra.aprobado,
                r.phase_id AS current_phase_id,
                pc.nombre AS categoria,
                pp.nombre AS fase_actual
            FROM request_attachments ra
            INNER JOIN requests r ON r.id = ra.request_id
            LEFT JOIN project_types pt ON pt.id = r.tipo_id
            LEFT JOIN project_categories pc ON pc.id = pt.category_id
            LEFT JOIN project_phases pp ON pp.id = ra.phase_id
            WHERE ra.id = :id
            LIMIT 1"
        );

        $stmt->execute(['id' => $attachmentId]);
        $row = $stmt->fetch();

        if (!$row) {
            return;
        }

        $requestId = (int) $row['request_id'];
        $phaseId = (int) $row['phase_id'];

        if ((int) $row['aprobado'] !== 1) {
            return;
        }

        if (($row['categoria'] ?? '') !== 'UX/UI y Frontend') {
            return;
        }

        if (($row['fase_actual'] ?? '') !== 'Solicitud') {
            return;
        }

        if (!$this->allRequiredDocumentsApproved($requestId, $phaseId)) {
            return;
        }

        $devPhaseStmt = $this->db->prepare(
            "SELECT id
            FROM project_phases
            WHERE nombre = 'Desarrollo'
            AND activo = 1
            LIMIT 1"
        );

        $devPhaseStmt->execute();
        $developmentPhaseId = (int) $devPhaseStmt->fetchColumn();

        if ($developmentPhaseId <= 0) {
            return;
        }

        $update = $this->db->prepare(
            "UPDATE requests
            SET phase_id = :phase_id,
                porcentaje_avance = CASE 
                    WHEN porcentaje_avance < 50 THEN 50 
                    ELSE porcentaje_avance 
                END
            WHERE id = :request_id"
        );

        $update->execute([
            'phase_id' => $developmentPhaseId,
            'request_id' => $requestId,
        ]);
    }


    private function markProjectCompletedForNotification(int $requestId): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $stmt = $this->db->prepare(
            "SELECT codigo, titulo
             FROM requests
             WHERE id = :request_id
             LIMIT 1"
        );
        $stmt->execute(['request_id' => $requestId]);
        $request = $stmt->fetch() ?: [];

        $_SESSION['project_completed'] = [
            'request_id' => $requestId,
            'codigo' => $request['codigo'] ?? '',
            'titulo' => $request['titulo'] ?? '',
        ];
    }

    public function isLastPhase(int $phaseId): bool
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

    public function findTypeForPhase(int $typeId, int $phaseId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT dt.*, pp.nombre AS fase_nombre
             FROM document_types dt
             LEFT JOIN project_phases pp ON pp.id = dt.fase_id
             WHERE dt.id = :id
               AND dt.fase_id = :phase_id
               AND dt.activo = 1
             LIMIT 1"
        );

        $stmt->execute([
            'id' => $typeId,
            'phase_id' => $phaseId,
        ]);

        return $stmt->fetch() ?: null;
    }

    public function hasRequiredDocumentsLoaded(int $requestId, int $phaseId): array
    {
        $documents = $this->requiredByPhase($requestId, $phaseId);

        $missing = [];
        foreach ($documents as $doc) {
            $required = (int) ($doc['obligatorio'] ?? 0) === 1;
            $loaded = (int) ($doc['cargados'] ?? 0) > 0;

            if ($required && !$loaded) {
                $missing[] = $doc['nombre'];
            }
        }

        return [
            'ok' => empty($missing),
            'missing' => $missing,
            'documents' => $documents,
        ];
    }

    public function phaseCanBeApproved(int $requestId, int $phaseId): array
    {
        $documents = $this->requiredByPhase($requestId, $phaseId);

        $missing = [];
        foreach ($documents as $doc) {
            $required = (int) ($doc['obligatorio'] ?? 0) === 1;
            $loaded = (int) ($doc['cargados'] ?? 0) > 0;
            $approved = (int) ($doc['aprobado'] ?? 0) === 1;

            if ($required && (!$loaded || !$approved)) {
                $missing[] = $doc['nombre'];
            }
        }

        return [
            'ok' => empty($missing),
            'missing' => $missing,
            'documents' => $documents,
        ];
    }

    public function isSingleApprovedDocumentPresent(int $requestId, int $phaseId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) 
             FROM request_attachments
             WHERE request_id = :request_id
               AND phase_id = :phase_id
               AND aprobado = 1
               AND COALESCE(estado_documento, '') <> 'inactivo'"
        );
        $stmt->execute([
            'request_id' => $requestId,
            'phase_id' => $phaseId
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function paginateGlobal(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where = " WHERE 1=1 ";
        $params = [];

        if (!empty($filters['q'])) {
            $where .= " AND (r.codigo LIKE :q OR r.titulo LIKE :q OR ra.nombre_original LIKE :q OR dt.nombre LIKE :q)";
            $params['q'] = '%' . trim((string) $filters['q']) . '%';
        }

        if (!empty($filters['request_id'])) {
            $where .= " AND ra.request_id = :request_id";
            $params['request_id'] = (int) $filters['request_id'];
        }

        if (!empty($filters['phase_id'])) {
            $where .= " AND ra.phase_id = :phase_id";
            $params['phase_id'] = (int) $filters['phase_id'];
        }

        if (!empty($filters['document_type_id'])) {
            $where .= " AND ra.document_type_id = :document_type_id";
            $params['document_type_id'] = (int) $filters['document_type_id'];
        }

        if (!empty($filters['estado_documento'])) {
            $where .= " AND ra.estado_documento = :estado_documento";
            $params['estado_documento'] = (string) $filters['estado_documento'];
        }

        if (!empty($filters['solo_activos'])) {
            $where .= " AND COALESCE(ra.estado_documento, '') <> 'inactivo'";
        }

        $from = " FROM request_attachments ra
                  INNER JOIN requests r ON r.id = ra.request_id
                  LEFT JOIN project_phases pp ON pp.id = ra.phase_id
                  LEFT JOIN document_types dt ON dt.id = ra.document_type_id
                  LEFT JOIN users u ON u.id = ra.subido_por
                  LEFT JOIN users ap ON ap.id = ra.aprobado_por ";

        $countStmt = $this->db->prepare("SELECT COUNT(*) AS total" . $from . $where);
        $countStmt->execute($params);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT
                    ra.*,
                    r.codigo,
                    r.titulo AS solicitud_titulo,
                    COALESCE(pp.nombre, 'Sin fase') AS fase_nombre,
                    pp.orden AS fase_orden,
                    COALESCE(dt.nombre, 'Sin tipo') AS tipo_documento,
                    dt.obligatorio,
                    u.nombre AS subido_por_nombre,
                    ap.nombre AS aprobado_por_nombre,
                    (
                        SELECT COUNT(*)
                        FROM request_attachments rav
                        WHERE rav.request_id = ra.request_id
                          AND rav.document_type_id <=> ra.document_type_id
                          AND rav.id <= ra.id
                    ) AS version_calculada
                " . $from . $where . "
                ORDER BY ra.created_at DESC, ra.id DESC
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

    public function findAttachment(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                ra.*,
                r.codigo,
                r.titulo AS solicitud_titulo,
                COALESCE(pp.nombre, 'Sin fase') AS fase_nombre,
                COALESCE(dt.nombre, 'Sin tipo') AS tipo_documento,
                u.nombre AS subido_por_nombre,
                ap.nombre AS aprobado_por_nombre
             FROM request_attachments ra
             INNER JOIN requests r ON r.id = ra.request_id
             LEFT JOIN project_phases pp ON pp.id = ra.phase_id
             LEFT JOIN document_types dt ON dt.id = ra.document_type_id
             LEFT JOIN users u ON u.id = ra.subido_por
             LEFT JOIN users ap ON ap.id = ra.aprobado_por
             WHERE ra.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function paginateTypes(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where = " WHERE 1=1 ";
        $params = [];

        if (!empty($filters['q'])) {
            $where .= " AND (dt.nombre LIKE :q OR dt.descripcion LIKE :q OR pp.nombre LIKE :q)";
            $params['q'] = '%' . trim((string) $filters['q']) . '%';
        }

        if (!empty($filters['phase_id'])) {
            $where .= " AND dt.fase_id = :phase_id";
            $params['phase_id'] = (int) $filters['phase_id'];
        }

        if ($filters['obligatorio'] !== null && $filters['obligatorio'] !== '') {
            $where .= " AND dt.obligatorio = :obligatorio";
            $params['obligatorio'] = (int) $filters['obligatorio'];
        }

        if ($filters['activo'] !== null && $filters['activo'] !== '') {
            $where .= " AND dt.activo = :activo";
            $params['activo'] = (int) $filters['activo'];
        }

        $from = " FROM document_types dt
                  LEFT JOIN project_phases pp ON pp.id = dt.fase_id ";

        $countStmt = $this->db->prepare("SELECT COUNT(*) AS total " . $from . $where);
        $countStmt->execute($params);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT
                    dt.id,
                    dt.nombre,
                    dt.fase_id,
                    dt.obligatorio,
                    dt.descripcion,
                    dt.archivo_referencia,
                    dt.archivo_original,
                    dt.archivo_mime,
                    dt.archivo_peso,
                    dt.activo,
                    dt.created_at,
                    dt.updated_at,
                    pp.nombre AS fase_nombre,
                    pp.orden AS fase_orden,
                    (
                        SELECT COUNT(*)
                        FROM request_attachments ra
                        WHERE ra.document_type_id = dt.id
                    ) AS usos
                " . $from . $where . "
                ORDER BY pp.orden ASC, dt.obligatorio DESC, dt.nombre ASC
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

    public function findType(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT dt.*, pp.nombre AS fase_nombre
             FROM document_types dt
             LEFT JOIN project_phases pp ON pp.id = dt.fase_id
             WHERE dt.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * @return int|false
     */
    public function createType(array $data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO document_types (nombre, fase_id, obligatorio, descripcion, activo, created_at, updated_at)
         VALUES (:nombre, :fase_id, :obligatorio, :descripcion, :activo, NOW(), NOW())"
        );

        $ok = $stmt->execute([
            'nombre' => trim((string) $data['nombre']),
            'fase_id' => (int) $data['fase_id'],
            'obligatorio' => (int) $data['obligatorio'],
            'descripcion' => $data['descripcion'] ?? null,
            'activo' => (int) $data['activo'],
        ]);

        return $ok ? (int) $this->db->lastInsertId() : false;
    }

    public function updateType(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE document_types
             SET nombre = :nombre,
                 fase_id = :fase_id,
                 obligatorio = :obligatorio,
                 descripcion = :descripcion,
                 activo = :activo,
                 updated_at = NOW()
             WHERE id = :id"
        );

        return $stmt->execute([
            'id' => $id,
            'nombre' => trim((string) $data['nombre']),
            'fase_id' => (int) $data['fase_id'],
            'obligatorio' => (int) $data['obligatorio'],
            'descripcion' => $data['descripcion'] ?? null,
            'activo' => (int) $data['activo'],
        ]);
    }

    public function uploadTypeFile(int $typeId, array $file): bool
    {
        $type = $this->findType($typeId);
        if (!$type) {
            return false;
        }

        $upload = $this->storeUploadedFile($typeId, $file, 'document_types/' . $typeId);
        if (!$upload) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE document_types
             SET archivo_referencia = :archivo_referencia,
                 archivo_original = :archivo_original,
                 archivo_mime = :archivo_mime,
                 archivo_peso = :archivo_peso,
                 updated_at = NOW()
             WHERE id = :id"
        );

        return $stmt->execute([
            'id' => $typeId,
            'archivo_referencia' => $upload['file_path'],
            'archivo_original' => $upload['original_name'],
            'archivo_mime' => $upload['mime_type'],
            'archivo_peso' => $upload['file_size'],
        ]);
    }

    public function clearTypeFile(int $typeId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE document_types
             SET archivo_referencia = NULL,
                 archivo_original = NULL,
                 archivo_mime = NULL,
                 archivo_peso = NULL,
                 updated_at = NOW()
             WHERE id = :id"
        );

        return $stmt->execute(['id' => $typeId]);
    }

    public function toggleType(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE document_types
             SET activo = CASE WHEN activo = 1 THEN 0 ELSE 1 END,
                 updated_at = NOW()
             WHERE id = :id"
        );

        return $stmt->execute(['id' => $id]);
    }

    public function allRequests(): array
    {
        return $this->db->query(
            "SELECT id, codigo, titulo
             FROM requests
             ORDER BY id DESC"
        )->fetchAll();
    }

    public function allPhases(): array
    {
        return $this->db->query(
            "SELECT id, nombre, orden
             FROM project_phases
             WHERE activo = 1
             ORDER BY orden ASC, id ASC"
        )->fetchAll();
    }

    public function allDocumentTypes(): array
    {
        return $this->db->query(
            "SELECT
                dt.id,
                dt.nombre,
                dt.fase_id,
                dt.obligatorio,
                dt.descripcion,
                pp.nombre AS fase_nombre,
                pp.orden AS fase_orden
             FROM document_types dt
             LEFT JOIN project_phases pp ON pp.id = dt.fase_id
             WHERE dt.activo = 1
             ORDER BY pp.orden ASC, dt.obligatorio DESC, dt.nombre ASC"
        )->fetchAll();
    }


    public function typeTemplatesByPhase(int $phaseId, ?int $categoryId = null): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                dt.id,
                dt.nombre,
                dt.fase_id,
                dt.obligatorio,
                dt.descripcion,
                dt.activo,

                CASE WHEN dt.archivo_base_ruta IS NOT NULL AND dt.archivo_base_ruta <> '' THEN dt.archivo_base_ruta ELSE dt.archivo_referencia END AS archivo_ruta,
                CASE WHEN dt.archivo_base_nombre IS NOT NULL AND dt.archivo_base_nombre <> '' THEN dt.archivo_base_nombre ELSE dt.archivo_original END AS archivo_nombre,
                CASE WHEN dt.archivo_base_mime IS NOT NULL AND dt.archivo_base_mime <> '' THEN dt.archivo_base_mime ELSE dt.archivo_mime END AS archivo_mime,
                COALESCE(dt.archivo_base_peso, dt.archivo_peso) AS archivo_peso,

                dt.archivo_base_ruta,
                dt.archivo_base_nombre,
                dt.archivo_base_mime,
                dt.archivo_base_peso,
                dt.archivo_referencia,
                dt.archivo_original,
                dt.archivo_mime,
                dt.archivo_peso,

                pp.nombre AS fase_nombre
             FROM document_types dt
             LEFT JOIN project_phases pp ON pp.id = dt.fase_id
                WHERE dt.fase_id = :phase_id
                AND dt.activo = 1
                AND (
                    dt.category_id IS NULL
                    OR dt.category_id = :category_id
                )
                AND (
                    dt.exclude_category_id IS NULL
                    OR dt.exclude_category_id <> :exclude_category_id
                )
             ORDER BY dt.obligatorio DESC, dt.nombre ASC"
        );

        $stmt->execute([
            'phase_id' => $phaseId,
            'category_id' => $categoryId,
            'exclude_category_id' => $categoryId,
        ]);
        return $stmt->fetchAll();
    }

    public function optionalTypeTemplatesBeforePhase(int $currentPhaseId, ?int $categoryId = null): array
    {
        $currentPhase = $this->db->prepare(
            "SELECT orden FROM project_phases WHERE id = :id LIMIT 1"
        );
        $currentPhase->execute(['id' => $currentPhaseId]);
        $current = $currentPhase->fetch();

        if (!$current) {
            return [];
        }

        $sql = "SELECT
                    dt.id,
                    dt.nombre,
                    dt.fase_id,
                    dt.obligatorio,
                    dt.descripcion,
                    dt.activo,
                    pp.nombre AS fase_nombre,
                    pp.orden AS fase_orden,

                    CASE
                        WHEN dt.archivo_base_ruta IS NOT NULL AND dt.archivo_base_ruta <> ''
                        THEN dt.archivo_base_ruta
                        ELSE dt.archivo_referencia
                    END AS archivo_ruta,

                    CASE
                        WHEN dt.archivo_base_nombre IS NOT NULL AND dt.archivo_base_nombre <> ''
                        THEN dt.archivo_base_nombre
                        ELSE dt.archivo_original
                    END AS archivo_nombre,

                    CASE
                        WHEN dt.archivo_base_mime IS NOT NULL AND dt.archivo_base_mime <> ''
                        THEN dt.archivo_base_mime
                        ELSE dt.archivo_mime
                    END AS archivo_mime,

                    COALESCE(dt.archivo_base_peso, dt.archivo_peso) AS archivo_peso,

                    dt.archivo_base_ruta,
                    dt.archivo_base_nombre,
                    dt.archivo_base_mime,
                    dt.archivo_base_peso,
                    dt.archivo_referencia,
                    dt.archivo_original,
                    dt.archivo_mime,
                    dt.archivo_peso
                FROM document_types dt
                INNER JOIN project_phases pp ON pp.id = dt.fase_id
                WHERE dt.activo = 1
                AND dt.obligatorio = 0
                AND pp.orden < :current_order";

        $params = [
            'current_order' => (int) $current['orden'],
        ];

        if ($categoryId !== null && $categoryId > 0) {
            $sql .= " AND (
            dt.category_id IS NULL
            OR dt.category_id = 0
            OR dt.category_id = :category_id
        )";

            $params['category_id'] = $categoryId;
        }

        $sql .= " ORDER BY pp.orden ASC, dt.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function setInactive(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE request_attachments
             SET estado_documento = 'inactivo', aprobado = 0
             WHERE id = :id"
        );

        return $stmt->execute(['id' => $id]);
    }

    public function reactivate(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE request_attachments
             SET estado_documento = 'pendiente', aprobado = 0, aprobado_por = NULL, fecha_aprobacion = NULL
             WHERE id = :id AND estado_documento = 'inactivo'"
        );

        return $stmt->execute(['id' => $id]);
    }

    private function uploadDebug(string $message): void
    {
        $logFile = __DIR__ . '/../../storage/logs/upload_debug.log';

        @file_put_contents(
            $logFile,
            '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL,
            FILE_APPEND
        );
    }
}
