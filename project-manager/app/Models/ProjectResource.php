<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class ProjectResource extends Model
{
    public const TYPES = [
        'documento' => 'Documento anexo',
        'imagen' => 'Imagen',
        'video' => 'Video',
        'transcripcion' => 'Transcripción',
        'acta' => 'Acta / minuta',
        'presentacion' => 'Presentación',
        'logo' => 'Imagen corporativa / logo',
        'enlace' => 'Enlace externo',
        'otro' => 'Otro',
    ];

    public function types(): array
    {
        return self::TYPES;
    }

    public function byRequest(int $requestId, bool $includeInactive = false): array
    {
        $where = 'pr.request_id = :request_id';
        if (!$includeInactive) {
            $where .= ' AND pr.is_active = 1';
        }

        $stmt = $this->db->prepare(
            "SELECT pr.*, u.nombre AS uploaded_by_name, iu.nombre AS inactivated_by_name
             FROM project_resources pr
             LEFT JOIN users u ON u.id = pr.uploaded_by
             LEFT JOIN users iu ON iu.id = pr.inactivated_by
             WHERE {$where}
             ORDER BY pr.created_at DESC, pr.id DESC"
        );
        $stmt->execute(['request_id' => $requestId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT pr.*, u.nombre AS uploaded_by_name, iu.nombre AS inactivated_by_name
             FROM project_resources pr
             LEFT JOIN users u ON u.id = pr.uploaded_by
             LEFT JOIN users iu ON iu.id = pr.inactivated_by
             WHERE pr.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data, ?array $file = null): bool
    {
        $requestId = (int) ($data['request_id'] ?? 0);
        $uploadedBy = (int) ($data['uploaded_by'] ?? 0);
        $resourceType = $this->normalizeType((string) ($data['resource_type'] ?? 'otro'));
        $title = trim((string) ($data['title'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $externalUrl = trim((string) ($data['external_url'] ?? ''));
        $isPublic = !empty($data['is_public']) ? 1 : 0;

        if ($requestId <= 0 || $uploadedBy <= 0 || $title === '') {
            return false;
        }

        $filePath = null;
        $originalName = null;
        $mimeType = null;
        $fileSize = null;

        if ($file && !empty($file['name']) && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $upload = $this->storeFile($requestId, $file);
            if (!$upload) {
                return false;
            }
            $filePath = $upload['file_path'];
            $originalName = $upload['original_name'];
            $mimeType = $upload['mime_type'];
            $fileSize = $upload['file_size'];
        }

        if ($filePath === null && $externalUrl === '') {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO project_resources
             (request_id, uploaded_by, resource_type, title, description, file_path, original_name, external_url, mime_type, file_size, is_public, is_active, created_at, updated_at)
             VALUES
             (:request_id, :uploaded_by, :resource_type, :title, :description, :file_path, :original_name, :external_url, :mime_type, :file_size, :is_public, 1, NOW(), NOW())"
        );

        return $stmt->execute([
            'request_id' => $requestId,
            'uploaded_by' => $uploadedBy,
            'resource_type' => $resourceType,
            'title' => $title,
            'description' => $description !== '' ? $description : null,
            'file_path' => $filePath,
            'original_name' => $originalName,
            'external_url' => $externalUrl !== '' ? $externalUrl : null,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'is_public' => $isPublic,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $resourceType = $this->normalizeType((string) ($data['resource_type'] ?? 'otro'));
        $title = trim((string) ($data['title'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $externalUrl = trim((string) ($data['external_url'] ?? ''));
        $isPublic = !empty($data['is_public']) ? 1 : 0;

        if ($id <= 0 || $title === '') {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE project_resources
             SET resource_type = :resource_type,
                 title = :title,
                 description = :description,
                 external_url = :external_url,
                 is_public = :is_public,
                 updated_at = NOW()
             WHERE id = :id"
        );

        return $stmt->execute([
            'id' => $id,
            'resource_type' => $resourceType,
            'title' => $title,
            'description' => $description !== '' ? $description : null,
            'external_url' => $externalUrl !== '' ? $externalUrl : null,
            'is_public' => $isPublic,
        ]);
    }

    public function inactivate(int $id, int $userId): bool
    {
        if ($id <= 0 || $userId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE project_resources
             SET is_active = 0,
                 inactivated_by = :inactivated_by,
                 inactivated_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id
               AND is_active = 1"
        );

        return $stmt->execute([
            'id' => $id,
            'inactivated_by' => $userId,
        ]);
    }

    public function delete(int $id): bool
    {
        // Por trazabilidad y seguridad, el material complementario no se elimina físicamente.
        // Este método se mantiene como alias seguro para compatibilidad con llamadas existentes.
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE project_resources
             SET is_active = 0,
                 inactivated_at = COALESCE(inactivated_at, NOW()),
                 updated_at = NOW()
             WHERE id = :id
               AND is_active = 1"
        );

        return $stmt->execute(['id' => $id]);
    }

    public function groupedByType(int $requestId): array
    {
        $rows = $this->byRequest($requestId);
        $grouped = [];
        foreach ($rows as $row) {
            $type = $row['resource_type'] ?? 'otro';
            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][] = $row;
        }
        return $grouped;
    }

    private function normalizeType(string $type): string
    {
        return array_key_exists($type, self::TYPES) ? $type : 'otro';
    }

    private function storeFile(int $requestId, array $file): ?array
    {
        $originalName = basename((string) ($file['name'] ?? ''));
        $tmpPath = (string) ($file['tmp_name'] ?? '');
        $fileSize = (int) ($file['size'] ?? 0);
        $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($uploadError !== UPLOAD_ERR_OK || $originalName === '' || $tmpPath === '' || !is_uploaded_file($tmpPath)) {
            return null;
        }

        // 1. Limitar tamaño máximo
        $maxBytes = 20 * 1024 * 1024; // 20 MB

        if ($fileSize <= 0 || $fileSize > $maxBytes) {
            return null;
        }

        // 2. Obtener extensión real del nombre original
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($extension === '') {
            return null;
        }

        // 3. Bloquear extensiones peligrosas
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
            return null;
        }

        // 4. Permitir solo extensiones esperadas
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
            return null;
        }

        // 5. Validar MIME real del archivo, no confiar en $_FILES['type']
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if (!$finfo) {
            return null;
        }

        $mime = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        if (!$mime) {
            return null;
        }

        // 6. Whitelist de MIME permitidos
        $allowedMimeTypes = [
            'pdf' => ['application/pdf'],

            'doc' => ['application/msword'],
            'docx' => [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip',
            ],

            'xls' => [
                'application/vnd.ms-excel',
                'application/octet-stream',
            ],
            'xlsx' => [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/zip',
            ],

            'ppt' => [
                'application/vnd.ms-powerpoint',
                'application/octet-stream',
            ],
            'pptx' => [
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/zip',
            ],

            'txt' => ['text/plain'],
            'csv' => [
                'text/plain',
                'text/csv',
                'application/csv',
                'application/vnd.ms-excel',
            ],

            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
        ];

        if (!isset($allowedMimeTypes[$extension]) || !in_array($mime, $allowedMimeTypes[$extension], true)) {
            return null;
        }

        // 7. Validar que las imágenes realmente sean imágenes
        if (strpos($mime, 'image/') === 0 && @getimagesize($tmpPath) === false) {
            return null;
        }

        // 8. Crear carpeta destino
        $dir = __DIR__ . '/../../public/uploads/requests/' . $requestId . '/resources';

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return null;
        }

        // 9. Renombrar archivo: nunca usar directamente el nombre original para guardar
        try {
            $safeFileName = bin2hex(random_bytes(16)) . '.' . $extension;
        } catch (\Throwable $e) {
            $safeFileName = uniqid('resource_', true) . '.' . $extension;
        }

        $dest = $dir . '/' . $safeFileName;

        if (!move_uploaded_file($tmpPath, $dest)) {
            return null;
        }

        return [
            'file_path' => 'uploads/requests/' . $requestId . '/resources/' . $safeFileName,
            'original_name' => $originalName,
            'mime_type' => $mime,
            'file_size' => $fileSize,
        ];
    }
}
