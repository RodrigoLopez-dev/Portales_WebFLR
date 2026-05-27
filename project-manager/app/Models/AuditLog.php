<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class AuditLog extends Model
{
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO audit_logs (
                user_id, user_name, user_role, action, module, entity_type, entity_id,
                description, old_values, new_values, ip_address, user_agent, url,
                method, severity, status
            ) VALUES (
                :user_id, :user_name, :user_role, :action, :module, :entity_type, :entity_id,
                :description, :old_values, :new_values, :ip_address, :user_agent, :url,
                :method, :severity, :status
            )'
        );

        return $stmt->execute([
            'user_id' => $data['user_id'] ?? null,
            'user_name' => $data['user_name'] ?? null,
            'user_role' => $data['user_role'] ?? null,
            'action' => $data['action'] ?? 'unknown',
            'module' => $data['module'] ?? 'system',
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'description' => $data['description'] ?? null,
            'old_values' => isset($data['old_values']) ? json_encode($data['old_values'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'new_values' => isset($data['new_values']) ? json_encode($data['new_values'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'url' => $data['url'] ?? null,
            'method' => $data['method'] ?? null,
            'severity' => $data['severity'] ?? 'info',
            'status' => $data['status'] ?? 'success',
        ]);
    }

    public function paginate(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $page = max(1, $page);
        $perPage = min(max(10, $perPage), 100);
        $offset = ($page - 1) * $perPage;

        [$where, $params] = $this->buildWhere($filters);

        $countStmt = $this->db->prepare('SELECT COUNT(*) FROM audit_logs ' . $where);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = 'SELECT * FROM audit_logs ' . $where . ' ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'rows' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => (int)ceil($total / $perPage),
        ];
    }

    private function buildWhere(array $filters): array
    {
        //$where = [];
        // 🔥 EXCLUIR accesos al módulo audit (ruido)
        $where[] = "NOT (module = 'audit' AND action = 'view')";

        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(description LIKE :q OR user_name LIKE :q OR action LIKE :q OR module LIKE :q OR entity_type LIKE :q OR entity_id LIKE :q)';
            $params[':q'] = '%' . trim((string)$filters['q']) . '%';
        }

        if (!empty($filters['module'])) {
            $where[] = 'module = :module';
            $params[':module'] = trim((string)$filters['module']);
        }

        if (!empty($filters['action'])) {
            $where[] = 'action = :action';
            $params[':action'] = trim((string)$filters['action']);
        }

        if (!empty($filters['severity'])) {
            $where[] = 'severity = :severity';
            $params[':severity'] = trim((string)$filters['severity']);
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= :date_from';
            $params[':date_from'] = trim((string)$filters['date_from']) . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= :date_to';
            $params[':date_to'] = trim((string)$filters['date_to']) . ' 23:59:59';
        }

        return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
    }
}
