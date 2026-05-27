<?php
function requestFilterUrl(array $extra = []): string
{
    $params = array_merge($_GET, $extra);

    $params = array_filter($params, function ($v) {
        return $v !== null && $v !== '';
    });

    return base_url('/requests' . (!empty($params) ? '?' . http_build_query($params) : ''));
}

function requestExportUrl(): string
{
    $params = array_filter($_GET, function ($v) {
        return $v !== null && $v !== '';
    });

    return base_url('/requests/export' . (!empty($params) ? '?' . http_build_query($params) : ''));
}

function requestPhaseBadgeClass(?string $fase): string
{
    switch ($fase ?? '') {
        case 'Solicitud':
            return 'bg-secondary';

        case 'Levantamiento':
            return 'bg-info text-dark';

        case 'Formalización':
            return 'bg-primary';

        case 'Diseño':
        case 'Desarrollo':
            return 'bg-warning text-dark';

        case 'QA':
            return 'bg-dark';

        case 'Paso a Producción':
        case 'Cierre':
            return 'bg-success';

        default:
            return 'bg-secondary';
    }
}
?>

<div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Solicitudes</h1>

        <div class="d-flex gap-2">
            <a href="<?= e(requestExportUrl()) ?>" class="btn btn-success">
                Exportar Excel
            </a>
            <a href="<?= e(base_url('/requests/create')) ?>" class="btn btn-primary">
                Nueva solicitud
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Filtros avanzados</div>
        <div class="card-body">
            <form method="GET" action="<?= e(base_url('/requests')) ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="q" class="form-control" placeholder="Código, título o descripción"
                        value="<?= e($filters['q'] ?? '') ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado_id" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach (($catalogs['statuses'] ?? []) as $item): ?>
                            <option value="<?= e($item['id']) ?>" <?= ((string) ($filters['estado_id'] ?? '') === (string) $item['id']) ? 'selected' : '' ?>>
                                <?= e($item['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>


                <div class="col-md-2">
                    <label class="form-label">Categoría</label>
                    <select id="filter_category" name="category_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach (($catalogs['categories'] ?? []) as $item): ?>
                            <option value="<?= e($item['id']) ?>" <?= ((string) ($filters['category_id'] ?? '') === (string) $item['id']) ? 'selected' : '' ?>>
                                <?= e($item['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>


                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select id="filter_tipo" name="tipo_id" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach (($catalogs['types'] ?? []) as $item): ?>
                            <option value="<?= e($item['id']) ?>" <?= ((string) ($filters['tipo_id'] ?? '') === (string) $item['id']) ? 'selected' : '' ?>>
                                <?= e($item['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Prioridad</label>
                    <select name="prioridad_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach (($catalogs['priorities'] ?? []) as $item): ?>
                            <option value="<?= e($item['id']) ?>" <?= ((string) ($filters['prioridad_id'] ?? '') === (string) $item['id']) ? 'selected' : '' ?>>
                                <?= e($item['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Responsable</label>
                    <select name="responsable_id" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach (($catalogs['users'] ?? []) as $item): ?>
                            <option value="<?= e($item['id']) ?>" <?= ((string) ($filters['responsable_id'] ?? '') === (string) $item['id']) ? 'selected' : '' ?>>
                                <?= e($item['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fase</label>
                    <select name="phase_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach (($catalogs['phases'] ?? []) as $item): ?>
                            <option value="<?= e($item['id']) ?>" <?= ((string) ($filters['phase_id'] ?? '') === (string) $item['id']) ? 'selected' : '' ?>>
                                <?= e($item['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Por página</label>
                    <select name="per_page" class="form-select">
                        <?php foreach ([10, 25, 50] as $n): ?>
                            <option value="<?= $n ?>" <?= ((int) ($pagination['per_page'] ?? 10) === $n) ? 'selected' : '' ?>>
                                <?= $n ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-7 d-flex align-items-end flex-wrap gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="bloqueadas" value="1" id="bloqueadas"
                            <?= !empty($filters['bloqueadas']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="bloqueadas">Solo bloqueadas</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="atrasadas" value="1" id="atrasadas"
                            <?= !empty($filters['atrasadas']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="atrasadas">Solo atrasadas</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="pendientes_doc" value="1"
                            id="pendientes_doc" <?= !empty($filters['pendientes_doc']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="pendientes_doc">Docs pendientes</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="rechazados_doc" value="1"
                            id="rechazados_doc" <?= !empty($filters['rechazados_doc']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="rechazados_doc">Docs rechazados</label>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="<?= e(base_url('/requests')) ?>" class="btn btn-outline-secondary">Limpiar</a>
                    <button class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                Listado
                <span class="text-muted">
                    (<?= e((string) ($pagination['total'] ?? count($requests))) ?> registros)
                </span>
            </span>

            <div class="d-flex gap-2">
                <a href="<?= e(base_url('/requests/kanban')) ?>" class="btn btn-outline-dark btn-sm">Ver Kanban</a>
            </div>
        </div>

        <div class="card-body">
            <?php if (!empty($requests)): ?>
                <div class="w-100">
                    <table class="table table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Título</th>
                                <th>Categoría</th>
                                <th>Tipo</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Responsable</th>
                                <th>Fase</th>
                                <th>Avance</th>
                                <th>Horas</th>
                                <th>Fecha req.</th>
                                <th>Alertas</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($requests as $r): ?>
                                <?php
                                $id = (int) ($r['id'] ?? 0);
                                $avance = (int) ($r['porcentaje_avance'] ?? 0);
                                $docsPendientes = (int) ($r['docs_pendientes'] ?? 0);
                                $docsRechazados = (int) ($r['docs_rechazados'] ?? 0);
                                $diasAtraso = (int) ($r['dias_atraso'] ?? 0);
                                $horasExcedidas = (int) ($r['horas_excedidas'] ?? 0) === 1;
                                $motivoBloqueo = trim((string) ($r['motivo_bloqueo'] ?? ''));
                                $bloqueada = $motivoBloqueo !== '' || mb_strtolower((string) ($r['estado'] ?? '')) === 'bloqueada';
                                $fase = (string) ($r['fase'] ?? '');
                                $estadoColor = $r['estado_color'] ?? 'secondary';
                                $modalBlockId = 'blockRequestModal' . $id;
                                $modalUnblockId = 'unblockRequestModal' . $id;
                                ?>

                                <tr>
                                    <td>
                                        <a href="<?= e(base_url('/requests/history?id=' . $id)) ?>"
                                            class="fw-bold text-decoration-none">
                                            <?= e($r['codigo'] ?? '') ?>
                                        </a>
                                    </td>

                                    <td>
                                        <div class="fw-semibold"><?= e($r['titulo'] ?? '') ?></div>
                                        <?php if (!empty($r['descripcion'])): ?>
                                            <div class="small text-muted">
                                                <?= e(mb_strimwidth((string) $r['descripcion'], 0, 70, '...')) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td><?= e($r['categoria'] ?? '-') ?></td>
                                    <td><?= e($r['tipo'] ?? '-') ?></td>

                                    <td><?= e($r['prioridad'] ?? '-') ?></td>

                                    <td>
                                        <span class="badge bg-<?= e($estadoColor ?: 'secondary') ?>">
                                            <?= e($r['estado'] ?? '-') ?>
                                        </span>
                                    </td>

                                    <td><?= e($r['responsable'] ?? '-') ?></td>

                                    <td>
                                        <span class="badge <?= e(requestPhaseBadgeClass($fase)) ?>">
                                            <?= e($fase ?: 'Sin fase') ?>
                                        </span>
                                    </td>

                                    <td style="min-width: 160px;">
                                        <div class="progress" style="height: 16px;">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: <?= e((string) $avance) ?>%;">
                                                <?= e((string) $avance) ?>%
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <?= e(number_format((float) ($r['esfuerzo_real_horas'] ?? 0), 1)) ?>
                                        /
                                        <?= e(number_format((float) ($r['esfuerzo_estimado_horas'] ?? 0), 1)) ?>
                                    </td>

                                    <td><?= e($r['fecha_requerida'] ?? '-') ?></td>

                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php if ($bloqueada): ?>
                                                <span class="badge bg-warning text-dark"
                                                    title="<?= e($motivoBloqueo ?: 'Solicitud marcada como bloqueada') ?>">Bloqueada</span>
                                            <?php endif; ?>

                                            <?php if ($diasAtraso > 0): ?>
                                                <span class="badge bg-danger"><?= e((string) $diasAtraso) ?> días atraso</span>
                                            <?php endif; ?>

                                            <?php if ($docsPendientes > 0): ?>
                                                <span class="badge bg-info text-dark"><?= e((string) $docsPendientes) ?> doc.
                                                    pend.</span>
                                            <?php endif; ?>

                                            <?php if ($docsRechazados > 0): ?>
                                                <span class="badge bg-danger"><?= e((string) $docsRechazados) ?> doc. rech.</span>
                                            <?php endif; ?>

                                            <?php if ($horasExcedidas): ?>
                                                <span class="badge bg-dark">Horas excedidas</span>
                                            <?php endif; ?>

                                            <?php if (!$bloqueada && $diasAtraso <= 0 && $docsPendientes <= 0 && $docsRechazados <= 0 && !$horasExcedidas): ?>
                                                <span class="badge bg-success">OK</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="text-end">
                                        <div class="d-flex justify-content-end flex-wrap gap-2">
                                            <a href="<?= e(base_url('/requests/history?id=' . $id)) ?>"
                                                class="btn btn-outline-primary btn-sm">
                                                Ver
                                            </a>

                                            <?php if ($bloqueada): ?>
                                                <button type="button" class="btn btn-secondary btn-sm" disabled
                                                    title="<?= e($motivoBloqueo ?: 'Solicitud bloqueada') ?>">
                                                    Bloqueado
                                                </button>
                                            <?php elseif ($docsRechazados > 0 || $docsPendientes > 0): ?>
                                                <a href="<?= e(base_url('/requests/advance?id=' . $id)) ?>"
                                                    class="btn btn-warning btn-sm">
                                                    Gestionar docs
                                                </a>
                                            <?php elseif ($fase === 'Cierre' && (int) ($r['es_final'] ?? 0) === 1): ?>
                                                <a href="<?= e(base_url('/requests/history?id=' . $id)) ?>"
                                                    class="btn btn-outline-secondary btn-sm">
                                                    Revisar cierre
                                                </a>
                                            <?php elseif ($fase === 'Cierre'): ?>
                                                <a href="<?= e(base_url('/requests/advance?id=' . $id)) ?>"
                                                    class="btn btn-success btn-sm">
                                                    Gestionar cierre
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= e(base_url('/requests/advance?id=' . $id)) ?>"
                                                    class="btn btn-success btn-sm">
                                                    Avanzar
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

                                            <?php if ($bloqueada): ?>
                                                <button class="btn btn-outline-secondary btn-sm" disabled
                                                    title="<?= e($motivoBloqueo ?: 'Solicitud bloqueada, no editable') ?>">
                                                    Editar
                                                </button>
                                            <?php elseif (($r['fase'] ?? '') === 'Cierre'): ?>
                                                <button class="btn btn-outline-secondary btn-sm" disabled
                                                    title="Proyecto cerrado, no editable">
                                                    Editar
                                                </button>
                                            <?php else: ?>
                                                <a href="<?= e(base_url('/requests/edit?id=' . $id)) ?>"
                                                    class="btn btn-outline-secondary btn-sm">
                                                    Editar
                                                </a>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!$bloqueada): ?>
                                            <div class="modal fade text-start" id="<?= e($modalBlockId) ?>" tabindex="-1"
                                                aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form method="POST" action="<?= e(base_url('/requests/block')) ?>"
                                                        class="modal-content">
                                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                        <input type="hidden" name="request_id" value="<?= e((string) $id) ?>">
                                                        <input type="hidden" name="return_to"
                                                            value="<?= e('/requests' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')) ?>">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Bloquear solicitud <?= e($r['codigo'] ?? '') ?>
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Cerrar"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label class="form-label">Motivo del bloqueo</label>
                                                            <textarea name="motivo_bloqueo" class="form-control" rows="4" required
                                                                placeholder="Ejemplo: Esperando aprobación externa, proveedor pendiente, información faltante..."></textarea>
                                                            <div class="form-text">El bloqueo es manual y no cambia la fase de la
                                                                solicitud.</div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                data-bs-dismiss="modal">Cancelar</button>
                                                            <button class="btn btn-danger">Bloquear</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="modal fade text-start" id="<?= e($modalUnblockId) ?>" tabindex="-1"
                                                aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form method="POST" action="<?= e(base_url('/requests/unblock')) ?>"
                                                        class="modal-content">
                                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                        <input type="hidden" name="request_id" value="<?= e((string) $id) ?>">
                                                        <input type="hidden" name="return_to"
                                                            value="<?= e('/requests' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')) ?>">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Desbloquear solicitud
                                                                <?= e($r['codigo'] ?? '') ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Cerrar"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php if ($motivoBloqueo !== ''): ?>
                                                                <div class="alert alert-warning">
                                                                    <strong>Motivo actual:</strong><br>
                                                                    <?= e($motivoBloqueo) ?>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="alert alert-warning">
                                                                    Esta solicitud está marcada como bloqueada por su estado actual.
                                                                </div>
                                                            <?php endif; ?>

                                                            <div class="mb-3">
                                                                <label class="form-label">Motivo de desbloqueo</label>
                                                                <textarea name="motivo_desbloqueo" class="form-control" rows="3"
                                                                    required
                                                                    placeholder="Ejemplo: Se recibió la información pendiente, se corrigió el impedimento o gerencia autorizó continuar."></textarea>
                                                                <div class="form-text">
                                                                    Este motivo quedará registrado en auditoría.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                data-bs-dismiss="modal">Cancelar</button>
                                                            <button class="btn btn-success">Desbloquear</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                $page = (int) ($pagination['page'] ?? 1);
                $pages = (int) ($pagination['pages'] ?? 1);
                $perPage = (int) ($pagination['per_page'] ?? 10);
                ?>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Página <?= e((string) $page) ?> de <?= e((string) $pages) ?>
                    </div>

                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="<?= e(requestFilterUrl(['page' => max(1, $page - 1), 'per_page' => $perPage])) ?>">
                                    Anterior
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $pages; $i++): ?>
                                <?php if ($i === 1 || $i === $pages || abs($i - $page) <= 2): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link"
                                            href="<?= e(requestFilterUrl(['page' => $i, 'per_page' => $perPage])) ?>">
                                            <?= e((string) $i) ?>
                                        </a>
                                    </li>
                                <?php elseif (abs($i - $page) === 3): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="<?= e(requestFilterUrl(['page' => min($pages, $page + 1), 'per_page' => $perPage])) ?>">
                                    Siguiente
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php else: ?>
                <div class="alert alert-light mb-0">
                    No hay solicitudes registradas.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</div>
</div>

<script>
    const filterProjectTypes = <?= json_encode($catalogs['types'] ?? [], JSON_UNESCAPED_UNICODE) ?>;

    const filterCategorySelect = document.getElementById('filter_category');
    const filterTipoSelect = document.getElementById('filter_tipo');
    const selectedFilterTipoId = "<?= e((string) ($filters['tipo_id'] ?? '')) ?>";

    function loadFilterTiposByCategory(categoryId) {
        let html = '<option value="">Todos</option>';

        filterProjectTypes
            .filter(type => !categoryId || String(type.category_id) === String(categoryId))
            .forEach(type => {
                const selected = String(type.id) === String(selectedFilterTipoId) ? 'selected' : '';
                html += `<option value="${type.id}" ${selected}>${type.nombre}</option>`;
            });

        filterTipoSelect.innerHTML = html;
    }

    filterCategorySelect.addEventListener('change', function () {
        loadFilterTiposByCategory(this.value);
    });

    loadFilterTiposByCategory(filterCategorySelect.value);
</script>