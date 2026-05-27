<?php
function planningUrl(array $extra = []): string
{
    $params = array_merge($_GET, $extra);

    $params = array_filter($params, function ($v) {
        return $v !== null && $v !== '';
    });

    $query = http_build_query($params);

    return base_url('/planning' . ($query !== '' ? '?' . $query : ''));
}

function planningExportUrl(): string
{
    $params = array_filter($_GET, function ($v) {
        return $v !== null && $v !== '';
    });

    $query = http_build_query($params);

    return base_url('/planning/export' . ($query !== '' ? '?' . $query : ''));
}

function planEstadoBadge(string $estado): string
{
    switch ($estado) {
        case 'cerrado':
        case 'completado':
            return 'bg-success';

        case 'en_ejecucion':
        case 'en ejecución':
            return 'bg-primary';

        case 'pausado':
            return 'bg-warning text-dark';

        case 'cancelado':
            return 'bg-danger';

        default:
            return 'bg-secondary';
    }
}
?>

<style>
    .planning-kpi {
        border: 0;
        border-radius: 14px;
    }

    .planning-kpi .value {
        font-size: 1.7rem;
        font-weight: 800;
    }

    .planning-card {
        border: 0;
        border-radius: 14px;
    }

    .planning-alerts .badge {
        font-size: .72rem;
    }

    .planning-progress {
        min-width: 170px;
    }

    .planning-project-title {
        line-height: 1.25;
    }
</style>

