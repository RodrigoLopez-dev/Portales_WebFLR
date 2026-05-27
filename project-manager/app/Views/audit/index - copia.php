<?php require __DIR__ . '/../layouts/header.php'; ?>

<?php
$formatAuditJson = static function ($json): string {
    if (empty($json)) {
        return '-';
    }
    $data = json_decode((string)$json, true);
    if (!is_array($data) || empty($data)) {
        return '-';
    }

    $lines = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $lines[] = '<strong>' . e((string)$key) . ':</strong> ' . e((string)($value ?? '-'));
    }
    return implode('<br>', $lines);
};
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Auditoría del sistema</h1>
        <p class="text-muted mb-0">Registro de acciones, cambios y movimientos relevantes del sistema.</p>
    </div>
</div>

<form class="card mb-3" method="get" action="<?= e(base_url('/audit')) ?>">
    <div class="card-body row g-3">
        <div class="col-md-3">
            <label class="form-label">Buscar</label>
            <input type="text" name="q" class="form-control" value="<?= e($filters['q'] ?? '') ?>" placeholder="usuario, acción, descripción">
        </div>
        <div class="col-md-2">
            <label class="form-label">Módulo</label>
            <input type="text" name="module" class="form-control" value="<?= e($filters['module'] ?? '') ?>" placeholder="requests, planning, documents">
        </div>
        <div class="col-md-2">
            <label class="form-label">Acción</label>
            <input type="text" name="action" class="form-control" value="<?= e($filters['action'] ?? '') ?>" placeholder="create, update, task_updated">
        </div>
        <div class="col-md-2">
            <label class="form-label">Criticidad</label>
            <select name="severity" class="form-select">
                <option value="">Todas</option>
                <?php foreach (['info', 'warning', 'critical'] as $severity): ?>
                    <option value="<?= e($severity) ?>" <?= (($filters['severity'] ?? '') === $severity) ? 'selected' : '' ?>><?= e($severity) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2 align-items-end">
            <button class="btn btn-primary" type="submit">Filtrar</button>
            <a class="btn btn-outline-secondary" href="<?= e(base_url('/audit')) ?>">Limpiar</a>
        </div>
        <div class="col-md-2">
            <label class="form-label">Desde</label>
            <input type="date" name="date_from" class="form-control" value="<?= e($filters['date_from'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Hasta</label>
            <input type="date" name="date_to" class="form-control" value="<?= e($filters['date_to'] ?? '') ?>">
        </div>
    </div>
</form>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-sm table-striped align-middle">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Acción</th>
                    <th>Módulo</th>
                    <th>Entidad</th>
                    <th>Descripción</th>
                    <th>Antes</th>
                    <th>Después</th>
                    <th>IP</th>
                    <th>Nivel</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach (($result['rows'] ?? []) as $row): ?>
                <tr>
                    <td class="text-nowrap"><?= e($row['created_at']) ?></td>
                    <td><?= e($row['user_name'] ?? 'Sistema') ?></td>
                    <td><?= e($row['user_role'] ?? '-') ?></td>
                    <td><span class="badge text-bg-secondary"><?= e($row['action']) ?></span></td>
                    <td><?= e($row['module']) ?></td>
                    <td><?= e(($row['entity_type'] ?? '-') . (!empty($row['entity_id']) ? ' #' . $row['entity_id'] : '')) ?></td>
                    <td style="min-width:260px"><?= e($row['description'] ?? '') ?></td>
                    <td style="min-width:220px" class="small"><?= $formatAuditJson($row['old_values'] ?? null) ?></td>
                    <td style="min-width:220px" class="small"><?= $formatAuditJson($row['new_values'] ?? null) ?></td>
                    <td class="text-nowrap"><?= e($row['ip_address'] ?? '-') ?></td>
                    <td><?= e($row['severity']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($result['rows'])): ?>
                <tr><td colspan="11" class="text-center text-muted py-4">No hay registros para los filtros seleccionados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (($result['pages'] ?? 1) > 1): ?>
    <nav class="mt-3">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
                <?php $query = array_merge($_GET, ['page' => $i]); ?>
                <li class="page-item <?= $i === $result['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= e(base_url('/audit?' . http_build_query($query))) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
