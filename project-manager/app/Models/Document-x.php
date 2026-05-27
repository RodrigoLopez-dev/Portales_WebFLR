<?php
namespace App\Models;

use App\Core\Model;

class Document extends Model
{
    public function requiredByPhase(int $requestId, int $phaseId): array
    {
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
             WHERE dt.fase_id = :fase_id
             GROUP BY dt.id, dt.nombre, dt.obligatorio
             ORDER BY dt.obligatorio DESC, dt.nombre ASC"
        );

        $stmt->execute([
            'request_id' => $requestId,
            'fase_id' => $phaseId
        ]);

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
        if (empty($file['name']) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        $dir = __DIR__ . '/../../public/uploads/requests/' . $requestId . '/documents';

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }

        $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', basename($file['name']));
        $name = date('Ymd_His') . '_' . uniqid() . '_' . $safeName;
        $dest = $dir . '/' . $name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return false;
        }

        $path = 'uploads/requests/' . $requestId . '/documents/' . $name;

        $stmt = $this->db->prepare(
            "INSERT INTO request_attachments
            (request_id, phase_id, document_type_id, nombre_original, ruta_archivo, tipo_mime, peso, estado_documento, aprobado, subido_por, created_at)
            VALUES (:r,:p,:t,:n,:ruta,:mime,:peso,'pendiente',0,:u,NOW())"
        );

        return $stmt->execute([
            'r' => $requestId,
            'p' => $phaseId,
            't' => $typeId,
            'n' => $file['name'],
            'ruta' => $path,
            'mime' => $file['type'] ?? null,
            'peso' => (int)($file['size'] ?? 0),
            'u' => $userId
        ]);
    }

    public function approve(int $id, int $userId, string $estado): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE request_attachments
             SET estado_documento = :e,
                 aprobado = :a,
                 aprobado_por = :u,
                 fecha_aprobacion = NOW()
             WHERE id = :id"
        );

        return $stmt->execute([
            'e' => $estado,
            'a' => $estado === 'aprobado' ? 1 : 0,
            'u' => $userId,
            'id' => $id
        ]);
    }
}
