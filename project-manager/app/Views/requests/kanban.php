<?php
function kanbanBadgeClass(string $type, ?string $value): string
{
    $value = mb_strtolower(trim((string) $value));

    if ($type === 'priority') {
        switch ($value) {
            case 'alta':
            case 'crítica':
            case 'critica':
                return 'bg-danger';

            case 'media':
                return 'bg-warning text-dark';

            case 'baja':
                return 'bg-success';

            default:
                return 'bg-secondary';
        }
    }

    if ($type === 'status') {
        switch ($value) {
            case 'aprobada':
            case 'aprobado':
            case 'cerrada':
            case 'implementada':
                return 'bg-success';

            case 'rechazada':
            case 'rechazado':
            case 'bloqueada':
            case 'observada':
                return 'bg-danger';

            case 'en revisión':
            case 'en revision':
            case 'en pruebas':
            case 'planificada':
                return 'bg-warning text-dark';

            case 'ingresada':
            case 'en desarrollo':
            case 'en análisis funcional':
            case 'en analisis funcional':
            case 'en evaluación técnica':
            case 'en evaluacion tecnica':
                return 'bg-primary';

            default:
                return 'bg-secondary';
        }
    }

    return 'bg-secondary';
}

function kanbanPriorityClass(?string $priority): string
{
    $priority = mb_strtolower(trim((string) $priority));

    switch ($priority) {
        case 'alta':
        case 'crítica':
        case 'critica':
            return 'priority-alta';

        case 'media':
            return 'priority-media';

        case 'baja':
            return 'priority-baja';

        default:
            return '';
    }
}
?>

<style>
    .kanban-board-title {
        font-weight: 800;
        margin-bottom: 1rem;
    }

    .kanban-toolbar {
        border: 0;
        border-radius: 16px;
    }

    .kanban-toolbar .form-control,
    .kanban-toolbar .form-select,
    .kanban-toolbar .btn {
        height: 42px;
    }

    .kanban-column-card {
        border: 1px solid #dee2e6;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 .125rem .35rem rgba(0, 0, 0, .04);
    }

    .kanban-column-card.blocked-column {
        border-color: #f5c2c7;
    }

    .kanban-column-header {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: .8rem 1rem;
    }

    .kanban-column-card.blocked-column .kanban-column-header {
        background: #fff5f5;
    }

    .kanban-column-title {
        font-size: .95rem;
        font-weight: 800;
    }

    .kanban-column-summary {
        font-size: .75rem;
        color: #6c757d;
        margin-top: .15rem;
    }

    .kanban-column-body {
        background: #f8f9fa;
        min-height: 330px;
        max-height: 620px;
        overflow-y: auto;
        padding: .75rem;
    }

    .kanban-column-card.blocked-column .kanban-column-body {
        background: #fffafa;
    }

    .kanban-item {
        border: 1px solid #e9ecef;
        border-left: 5px solid #0d6efd;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 .125rem .35rem rgba(0, 0, 0, .06);
        margin-bottom: .85rem;
        overflow: hidden;
        transition: transform .12s ease, box-shadow .12s ease;
        padding: 10px;
    }

    .kanban-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 .45rem 1rem rgba(0, 0, 0, .09);
    }

    .kanban-item.priority-alta {
        border-left-color: #dc3545;
    }

    .kanban-item.priority-media {
        border-left-color: #ffc107;
    }

    .kanban-item.priority-baja {
        border-left-color: #198754;
    }

    .kanban-item.is-blocked {
        border-left-color: #dc3545;
        background: #fff8f8;
    }

    .kanban-code {
        font-size: .72rem;
        color: #6c757d;
        font-weight: 700;
    }

    .kanban-title {
        font-size: .98rem;
        font-weight: 800;
        line-height: 1.25;
        margin-bottom: .45rem;
    }

    .kanban-desc {
        font-size: .78rem;
        color: #6c757d;
        line-height: 1.3;
    }

    .kanban-badges .badge {
        font-size: .68rem;
        padding: .38rem .5rem;
    }

    .kanban-alert {
        border-radius: 10px;
        padding: .45rem .55rem;
        font-size: .74rem;
        font-weight: 700;
        margin-bottom: .45rem;
    }

    .kanban-alert-danger {
        border: 1px solid #f5c2c7;
        background: #fff5f5;
        color: #842029;
    }

    .kanban-alert-warning {
        border: 1px solid #ffecb5;
        background: #fff8e1;
        color: #664d03;
    }

    .kanban-alert-info {
        border: 1px solid #b6effb;
        background: #f0fbff;
        color: #055160;
    }

    .kanban-meta {
        font-size: .78rem;
        line-height: 1.35;
    }

    .kanban-progress-label {
        font-size: .76rem;
        font-weight: 700;
        color: #495057;
    }

    .kanban-actions .btn {
        min-width: 82px;
    }

    .kanban-empty {
        color: #6c757d;
        font-size: .85rem;
    }

    .kanban-flow-note {
        font-size: .8rem;
        color: #6c757d;
    }
