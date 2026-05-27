<?php
$avanceGeneral = max(0, min(100, (int) ($plan['avance_general'] ?? 0)));

$planEstado = (string) ($plan['estado'] ?? '');

switch ($planEstado) {
    case 'completado':
    case 'cerrado':
        $planEstadoClass = 'success';
        break;

    case 'en_ejecucion':
    case 'en ejecución':
        $planEstadoClass = 'primary';
        break;

    case 'atrasado':
        $planEstadoClass = 'danger';
        break;

    case 'pausado':
        $planEstadoClass = 'warning text-dark';
        break;

    case 'cancelado':
        $planEstadoClass = 'dark';
        break;

    default:
        $planEstadoClass = 'secondary';
        break;
}

$taskStatusBadge = static function (?string $estado): string {
    switch ((string) $estado) {
        case 'pendiente':
            return 'secondary';

        case 'en_progreso':
        case 'en progreso':
            return 'primary';

        case 'completada':
            return 'success';

        case 'atrasada':
            return 'danger';

        default:
            return 'dark';
    }
};

$priorityBadge = static function (?string $prioridad): string {
    switch ((string) $prioridad) {
        case 'alta':
            return 'danger';

        case 'media':
            return 'warning text-dark';

        case 'baja':
            return 'secondary';

        default:
            return 'secondary';
    }
};

$milestoneStatusBadge = static function (?string $estado): string {
    switch ((string) $estado) {
        case 'completado':
        case 'completada':
            return 'success';

        case 'en_progreso':
        case 'en progreso':
            return 'primary';

        case 'pendiente':
            return 'secondary';

        case 'atrasado':
        case 'atrasada':
            return 'danger';

        default:
            return 'secondary';
    }
};
?>

