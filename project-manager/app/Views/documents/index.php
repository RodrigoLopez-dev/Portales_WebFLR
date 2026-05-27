<?php
function docTypeFilterUrl(array $extra = []): string
{
    $params = array_merge($_GET, $extra);

    $params = array_filter($params, function ($v) {
        return $v !== null && $v !== '';
    });

    $query = http_build_query($params);

    return base_url('/documents' . ($query !== '' ? '?' . $query : ''));
}

$groupedByPhase = [];

foreach (($documentTypes ?? []) as $doc) {
    $phaseName = $doc['fase_nombre'] ?? 'Sin fase';
    $phaseOrder = (int)($doc['fase_orden'] ?? 999);
    $phaseKey = $phaseOrder . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $phaseName);

    if (!isset($groupedByPhase[$phaseKey])) {
        $groupedByPhase[$phaseKey] = [
            'name' => $phaseName,
            'order' => $phaseOrder,
            'items' => [],
        ];
    }

    $groupedByPhase[$phaseKey]['items'][] = $doc;
}

uasort($groupedByPhase, function ($a, $b) {
    $orderCompare = $a['order'] <=> $b['order'];

    if ($orderCompare !== 0) {
        return $orderCompare;
    }

    return strcmp($a['name'], $b['name']);
});

$totalDocs = (int)($pagination['total'] ?? count($documentTypes ?? []));
$page = (int)($pagination['page'] ?? 1);
$pages = (int)($pagination['pages'] ?? 1);
$perPage = (int)($pagination['per_page'] ?? 15);
?>

<style>
    .document-phase-card {
        border: 1px solid #dee2e6;
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 1rem;
        background: #fff;
    }

    .document-phase-header {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: .85rem 1rem;
    }

    .document-phase-toggle {
        width: 34px;
        height: 34px;
        font-size: 1.15rem;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }

    .document-phase-toggle .minus-icon {
        display: none;
    }

    .document-phase-toggle[aria-expanded="true"] .plus-icon {
        display: none;
    }

    .document-phase-toggle[aria-expanded="true"] .minus-icon {
        display: inline;
    }

    .document-phase-summary {
        font-size: .82rem;
        color: #6c757d;
    }

    .document-row-inactive {
        opacity: .65;
    }

    .document-description {
        font-size: .82rem;
    }

    .document-table th,
    .document-table td {
        vertical-align: middle;
    }
</style>