</style>

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h2 class="kanban-board-title mb-1">Tablero Kanban Pro por Fases</h2>
        <div class="kanban-flow-note">
            Las solicitudes bloqueadas se separan en una columna especial. El bloqueo es manual y no cambia la fase
            original.
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= e(base_url('/requests')) ?>" class="btn btn-outline-primary btn-sm">Ver listado</a>
        <a href="<?= e(base_url('/requests/create')) ?>" class="btn btn-primary btn-sm">Nueva solicitud</a>
    </div>
</div>

<div class="card mb-4 kanban-toolbar shadow-sm">
    <div class="card-body">
        <form method="GET" action="<?= e(base_url('/requests/kanban')) ?>" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control"
                    placeholder="Buscar por código, título o descripción..."
                    value="<?= htmlspecialchars($filters['q'] ?? ($_GET['q'] ?? '')) ?>">
            </div>

            <?php if (!empty($catalogs['priorities'])): ?>
                <div class="col-md-3">
                    <select name="prioridad_id" class="form-select">
                        <option value="">Prioridad</option>
                        <?php foreach ($catalogs['priorities'] as $p): ?>
                            <option value="<?= (int) $p['id'] ?>" <?= (string) ($filters['prioridad_id'] ?? '') === (string) $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (!empty($catalogs['users'])): ?>
                <div class="col-md-3">
                    <select name="responsable_id" class="form-select">
                        <option value="">Responsable</option>
                        <?php foreach ($catalogs['users'] as $u): ?>
                            <option value="<?= (int) $u['id'] ?>" <?= (string) ($filters['responsable_id'] ?? '') === (string) $u['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <?php foreach ($board as $column): ?>
        <?php
        $items = $column['items'] ?? [];
        $countItems = count($items);
        $isBlockedColumn = !empty($column['is_blocked_column']);

        $avgProgress = $countItems > 0
            ? round(array_sum(array_map(function ($it) {
                return (int) ($it['porcentaje_avance'] ?? 0);
            }, $items)) / $countItems)
            : 0;

        $overdueCount = 0;
        $blockedCount = 0;
        $docsPendingCount = 0;
        $docsRejectedCount = 0;

        foreach ($items as $it) {
            $overdueCount += (int) ($it['dias_atraso'] ?? 0) > 0 ? 1 : 0;

            $motivo = trim((string) ($it['motivo_bloqueo'] ?? ''));
            $estado = mb_strtolower((string) ($it['estado'] ?? ''));

            if ($motivo !== '' || $estado === 'bloqueada') {
                $blockedCount++;
            }

            $docsPendingCount += (int) ($it['docs_pendientes'] ?? 0) > 0 ? 1 : 0;
            $docsRejectedCount += (int) ($it['docs_rechazados'] ?? 0) > 0 ? 1 : 0;
        }
        ?>

        <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
            <div class="kanban-column-card h-100 <?= $isBlockedColumn ? 'blocked-column' : '' ?>">
                <div class="kanban-column-header d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kanban-column-title">
                            <?= htmlspecialchars($column['phase']['nombre'] ?? 'Sin fase') ?>
                        </div>

                        <div class="kanban-column-summary">
                            <?= $countItems ?> solicitud(es) · Prom. <?= $avgProgress ?>%

                            <?php if ($blockedCount > 0): ?>
                                · <span class="text-danger"><?= $blockedCount ?> bloq.</span>
                            <?php endif; ?>

                            <?php if ($overdueCount > 0): ?>
                                · <span class="text-danger"><?= $overdueCount ?> atras.</span>
                            <?php endif; ?>

                            <?php if ($docsPendingCount > 0): ?>
                                · <span class="text-info"><?= $docsPendingCount ?> docs pend.</span>
                            <?php endif; ?>

                            <?php if ($docsRejectedCount > 0): ?>
                                · <span class="text-danger"><?= $docsRejectedCount ?> docs rech.</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <span class="badge <?= $isBlockedColumn ? 'bg-danger' : 'bg-secondary' ?>">
                        <?= $countItems ?>
                    </span>
                </div>

                <div class="kanban-column-body">
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                            <?php
                            $id = (int) ($item['id'] ?? 0);
                            $avance = (int) ($item['porcentaje_avance'] ?? 0);
                            $estado = $item['estado'] ?? '-';
                            $fase = $item['fase'] ?? ($column['phase']['nombre'] ?? '-');
                            $responsable = $item['responsable'] ?? '-';
                            $prioridadLabel = $item['prioridad'] ?? '-';

                            $motivoBloqueo = trim((string) ($item['motivo_bloqueo'] ?? ''));
                            $bloqueada = $motivoBloqueo !== '' || mb_strtolower((string) $estado) === 'bloqueada';

                            $docsPendientes = (int) ($item['docs_pendientes'] ?? 0);
                            $docsRechazados = (int) ($item['docs_rechazados'] ?? 0);
                            $diasAtraso = (int) ($item['dias_atraso'] ?? 0);
                            $horasExcedidas = (int) ($item['horas_excedidas'] ?? 0) === 1;

                            $modalBlockId = 'kanbanBlockRequestModal' . $id;
                            $modalUnblockId = 'kanbanUnblockRequestModal' . $id;

                            $priorityClass = kanbanPriorityClass($prioridadLabel);
                            ?>

                            <div class="kanban-item <?= $priorityClass ?> <?= $bloqueada ? 'is-blocked' : '' ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div class="kanban-code"><?= htmlspecialchars($item['codigo'] ?? '') ?></div>

                                        <?php if ($diasAtraso > 0): ?>
                                            <span class="badge bg-danger"><?= $diasAtraso ?>d atraso</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="kanban-title">
                                        <?= htmlspecialchars($item['titulo'] ?? '') ?>
                                    </div>

                                    <div class="kanban-badges d-flex flex-wrap gap-2 mb-2">
                                        <span class="badge bg-dark"><?= htmlspecialchars($fase) ?></span>
                                        <span
                                            class="badge <?= kanbanBadgeClass('status', $estado) ?>"><?= htmlspecialchars($estado) ?></span>
                                        <span class="badge <?= kanbanBadgeClass('priority', $prioridadLabel) ?>">
                                            <?= htmlspecialchars($prioridadLabel) ?>
                                        </span>

                                        <?php if ($bloqueada): ?>
                                            <span class="badge bg-danger">Bloqueada</span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($bloqueada): ?>
                                        <div class="kanban-alert kanban-alert-danger">
                                            🚫 Bloqueada
                                            <?php if ($motivoBloqueo !== ''): ?>
                                                <br><?= htmlspecialchars(mb_strimwidth($motivoBloqueo, 0, 110, '...')) ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($docsRechazados > 0): ?>
                                        <div class="kanban-alert kanban-alert-danger">
                                            ❌ <?= $docsRechazados ?> documento(s) rechazado(s)
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($docsPendientes > 0): ?>
                                        <div class="kanban-alert kanban-alert-info">
                                            📄 <?= $docsPendientes ?> documento(s) pendiente(s)
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($horasExcedidas): ?>
                                        <div class="kanban-alert kanban-alert-warning">
                                            ⏱ Horas excedidas:
                                            <?= number_format((float) ($item['esfuerzo_real_horas'] ?? 0), 1) ?>
                                            /
                                            <?= number_format((float) ($item['esfuerzo_estimado_horas'] ?? 0), 1) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="kanban-meta mb-2">
                                        <strong>Responsable:</strong> <?= htmlspecialchars($responsable) ?>
                                    </div>

                                    <?php if (!empty($item['descripcion'])): ?>
                                        <div class="kanban-desc mb-2">
                                            <?= htmlspecialchars(mb_strimwidth((string) $item['descripcion'], 0, 120, '...')) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="kanban-progress-label">Avance</span>
                                        <span class="kanban-progress-label"><?= $avance ?>%</span>
                                    </div>

                                    <div class="progress mt-1 mb-3" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?= $avance ?>%;"
                                            aria-valuenow="<?= $avance ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>

                                    <?php if ($bloqueada): ?>
                                        <div class="small text-danger fw-bold mb-2">
                                            ⛔ No debería avanzar hasta resolver el bloqueo.
                                        </div>
                                    <?php endif; ?>

                                    <div class="kanban-actions d-flex flex-wrap gap-2">
                                        <a href="<?= e(base_url('/requests/history?id=' . $id)) ?>"
                                            class="btn btn-outline-primary btn-sm">
                                            Ver
                                        </a>

                                        <?php if ($bloqueada): ?>
                                            <a href="<?= e(base_url('/requests/edit?id=' . $id)) ?>" class="btn btn-danger btn-sm">
                                                Resolver
                                            </a>
                                        <?php elseif ($docsRechazados > 0 || $docsPendientes > 0): ?>
                                            <a href="<?= e(base_url('/requests/advance?id=' . $id)) ?>" class="btn btn-warning btn-sm">
                                                Docs
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= e(base_url('/requests/advance?id=' . $id)) ?>" class="btn btn-success btn-sm">
                                                Gestionar
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($bloqueada): ?>
                                            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#<?= e($modalUnblockId) ?>">
                                                Desbloquear
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#<?= e($modalBlockId) ?>">
                                                Bloquear
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!$bloqueada): ?>
                                        <div class="modal fade" id="<?= e($modalBlockId) ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="POST" action="<?= e(base_url('/requests/block')) ?>"
                                                    class="modal-content">
                                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                    <input type="hidden" name="request_id" value="<?= e((string) $id) ?>">
                                                    <input type="hidden" name="return_to"
                                                        value="<?= e('/requests/kanban' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')) ?>">

                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            Bloquear solicitud <?= htmlspecialchars($item['codigo'] ?? '') ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Cerrar"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <label class="form-label">Motivo del bloqueo</label>
                                                        <textarea name="motivo_bloqueo" class="form-control" rows="4" required
                                                            placeholder="Ejemplo: Esperando aprobación externa, información faltante, proveedor pendiente..."></textarea>
                                                        <div class="form-text">
                                                            El bloqueo es manual y no cambia la fase de la solicitud.
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                                            Cancelar
                                                        </button>
                                                        <button class="btn btn-danger">
                                                            Bloquear
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="modal fade" id="<?= e($modalUnblockId) ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="POST" action="<?= e(base_url('/requests/unblock')) ?>"
                                                    class="modal-content">
                                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                    <input type="hidden" name="request_id" value="<?= e((string) $id) ?>">
                                                    <input type="hidden" name="return_to"
                                                        value="<?= e('/requests/kanban' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')) ?>">

                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            Desbloquear solicitud <?= htmlspecialchars($item['codigo'] ?? '') ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Cerrar"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <?php if ($motivoBloqueo !== ''): ?>
                                                            <div class="alert alert-warning mb-0">
                                                                <strong>Motivo actual:</strong><br>
                                                                <?= htmlspecialchars($motivoBloqueo) ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="alert alert-warning mb-0">
                                                                Esta solicitud está marcada como bloqueada por su estado actual.
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                                            Cancelar
                                                        </button>
                                                        <button class="btn btn-success">
                                                            Desbloquear
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="kanban-empty">Sin solicitudes</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>