<div class="planning-show-page mt-4 mb-5">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-1">Planificación del proyecto</h1>
            <div class="text-muted">Gestión de hitos, tareas, avance operativo y trazabilidad del plan.</div>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= e(base_url('/planning/gantt?id=' . ($plan['id'] ?? 0))) ?>"
                class="btn btn-success btn-sm px-3">Ver Gantt</a>
            <a href="<?= e(base_url('/planning')) ?>" class="btn btn-outline-secondary btn-sm px-3">Volver</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 planning-summary-card">
        <div class="card-body p-4">
            <div class="row g-3 align-items-start">
                <div class="col-md-5">
                    <small class="text-muted d-block">Proyecto</small>
                    <div class="fw-semibold text-dark">
                        <?= e($plan['codigo']) ?> - <?= e($plan['proyecto']) ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block">Plan</small>
                    <div class="fw-semibold text-dark"><?= e($plan['nombre']) ?></div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted d-block">Estado del plan</small>
                    <span class="badge bg-<?= e($planEstadoClass) ?> text-uppercase">
                        <?= e($planEstado ?: '-') ?>
                    </span>
                </div>

                <div class="col-md-3">
                    <small class="text-muted d-block">Fase actual</small>
                    <span class="badge bg-dark"><?= e($plan['fase_nombre']) ?></span>
                </div>

                <div class="col-md-3">
                    <small class="text-muted d-block">Inicio plan</small>
                    <div class="fw-semibold"><?= e($plan['fecha_inicio_plan'] ?? '-') ?></div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted d-block">Fin plan</small>
                    <div class="fw-semibold"><?= e($plan['fecha_fin_plan'] ?? '-') ?></div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted d-block">Horas estimadas</small>
                    <div class="fw-semibold"><?= e(number_format((float) ($stats['horas_estimadas'] ?? 0), 2)) ?></div>
                </div>
            </div>

            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">Avance general</span>
                    <span class="fw-bold text-primary"><?= e((string) $avanceGeneral) ?>%</span>
                </div>
                <div class="progress planning-progress-lg">
                    <div class="progress-bar progress-bar-striped <?= $avanceGeneral < 100 ? 'progress-bar-animated' : '' ?>"
                        role="progressbar" style="width: <?= e((string) $avanceGeneral) ?>%;"
                        aria-valuenow="<?= e((string) $avanceGeneral) ?>" aria-valuemin="0" aria-valuemax="100">
                        <?= e((string) $avanceGeneral) ?>%
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4 planning-kpis">
        <div class="col-sm-6 col-lg-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Total tareas</small>
                    <div class="fs-3 fw-bold text-dark"><?= e((string) ($stats['total_tareas'] ?? 0)) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Pendientes</small>
                    <div class="fs-3 fw-bold text-secondary"><?= e((string) ($stats['pendientes'] ?? 0)) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">En progreso</small>
                    <div class="fs-3 fw-bold text-primary"><?= e((string) ($stats['en_progreso'] ?? 0)) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Completadas</small>
                    <div class="fs-3 fw-bold text-success"><?= e((string) ($stats['completadas'] ?? 0)) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Atrasadas</small>
                    <div class="fs-3 fw-bold text-danger"><?= e((string) ($stats['atrasadas'] ?? 0)) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Horas est.</small>
                    <div class="fs-3 fw-bold text-warning">
                        <?= e(number_format((float) ($stats['horas_estimadas'] ?? 0), 2)) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm border-0 mb-4 planning-card">
                <div class="card-header bg-white fw-bold">Agregar hito</div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= e(base_url('/planning/milestone/store')) ?>" class="row g-3">
                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="project_plan_id" value="<?= e($plan['id']) ?>">

                        <div class="col-12">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha hito</label>
                            <input type="date" name="fecha_hito" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Orden</label>
                            <input type="number" name="orden" class="form-control" value="0">
                        </div>

                        <div class="col-12">
                            <button class="btn btn-primary px-3">Guardar hito</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 planning-card">
                <div class="card-header bg-white fw-bold">Hitos</div>
                <div class="card-body p-4">
                    <?php if (!empty($milestones)): ?>
                        <div class="planning-milestone-list">
                            <?php foreach ($milestones as $item): ?>
                                <div class="planning-milestone-item">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <div class="fw-semibold"><?= e($item['nombre']) ?></div>
                                            <small class="text-muted">Fecha: <?= e($item['fecha_hito'] ?? '-') ?></small>
                                        </div>
                                        <span class="badge bg-<?= e($milestoneStatusBadge($item['estado'] ?? '')) ?>">
                                            <?= e($item['estado'] ?? '-') ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($item['descripcion'])): ?>
                                        <div class="small text-muted mt-2"><?= e($item['descripcion']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">No hay hitos registrados.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm border-0 mb-4 planning-card">
                <div class="card-header bg-white fw-bold">Agregar tarea</div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= e(base_url('/planning/task/store')) ?>" class="row g-3">
                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="project_plan_id" value="<?= e($plan['id']) ?>">

                        <div class="col-md-8">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Hito</label>
                            <select name="milestone_id" class="form-select">
                                <option value="">Sin hito</option>
                                <?php foreach (($milestones ?? []) as $item): ?>
                                    <option value="<?= e($item['id']) ?>"><?= e($item['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Responsable</label>
                            <select name="responsable_id" class="form-select">
                                <option value="">Sin asignar</option>
                                <?php foreach (($users ?? []) as $item): ?>
                                    <option value="<?= e($item['id']) ?>"><?= e($item['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_progreso">En progreso</option>
                                <option value="completada">Completada</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Prioridad</label>
                            <select name="prioridad" class="form-select">
                                <option value="alta">Alta</option>
                                <option value="media" selected>Media</option>
                                <option value="baja">Baja</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Inicio plan</label>
                            <input type="date" name="fecha_inicio_plan" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Fin plan</label>
                            <input type="date" name="fecha_fin_plan" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Horas estimadas</label>
                            <input type="number" step="0.01" name="duracion_estimada_horas" class="form-control"
                                value="0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Orden</label>
                            <input type="number" name="orden" class="form-control" value="0">
                        </div>

                        <div class="col-12">
                            <button class="btn btn-success px-3">Guardar tarea</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4 planning-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Tareas</strong>
                    <span class="badge bg-secondary"><?= e((string) count($tasks ?? [])) ?> registro(s)</span>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($tasks)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle planning-task-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tarea</th>
                                        <th>Hito</th>
                                        <th>Responsable</th>
                                        <th>Estado</th>
                                        <th>Prioridad</th>
                                        <th>Plan</th>
                                        <th>Horas</th>
                                        <th>Avance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tasks as $task): ?>
                                        <?php
                                        $taskAvance = max(0, min(100, (int) ($task['avance'] ?? 0)));
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?= e($task['nombre']) ?></div>
                                                <?php if (!empty($task['descripcion'])): ?>
                                                    <small class="text-muted"><?= e($task['descripcion']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= e($task['milestone_nombre']) ?></td>
                                            <td><?= e($task['responsable_nombre']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= e($taskStatusBadge($task['estado'] ?? '')) ?>">
                                                    <?= e($task['estado']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= e($priorityBadge($task['prioridad'] ?? '')) ?>">
                                                    <?= e($task['prioridad']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="d-block"><?= e($task['fecha_inicio_plan'] ?? '-') ?></small>
                                                <small
                                                    class="d-block text-muted"><?= e($task['fecha_fin_plan'] ?? '-') ?></small>
                                            </td>
                                            <td>
                                                <?= e(number_format((float) $task['duracion_real_horas'], 2)) ?>
                                                /
                                                <?= e(number_format((float) $task['duracion_estimada_horas'], 2)) ?>
                                            </td>
                                            <td style="min-width: 150px;">
                                                <div class="progress planning-progress-sm">
                                                    <div class="progress-bar" style="width: <?= e((string) $taskAvance) ?>%;">
                                                        <?= e((string) $taskAvance) ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="planning-task-edit-row">
                                            <td colspan="8">
                                                <form method="POST" action="<?= e(base_url('/planning/task/update')) ?>"
                                                    class="row g-2 align-items-end">
                                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                    <input type="hidden" name="id" value="<?= e($task['id']) ?>">
                                                    <input type="hidden" name="project_plan_id" value="<?= e($plan['id']) ?>">

                                                    <div class="col-md-2">
                                                        <label class="form-label small text-muted">Hito</label>
                                                        <select name="milestone_id" class="form-select form-select-sm">
                                                            <option value="">Sin hito</option>
                                                            <?php foreach (($milestones ?? []) as $item): ?>
                                                                <option value="<?= e($item['id']) ?>"
                                                                    <?= (string) $task['milestone_id'] === (string) $item['id'] ? 'selected' : '' ?>>
                                                                    <?= e($item['nombre']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <label class="form-label small text-muted">Responsable</label>
                                                        <select name="responsable_id" class="form-select form-select-sm">
                                                            <option value="">Sin asignar</option>
                                                            <?php foreach (($users ?? []) as $item): ?>
                                                                <option value="<?= e($item['id']) ?>"
                                                                    <?= (string) $task['responsable_id'] === (string) $item['id'] ? 'selected' : '' ?>>
                                                                    <?= e($item['nombre']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <label class="form-label small text-muted">Estado</label>
                                                        <select name="estado" class="form-select form-select-sm">
                                                            <option value="pendiente" <?= $task['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                            <option value="en_progreso" <?= $task['estado'] === 'en_progreso' ? 'selected' : '' ?>>En progreso</option>
                                                            <option value="completada" <?= $task['estado'] === 'completada' ? 'selected' : '' ?>>Completada</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-1">
                                                        <label class="form-label small text-muted">Avance</label>
                                                        <input type="number" name="avance" class="form-control form-control-sm"
                                                            min="0" max="100" value="<?= e((string) $taskAvance) ?>">
                                                    </div>

                                                    <div class="col-md-2">
                                                        <label class="form-label small text-muted">Horas reales</label>
                                                        <input type="number" step="0.01" name="duracion_real_horas"
                                                            class="form-control form-control-sm"
                                                            value="<?= e(number_format((float) $task['duracion_real_horas'], 2, '.', '')) ?>">
                                                    </div>

                                                    <div class="col-md-2">
                                                        <label class="form-label small text-muted">Fin real</label>
                                                        <input type="date" name="fecha_fin_real"
                                                            class="form-control form-control-sm"
                                                            value="<?= e($task['fecha_fin_real'] ?? '') ?>">
                                                    </div>

                                                    <div class="col-md-1 d-grid">
                                                        <button class="btn btn-outline-primary btn-sm">Actualizar</button>
                                                    </div>

                                                    <input type="hidden" name="nombre" value="<?= e($task['nombre']) ?>">
                                                    <input type="hidden" name="descripcion"
                                                        value="<?= e($task['descripcion'] ?? '') ?>">
                                                    <input type="hidden" name="prioridad" value="<?= e($task['prioridad']) ?>">
                                                    <input type="hidden" name="fecha_inicio_plan"
                                                        value="<?= e($task['fecha_inicio_plan'] ?? '') ?>">
                                                    <input type="hidden" name="fecha_fin_plan"
                                                        value="<?= e($task['fecha_fin_plan'] ?? '') ?>">
                                                    <input type="hidden" name="fecha_inicio_real"
                                                        value="<?= e($task['fecha_inicio_real'] ?? '') ?>">
                                                    <input type="hidden" name="duracion_estimada_horas"
                                                        value="<?= e(number_format((float) $task['duracion_estimada_horas'], 2, '.', '')) ?>">
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-4 text-muted">No hay tareas registradas.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0 planning-card">
                <div class="card-header bg-white fw-bold">Historial del plan</div>
                <div class="card-body p-4">
                    <?php if (!empty($history)): ?>
                        <div class="planning-history-list">
                            <?php foreach ($history as $item): ?>
                                <div class="planning-history-item">
                                    <div class="fw-semibold"><?= e($item['accion']) ?></div>
                                    <small class="text-muted"><?= e($item['created_at']) ?> - <?= e($item['nombre']) ?></small>
                                    <?php if (!empty($item['detalle'])): ?>
                                        <div class="small mt-1"><?= e($item['detalle']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">No hay historial de planificación.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>