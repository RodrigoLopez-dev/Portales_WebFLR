<?php
$dayWidth = 34;
$timelineDays = count($days);

function ganttTaskColor(string $estado): string
{
    switch ($estado) {
        case 'completada':
            return '#198754';

        case 'en_progreso':
            return '#0d6efd';

        case 'pendiente':
            return '#6c757d';

        default:
            return '#adb5bd';
    }
}

function ganttOffset(string $timelineStart, ?string $taskStart, int $dayWidth): int
{
    if (!$taskStart) {
        return 0;
    }

    $start = strtotime($timelineStart);
    $task = strtotime($taskStart);

    if ($task <= $start) {
        return 0;
    }

    $diffDays = (int) floor(($task - $start) / 86400);
    return $diffDays * $dayWidth;
}

function ganttWidth(?string $taskStart, ?string $taskEnd, int $dayWidth): int
{
    if (!$taskStart || !$taskEnd) {
        return $dayWidth;
    }

    $start = strtotime($taskStart);
    $end = strtotime($taskEnd);

    if ($end < $start) {
        return $dayWidth;
    }

    $diffDays = (int) floor(($end - $start) / 86400) + 1;
    return max($dayWidth, $diffDays * $dayWidth);
}
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Planificación Gantt</h1>
        <div class="d-flex gap-2">
            <a href="<?= e(base_url('/planning/show?id=' . ($plan['id'] ?? 0))) ?>"
                class="btn btn-outline-primary">Detalle</a>
            <a href="<?= e(base_url('/planning')) ?>" class="btn btn-secondary">Volver</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= e(base_url('/planning/gantt')) ?>" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Proyecto / plan</label>
                    <select name="id" class="form-select">
                        <?php foreach (($plans ?? []) as $item): ?>
                            <option value="<?= e($item['id']) ?>" <?= (string) ($plan['id'] ?? '') === (string) $item['id'] ? 'selected' : '' ?>>
                                <?= e($item['codigo']) ?> - <?= e($item['proyecto']) ?> / <?= e($item['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fase actual</label>
                    <input type="text" class="form-control" value="<?= e($plan['fase_nombre'] ?? '-') ?>" disabled>
                </div>

                <div class="d-flex gap-2">
                    <a href="<?= e(base_url('/planning/show?id=' . ($plan['id'] ?? 0))) ?>"
                        class="btn btn-outline-secondary">Detalle</a>
                    <a href="<?= e(base_url('/planning')) ?>" class="btn btn-outline-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div><strong>Proyecto:</strong> <?= e($plan['codigo']) ?> - <?= e($plan['proyecto']) ?></div>
            <div><strong>Plan:</strong> <?= e($plan['nombre']) ?></div>
            <div><strong>Periodo:</strong> <?= e($timelineStart) ?> a <?= e($timelineEnd) ?></div>
            <div><strong>Avance general:</strong> <?= e((string) ($plan['avance_general'] ?? 0)) ?>%</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Vista Gantt</strong></div>
        <div class="card-body">
            <?php if (!empty($tasks) || !empty($milestones)): ?>
                <div style="overflow-x: auto;">
                    <div style="min-width: <?= 320 + ($timelineDays * $dayWidth) ?>px;">
                        <div class="d-flex border-bottom fw-bold bg-light">
                            <div style="width: 320px; min-width: 320px;" class="p-2 border-end">Tarea / Hito</div>
                            <div class="d-flex" style="width: <?= $timelineDays * $dayWidth ?>px;">
                                <?php foreach ($days as $day): ?>
                                    <div class="text-center border-end small <?= $day['is_weekend'] ? 'bg-light' : '' ?>"
                                        style="width: <?= $dayWidth ?>px; min-width: <?= $dayWidth ?>px; padding: 6px 0;"
                                        title="<?= e($day['date']) ?>">
                                        <?= e($day['day']) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php if (!empty($milestones)): ?>
                            <?php foreach ($milestones as $milestone): ?>
                                <?php
                                $offset = ganttOffset($timelineStart, $milestone['fecha_hito'] ?? null, $dayWidth);
                                ?>
                                <div class="d-flex border-bottom">
                                    <div style="width: 320px; min-width: 320px;" class="p-2 border-end">
                                        <strong>📍 <?= e($milestone['nombre']) ?></strong><br>
                                        <small class="text-muted">
                                            Hito · Fecha: <?= e($milestone['fecha_hito'] ?? '-') ?>
                                        </small>
                                    </div>

                                    <div style="position: relative; width: <?= $timelineDays * $dayWidth ?>px; min-height: 52px;">
                                        <div
                                            style="position:absolute; left:<?= $offset ?>px; top:14px; font-size:22px; color:#dc3545;">
                                            ◆
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php foreach (($tasks ?? []) as $task): ?>
                            <?php
                            $offset = ganttOffset($timelineStart, $task['fecha_inicio_plan'] ?? null, $dayWidth);
                            $width = ganttWidth($task['fecha_inicio_plan'] ?? null, $task['fecha_fin_plan'] ?? null, $dayWidth);
                            $color = ganttTaskColor((string) ($task['estado'] ?? 'pendiente'));
                            ?>
                            <div class="d-flex border-bottom">
                                <div style="width: 320px; min-width: 320px;" class="p-2 border-end">
                                    <strong><?= e($task['nombre']) ?></strong><br>
                                    <small class="text-muted">
                                        <?= e($task['milestone_nombre'] ?? 'Sin hito') ?> ·
                                        <?= e($task['responsable_nombre'] ?? 'Sin responsable') ?>
                                    </small><br>
                                    <small>
                                        <?= e($task['fecha_inicio_plan'] ?? '-') ?> a <?= e($task['fecha_fin_plan'] ?? '-') ?>
                                        · <?= e((string) ($task['avance'] ?? 0)) ?>%
                                    </small>
                                </div>

                                <div style="position: relative; width: <?= $timelineDays * $dayWidth ?>px; min-height: 58px;"
                                    class="bg-white">
                                    <?php foreach ($days as $index => $day): ?>
                                        <div style="
                                                position:absolute;
                                                left:<?= $index * $dayWidth ?>px;
                                                top:0;
                                                width:<?= $dayWidth ?>px;
                                                height:100%;
                                                border-right:1px solid #f1f1f1;
                                                background:<?= $day['is_weekend'] ? '#f8f9fa' : 'transparent' ?>;
                                            "></div>
                                    <?php endforeach; ?>

                                    <div title="<?= e($task['nombre']) ?>" style="
                                            position:absolute;
                                            left:<?= $offset ?>px;
                                            top:14px;
                                            width:<?= $width ?>px;
                                            height:28px;
                                            background:<?= e($color) ?>;
                                            border-radius:6px;
                                            color:#fff;
                                            font-size:12px;
                                            line-height:28px;
                                            text-align:center;
                                            overflow:hidden;
                                            white-space:nowrap;
                                            text-overflow:ellipsis;
                                            padding:0 8px;
                                            box-shadow:0 1px 3px rgba(0,0,0,0.15);
                                        ">
                                        <?= e($task['nombre']) ?> (<?= e((string) ($task['avance'] ?? 0)) ?>%)
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mt-4">
                    <strong>Leyenda:</strong>
                    <div class="d-flex flex-wrap gap-3 mt-2">
                        <div><span
                                style="display:inline-block;width:18px;height:18px;background:#6c757d;border-radius:4px;vertical-align:middle;"></span>
                            Pendiente</div>
                        <div><span
                                style="display:inline-block;width:18px;height:18px;background:#0d6efd;border-radius:4px;vertical-align:middle;"></span>
                            En progreso</div>
                        <div><span
                                style="display:inline-block;width:18px;height:18px;background:#198754;border-radius:4px;vertical-align:middle;"></span>
                            Completada</div>
                        <div><span style="display:inline-block;font-size:18px;color:#dc3545;vertical-align:middle;">◆</span>
                            Hito</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-muted">No hay tareas ni hitos para mostrar en el Gantt.</div>
            <?php endif; ?>
        </div>
    </div>
</div>