<div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Planificación</h1>
            <div class="text-muted">Control de planes, tareas, hitos, atrasos y carga de trabajo.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= e(planningExportUrl()) ?>" class="btn btn-success">Exportar Excel</a>
            <a href="<?= e(base_url('/planning/create')) ?>" class="btn btn-primary">Nueva planificación</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card planning-kpi shadow-sm">
                <div class="card-body">
                    <div class="small text-muted">Planes</div>
                    <div class="value text-primary"><?= e((string) ($summary['total_planes'] ?? 0)) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card planning-kpi shadow-sm">
                <div class="card-body">
                    <div class="small text-muted">Planes atrasados</div>
                    <div class="value text-danger"><?= e((string) ($summary['planes_atrasados'] ?? 0)) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card planning-kpi shadow-sm">
                <div class="card-body">
                    <div class="small text-muted">Tareas pendientes</div>
                    <div class="value text-warning"><?= e((string) ($summary['tareas_pendientes'] ?? 0)) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card planning-kpi shadow-sm">
                <div class="card-body">
                    <div class="small text-muted">Avance promedio</div>
                    <div class="value text-success">
                        <?= e(number_format((float) ($summary['avance_promedio'] ?? 0), 1)) ?>%</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card planning-card shadow-sm mb-4">
        <div class="card-header bg-white">Filtros</div>
        <div class="card-body">
            <form method="GET" action="<?= e(base_url('/planning')) ?>" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="q" class="form-control" placeholder="Código, proyecto o plan"
                        value="<?= e($filters['q'] ?? '') ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Fase</label>
                    <select name="phase_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach (($phases ?? []) as $phase): ?>
                            <option value="<?= e((string) $phase['id']) ?>" <?= (string) ($filters['phase_id'] ?? '') === (string) $phase['id'] ? 'selected' : '' ?>>
                                <?= e($phase['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach (['planificado' => 'Planificado', 'en_ejecucion' => 'En ejecución', 'pausado' => 'Pausado', 'cerrado' => 'Cerrado', 'cancelado' => 'Cancelado'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= (string) ($filters['estado'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Responsable de tarea</label>
                    <select name="responsable_id" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach (($users ?? []) as $u): ?>
                            <option value="<?= e((string) $u['id']) ?>" <?= (string) ($filters['responsable_id'] ?? '') === (string) $u['id'] ? 'selected' : '' ?>>
                                <?= e($u['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">Filtrar</button>
                </div>

                <div class="col-12 d-flex flex-wrap gap-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="atrasados" value="1" id="atrasados"
                            <?= !empty($filters['atrasados']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="atrasados">Solo atrasados</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="sin_tareas" value="1" id="sin_tareas"
                            <?= !empty($filters['sin_tareas']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="sin_tareas">Sin tareas</label>
                    </div>
                    <a href="<?= e(base_url('/planning')) ?>" class="btn btn-outline-secondary btn-sm">Limpiar
                        filtros</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card planning-card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span>Listado de planes <span class="text-muted">(<?= e((string) count($plans ?? [])) ?>)</span></span>
        </div>
        <div class="card-body">
            <?php if (!empty($plans)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Proyecto</th>
                                <th>Plan</th>
                                <th>Fase</th>
                                <th>Periodo</th>
                                <th>Estado</th>
                                <th>Avance</th>
                                <th>Tareas / Hitos</th>
                                <th>Alertas</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($plans as $item): ?>
                                <?php
                                $avance = (int) ($item['avance_general'] ?? 0);
                                $totalTareas = (int) ($item['total_tareas'] ?? 0);
                                $tareasCompletadas = (int) ($item['tareas_completadas'] ?? 0);
                                $tareasAtrasadas = (int) ($item['tareas_atrasadas'] ?? 0);
                                $hitosAtrasados = (int) ($item['hitos_atrasados'] ?? 0);
                                $diasAtraso = (int) ($item['dias_atraso_plan'] ?? 0);
                                $sinTareas = $totalTareas === 0;
                                $horasEstimadas = (float) ($item['horas_estimadas'] ?? 0);
                                $horasReales = (float) ($item['horas_reales'] ?? 0);
                                $horasExcedidas = $horasEstimadas > 0 && $horasReales > $horasEstimadas;
                                ?>
                                <tr>
                                    <td>
                                        <div class="planning-project-title">
                                            <strong><?= e($item['codigo']) ?></strong><br>
                                            <?= e($item['proyecto']) ?>
                                        </div>
                                        <div class="small text-muted">Resp.: <?= e($item['responsable_solicitud'] ?? '-') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= e($item['nombre']) ?></strong>
                                        <?php if (!empty($item['descripcion'])): ?>
                                            <div class="small text-muted">
                                                <?= e(mb_strimwidth((string) $item['descripcion'], 0, 70, '...')) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-dark"><?= e($item['fase_nombre']) ?></span></td>
                                    <td>
                                        <div><?= e($item['fecha_inicio_plan'] ?? '-') ?> →
                                            <?= e($item['fecha_fin_plan'] ?? '-') ?></div>
                                        <?php if (!empty($item['dias_restantes'])): ?>
                                            <div class="small text-muted">Quedan <?= e((string) $item['dias_restantes']) ?> días
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= e(planEstadoBadge((string) ($item['estado'] ?? ''))) ?>">
                                            <?= e($item['estado']) ?>
                                        </span>
                                    </td>
                                    <td class="planning-progress">
                                        <div class="progress" style="height: 18px;">
                                            <div class="progress-bar" style="width: <?= e((string) $avance) ?>%;">
                                                <?= e((string) $avance) ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><strong><?= e((string) $tareasCompletadas) ?></strong> /
                                            <?= e((string) $totalTareas) ?> tareas</div>
                                        <div class="small text-muted"><?= e((string) ($item['total_hitos'] ?? 0)) ?> hitos</div>
                                        <div class="small text-muted">
                                            Hrs: <?= e(number_format($horasReales, 1)) ?> /
                                            <?= e(number_format($horasEstimadas, 1)) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="planning-alerts d-flex flex-wrap gap-1">
                                            <?php if ($diasAtraso > 0): ?>
                                                <span class="badge bg-danger"><?= e((string) $diasAtraso) ?> días atraso</span>
                                            <?php endif; ?>
                                            <?php if ($tareasAtrasadas > 0): ?>
                                                <span class="badge bg-danger"><?= e((string) $tareasAtrasadas) ?> tareas
                                                    atras.</span>
                                            <?php endif; ?>
                                            <?php if ($hitosAtrasados > 0): ?>
                                                <span class="badge bg-danger"><?= e((string) $hitosAtrasados) ?> hitos atras.</span>
                                            <?php endif; ?>
                                            <?php if ($sinTareas): ?>
                                                <span class="badge bg-warning text-dark">Sin tareas</span>
                                            <?php endif; ?>
                                            <?php if ($horasExcedidas): ?>
                                                <span class="badge bg-dark">Horas excedidas</span>
                                            <?php endif; ?>
                                            <?php if (!$diasAtraso && !$tareasAtrasadas && !$hitosAtrasados && !$sinTareas && !$horasExcedidas): ?>
                                                <span class="badge bg-success">OK</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex flex-wrap justify-content-end gap-2">
                                            <a href="<?= e(base_url('/planning/show?id=' . $item['id'])) ?>"
                                                class="btn btn-outline-primary btn-sm">Abrir</a>
                                            <a href="<?= e(base_url('/planning/gantt?id=' . $item['id'])) ?>"
                                                class="btn btn-success btn-sm">Gantt</a>

                                            <?php if ($sinTareas): ?>
                                                <a href="<?= e(base_url('/planning/show?id=' . $item['id'])) ?>"
                                                    class="btn btn-warning btn-sm">Agregar tareas</a>
                                            <?php elseif ($avance >= 100): ?>
                                                <a href="<?= e(base_url('/planning/show?id=' . $item['id'])) ?>"
                                                    class="btn btn-outline-success btn-sm">Revisar cierre</a>
                                            <?php elseif ($tareasAtrasadas > 0 || $hitosAtrasados > 0): ?>
                                                <a href="<?= e(base_url('/planning/show?id=' . $item['id'])) ?>"
                                                    class="btn btn-danger btn-sm">Resolver atraso</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted">No hay planificaciones registradas.</div>
            <?php endif; ?>
        </div>
    </div>
</div>