<div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Gestión Documental</h1>
            <div class="text-muted">Administra el catálogo de documentos requeridos por fase. Aquí no se cargan archivos por solicitud.</div>
        </div>

        <?php if (\App\Core\Auth::can('documents.review')): ?>
            <a href="<?= e(base_url('/documents/create')) ?>" class="btn btn-primary">Nuevo documento</a>
        <?php endif; ?>
    </div>

    <div class="alert alert-info">
        <strong>Importante:</strong> este módulo define qué documentos existen, en qué fase se solicitan y si son obligatorios.
        La carga de archivos sigue realizándose desde el avance de cada solicitud/proyecto.
    </div>

    <div class="card mb-4">
        <div class="card-header">Filtros del catálogo documental</div>
        <div class="card-body">
            <form method="GET" action="<?= e(base_url('/documents')) ?>" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="q" class="form-control" placeholder="Nombre, descripción o fase" value="<?= e($filters['q'] ?? '') ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fase</label>
                    <select name="phase_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach (($catalogs['phases'] ?? []) as $phase): ?>
                            <option value="<?= e($phase['id']) ?>" <?= selected($phase['id'], $filters['phase_id'] ?? '') ?>>
                                <?= e($phase['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Obligatorio</label>
                    <select name="obligatorio" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" <?= selected('1', $filters['obligatorio'] ?? '') ?>>Sí</option>
                        <option value="0" <?= selected('0', $filters['obligatorio'] ?? '') ?>>No</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="activo" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" <?= selected('1', $filters['activo'] ?? '') ?>>Activo</option>
                        <option value="0" <?= selected('0', $filters['activo'] ?? '') ?>>Inactivo</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <label class="form-label">Pág.</label>
                    <select name="per_page" class="form-select">
                        <?php foreach ([10, 15, 25, 50] as $n): ?>
                            <option value="<?= $n ?>" <?= ((int)($pagination['per_page'] ?? 15) === $n) ? 'selected' : '' ?>><?= $n ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="<?= e(base_url('/documents')) ?>" class="btn btn-outline-secondary">Limpiar</a>
                    <button class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>
                Catálogo documental agrupado por fase
                <span class="text-muted">(<?= e((string)$totalDocs) ?> registros)</span>
            </span>

            <?php if (!empty($groupedByPhase)): ?>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="expandAllDocumentPhases">Expandir todo</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="collapseAllDocumentPhases">Contraer todo</button>
                </div>
            <?php endif; ?>
        </div>

        <div class="card-body">
            <?php if (!empty($groupedByPhase)): ?>
                <?php $phaseIndex = 0; ?>
                <?php foreach ($groupedByPhase as $phaseKey => $group): ?>
                    <?php
                        $phaseIndex++;
                        $collapseId = 'documentPhaseGroup' . $phaseIndex;
                        $items = $group['items'];
                        $count = count($items);
                        $requiredCount = 0;
                        $optionalCount = 0;
                        $activeCount = 0;
                        $inactiveCount = 0;
                        $usageCount = 0;
                        $fileCount = 0;

                        foreach ($items as $item) {
                            if (!empty($item['obligatorio'])) {
                                $requiredCount++;
                            } else {
                                $optionalCount++;
                            }

                            if ((int)($item['activo'] ?? 0) === 1) {
                                $activeCount++;
                            } else {
                                $inactiveCount++;
                            }

                            $usageCount += (int)($item['usos'] ?? 0);

                            if (!empty($item['archivo_referencia'])) {
                                $fileCount++;
                            }
                        }

                        $isFirstOpen = false;
                    ?>

                    <div class="document-phase-card">
                        <div class="document-phase-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <button
                                    class="btn btn-outline-dark btn-sm document-phase-toggle"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?= e($collapseId) ?>"
                                    aria-expanded="<?= $isFirstOpen ? 'true' : 'false' ?>"
                                    aria-controls="<?= e($collapseId) ?>"
                                    title="Mostrar/Ocultar documentos"
                                >
                                    <span class="plus-icon">+</span>
                                    <span class="minus-icon">−</span>
                                </button>

                                <div>
                                    <div class="fw-bold fs-6">
                                        <?= e($group['name']) ?>
                                        <span class="badge bg-secondary ms-1"><?= e((string)$count) ?></span>
                                    </div>
                                    <div class="document-phase-summary">
                                        <?= e((string)$requiredCount) ?> obligatorio(s) ·
                                        <?= e((string)$optionalCount) ?> opcional(es) ·
                                        <?= e((string)$activeCount) ?> activo(s)
                                        <?php if ($inactiveCount > 0): ?>
                                            · <span class="text-muted"><?= e((string)$inactiveCount) ?> inactivo(s)</span>
                                        <?php endif; ?>
                                        · <?= e((string)$usageCount) ?> uso(s)
                                        <?php if ($fileCount > 0): ?>
                                            · <span class="text-primary"><?= e((string)$fileCount) ?> con archivo</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <?php if ($requiredCount > 0): ?>
                                    <span class="badge bg-danger">Obligatorios: <?= e((string)$requiredCount) ?></span>
                                <?php endif; ?>
                                <?php if ($optionalCount > 0): ?>
                                    <span class="badge bg-secondary">Opcionales: <?= e((string)$optionalCount) ?></span>
                                <?php endif; ?>
                                <?php if ($inactiveCount > 0): ?>
                                    <span class="badge bg-dark">Inactivos: <?= e((string)$inactiveCount) ?></span>
                                <?php endif; ?>
                                <?php if ($fileCount > 0): ?>
                                    <span class="badge bg-primary">Con archivo: <?= e((string)$fileCount) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="collapse <?= $isFirstOpen ? 'show' : '' ?>" id="<?= e($collapseId) ?>">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 document-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 35%;">Documento</th>
                                            <th>Archivo</th>
                                            <th>Obligatorio</th>
                                            <th>Estado</th>
                                            <th>Usos</th>
                                            <th>Actualizado</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $doc): ?>
                                            <?php $isActive = (int)($doc['activo'] ?? 0) === 1; ?>
                                            <tr class="<?= $isActive ? '' : 'document-row-inactive table-light text-muted' ?>">
                                                <td>
                                                    <div class="fw-bold"><?= e($doc['nombre']) ?></div>
                                                    <?php if (!empty($doc['descripcion'])): ?>
                                                        <div class="document-description text-muted"><?= e($doc['descripcion']) ?></div>
                                                    <?php else: ?>
                                                        <div class="document-description text-muted">Sin descripción registrada.</div>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <?php if (!empty($doc['archivo_referencia'])): ?>
                                                        <div class="fw-semibold small"><?= e($doc['archivo_original'] ?? 'Archivo asociado') ?></div>
                                                        <?php if (!empty($doc['archivo_peso'])): ?>
                                                            <div class="small text-muted"><?= e(number_format(((int)$doc['archivo_peso']) / 1024, 1)) ?> KB</div>
                                                        <?php endif; ?>
                                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                                            <a href="<?= e(base_url('/documents/type-file?id=' . $doc['id'] . '&mode=view')) ?>" target="_blank" class="btn btn-outline-primary btn-sm">Ver</a>
                                                            <a href="<?= e(base_url('/documents/type-file?id=' . $doc['id'] . '&mode=download')) ?>" class="btn btn-success btn-sm">Descargar</a>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark border">Sin archivo</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <?php if (!empty($doc['obligatorio'])): ?>
                                                        <span class="badge bg-danger">Obligatorio</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Opcional</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <?php if ($isActive): ?>
                                                        <span class="badge bg-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <span class="badge bg-light text-dark border"><?= e((string)($doc['usos'] ?? 0)) ?></span>
                                                </td>

                                                <td><?= e($doc['updated_at'] ?? '-') ?></td>

                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end flex-wrap gap-2">
                                                        <?php if (\App\Core\Auth::can('documents.review')): ?>
                                                            <a href="<?= e(base_url('/documents/edit?id=' . $doc['id'])) ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                                                            <form method="POST" action="<?= e(base_url('/documents/toggle')) ?>" onsubmit="return confirm('¿Confirmas cambiar el estado de este tipo documental?');">
                                                                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                                <input type="hidden" name="id" value="<?= e($doc['id']) ?>">
                                                                <button class="btn btn-outline-secondary btn-sm">
                                                                    <?= $isActive ? 'Inactivar' : 'Activar' ?>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="small text-muted">Página <?= e((string)$page) ?> de <?= e((string)$pages) ?></div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= e(docTypeFilterUrl(['page' => max(1, $page - 1), 'per_page' => $perPage])) ?>">Anterior</a>
                            </li>
                            <?php for ($i = 1; $i <= $pages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= e(docTypeFilterUrl(['page' => $i, 'per_page' => $perPage])) ?>"><?= e((string)$i) ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= e(docTypeFilterUrl(['page' => min($pages, $page + 1), 'per_page' => $perPage])) ?>">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php else: ?>
                <div class="alert alert-light mb-0">No hay tipos documentales registrados.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const collapseElements = Array.from(document.querySelectorAll('[id^="documentPhaseGroup"]'));

    document.getElementById('expandAllDocumentPhases')?.addEventListener('click', function () {
        collapseElements.forEach(function (element) {
            bootstrap.Collapse.getOrCreateInstance(element, { toggle: false }).show();
        });
    });

    document.getElementById('collapseAllDocumentPhases')?.addEventListener('click', function () {
        collapseElements.forEach(function (element) {
            bootstrap.Collapse.getOrCreateInstance(element, { toggle: false }).hide();
        });
    });
});
</script>
