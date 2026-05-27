<?php
$totals = $totals ?? [];
$byStatus = $byStatus ?? [];
$byPhase = $byPhase ?? [];
$byType = $byType ?? [];
$byResponsible = $byResponsible ?? [];
$topLate = $topLate ?? [];
$topBlocked = $topBlocked ?? [];
$topPendingDocuments = $topPendingDocuments ?? [];

$activas = (int)($totals['activas'] ?? 0);
$atrasadas = (int)($totals['atrasadas'] ?? 0);
$bloqueadas = (int)($totals['bloqueadas'] ?? 0);
$pendientesDoc = (int)($totals['pendientes_doc'] ?? 0);
$rechazadosDoc = (int)($totals['rechazados_doc'] ?? 0);
$formalizacionPendiente = (int)($totals['formalizacion_pendiente'] ?? 0);
$avancePromedio = (float)($totals['avance_promedio'] ?? 0);
$cerradasMes = (int)($totals['cerradas_mes'] ?? 0);
$enRiesgo = $atrasadas + $bloqueadas;
$accionesTotales = $atrasadas + $bloqueadas + $pendientesDoc + $rechazadosDoc;

$totalFases = 0;
foreach ($byPhase as $row) {
    $totalFases += (int)($row['total'] ?? 0);
}

$maxTipo = 1;
foreach ($byType as $row) {
    $maxTipo = max($maxTipo, (int)($row['total'] ?? 0));
}

$phaseColors = [
    'Solicitud' => '#64748b',
    'Levantamiento' => '#06b6d4',
    'Formalización' => '#6366f1',
    'Diseño' => '#f59e0b',
    'Desarrollo' => '#2563eb',
    'QA' => '#111827',
    'Paso a Producción' => '#16a34a',
    'Cierre' => '#059669',
];

function dashboardCard(string $title, string $value, string $subtitle, string $icon, string $accent, ?string $url = null): void
{
    $html = '
        <div class="pm-kpi-card" style="--accent:' . e($accent) . '">
            <div class="pm-kpi-content">
                <div class="pm-kpi-title">' . e($title) . '</div>
                <div class="pm-kpi-value">' . e($value) . '</div>
                <div class="pm-kpi-subtitle">' . e($subtitle) . '</div>
            </div>
            <div class="pm-kpi-icon">' . e($icon) . '</div>
        </div>';

    if ($url) {
        echo '<a href="' . e($url) . '" class="text-decoration-none text-reset">' . $html . '</a>';
        return;
    }

    echo $html;
}

function dashboardAction(string $title, string $subtitle, int $count, string $accent, ?string $url = null): void
{
    $html = '
        <div class="pm-action-item" style="--accent:' . e($accent) . '">
            <div>
                <div class="pm-action-title">' . e($title) . '</div>
                <div class="pm-action-subtitle">' . e($subtitle) . '</div>
            </div>
            <span class="pm-action-badge">' . e((string)$count) . '</span>
        </div>';

    if ($url) {
        echo '<a href="' . e($url) . '" class="text-decoration-none text-reset">' . $html . '</a>';
        return;
    }

    echo $html;
}
?>

<style>
    :root {
        --pm-bg: #f4f7fb;
        --pm-card: #ffffff;
        --pm-text: #172033;
        --pm-muted: #64748b;
        --pm-line: #e6ecf4;
        --pm-primary: #0d6efd;
        --pm-danger: #dc3545;
        --pm-warning: #f59e0b;
        --pm-success: #16a34a;
        --pm-info: #06b6d4;
        --pm-shadow: 0 10px 28px rgba(15, 23, 42, .08);
        --pm-soft-shadow: 0 4px 14px rgba(15, 23, 42, .06);
    }

    body { background: var(--pm-bg); }

    .pm-dashboard {
        padding: 1.25rem 1.35rem 2rem;
    }

    .pm-hero {
        border-radius: 22px;
        padding: 1.4rem 1.5rem;
        margin-bottom: 1.2rem;
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.35), transparent 28%),
            linear-gradient(135deg, #c90058 0%, #8b0048 48%, #172033 100%);
        color: #fff;
        box-shadow: var(--pm-shadow);
        overflow: hidden;
        position: relative;
    }

    .pm-hero::after {
        content: '';
        position: absolute;
        right: -60px;
        top: -60px;
        width: 190px;
        height: 190px;
        border-radius: 50%;
        background: rgba(255,255,255,.12);
    }

    .pm-hero-title {
        font-size: 1.65rem;
        font-weight: 800;
        margin: 0;
        letter-spacing: -.02em;
    }

    .pm-hero-subtitle {
        color: rgba(255,255,255,.82);
        margin-top: .25rem;
        max-width: 720px;
    }

    .pm-hero-actions { position: relative; z-index: 2; }

    .pm-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .45rem .8rem;
        font-size: .78rem;
        font-weight: 700;
        background: rgba(255,255,255,.14);
        border: 1px solid rgba(255,255,255,.22);
        color: #fff;
    }

    .pm-grid-kpi {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .pm-kpi-card {
        position: relative;
        min-height: 116px;
        border-radius: 20px;
        background: var(--pm-card);
        box-shadow: var(--pm-soft-shadow);
        border: 1px solid rgba(226, 232, 240, .9);
        padding: 1rem;
        overflow: hidden;
        transition: transform .14s ease, box-shadow .14s ease;
    }

    .pm-kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--pm-shadow);
    }

    .pm-kpi-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        background: var(--accent);
    }

    .pm-kpi-card::after {
        content: '';
        position: absolute;
        right: -35px;
        bottom: -35px;
        width: 105px;
        height: 105px;
        border-radius: 50%;
        background: color-mix(in srgb, var(--accent) 13%, transparent);
    }

    .pm-kpi-title {
        color: var(--pm-muted);
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 800;
    }

    .pm-kpi-value {
        font-size: 1.85rem;
        font-weight: 850;
        color: var(--accent);
        line-height: 1.1;
        margin-top: .2rem;
    }

    .pm-kpi-subtitle {
        color: var(--pm-muted);
        font-size: .82rem;
        margin-top: .3rem;
    }

    .pm-kpi-icon {
        position: absolute;
        right: 1rem;
        top: 1rem;
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background: color-mix(in srgb, var(--accent) 12%, white);
        font-size: 1.25rem;
        z-index: 1;
    }

    .pm-card {
        border: 1px solid rgba(226, 232, 240, .95);
        border-radius: 20px;
        background: #fff;
        box-shadow: var(--pm-soft-shadow);
        overflow: hidden;
        height: 100%;
    }

    .pm-card-header {
        padding: 1rem 1.1rem .85rem;
        border-bottom: 1px solid var(--pm-line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
    }

    .pm-card-title {
        margin: 0;
        font-size: .95rem;
        font-weight: 850;
        color: var(--pm-text);
    }

    .pm-card-subtitle {
        margin-top: .12rem;
        color: var(--pm-muted);
        font-size: .78rem;
    }

    .pm-card-body { padding: 1rem 1.1rem; }

    .pm-action-list {
        display: grid;
        gap: .75rem;
    }

    .pm-action-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        border: 1px solid var(--pm-line);
        border-left: 5px solid var(--accent);
        border-radius: 16px;
        background: #fff;
        padding: .85rem .9rem;
        transition: background .12s ease, transform .12s ease;
    }

    .pm-action-item:hover {
        background: #f8fafc;
        transform: translateX(2px);
    }

    .pm-action-title {
        font-weight: 800;
        color: var(--pm-text);
        font-size: .88rem;
    }

    .pm-action-subtitle {
        color: var(--pm-muted);
        font-size: .78rem;
        margin-top: .1rem;
    }

    .pm-action-badge {
        min-width: 30px;
        height: 26px;
        padding: 0 .55rem;
        display: inline-grid;
        place-items: center;
        border-radius: 999px;
        background: var(--accent);
        color: #fff;
        font-weight: 850;
        font-size: .8rem;
    }

    .pm-risk-item,
    .pm-doc-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
        padding: .78rem 0;
        border-bottom: 1px solid var(--pm-line);
    }

    .pm-risk-item:last-child,
    .pm-doc-item:last-child { border-bottom: 0; }

    .pm-code {
        font-weight: 850;
        color: var(--pm-primary);
        text-decoration: none;
        font-size: .84rem;
    }

    .pm-row-title {
        color: var(--pm-text);
        font-size: .85rem;
        font-weight: 700;
        line-height: 1.25;
    }

    .pm-row-meta {
        color: var(--pm-muted);
        font-size: .75rem;
        margin-top: .12rem;
    }

    .pm-badge {
        border-radius: 999px;
        padding: .28rem .55rem;
        font-size: .68rem;
        font-weight: 850;
        white-space: nowrap;
    }

    .pm-badge-danger { background: #fee2e2; color: #b91c1c; }
    .pm-badge-warning { background: #fef3c7; color: #92400e; }
    .pm-badge-info { background: #cffafe; color: #0e7490; }
    .pm-badge-success { background: #dcfce7; color: #166534; }
    .pm-badge-dark { background: #e5e7eb; color: #111827; }

    .pm-progress-row { margin-bottom: .9rem; }
    .pm-progress-row:last-child { margin-bottom: 0; }

    .pm-progress-label {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        font-size: .82rem;
        margin-bottom: .35rem;
    }

    .pm-progress-track {
        height: 11px;
        border-radius: 999px;
        background: #eef2f7;
        overflow: hidden;
    }

    .pm-progress-fill {
        height: 100%;
        width: var(--value);
        max-width: 100%;
        min-width: 0;
        border-radius: 999px;
        background: var(--bar-color, #0d6efd);
    }

    .pm-mini-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .8rem;
    }

    .pm-mini-card {
        border: 1px solid var(--pm-line);
        border-radius: 16px;
        padding: .85rem;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    }

    .pm-mini-label {
        color: var(--pm-muted);
        font-size: .75rem;
        font-weight: 750;
    }

    .pm-mini-value {
        margin-top: .15rem;
        font-size: 1.35rem;
        font-weight: 850;
    }

    .pm-table {
        margin: 0;
        --bs-table-bg: transparent;
    }

    .pm-table th {
        color: var(--pm-muted);
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        border-bottom: 1px solid var(--pm-line);
    }

    .pm-table td {
        font-size: .82rem;
        vertical-align: middle;
        border-color: var(--pm-line);
    }

    .pm-empty {
        border: 1px dashed #cbd5e1;
        color: var(--pm-muted);
        border-radius: 16px;
        padding: 1.2rem;
        text-align: center;
        background: #f8fafc;
    }

    .pm-insight {
        border-radius: 18px;
        padding: 1rem;
        background: linear-gradient(135deg, #fff7ed 0%, #ffffff 80%);
        border: 1px solid #fed7aa;
        color: #7c2d12;
    }

    @media (max-width: 1199px) {
        .pm-grid-kpi { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 767px) {
        .pm-dashboard { padding: .85rem; }
        .pm-grid-kpi { grid-template-columns: 1fr; }
        .pm-hero { padding: 1.1rem; }
        .pm-hero-title { font-size: 1.35rem; }
        .pm-mini-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="pm-dashboard">
    <section class="pm-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="pm-hero-title">Dashboard</h1>
            <div class="pm-hero-subtitle">
                Vista ejecutiva para detectar riesgos, priorizar acciones y monitorear el avance del portafolio.
            </div>
        </div>
        <div class="pm-hero-actions d-flex flex-wrap gap-2">
            <span class="pm-pill">⚡ <?= e((string)$accionesTotales) ?> acciones</span>
            <a href="<?= e(base_url('/requests')) ?>" class="btn btn-light btn-sm fw-bold">Ver solicitudes</a>
            <a href="<?= e(base_url('/requests/kanban')) ?>" class="btn btn-outline-light btn-sm fw-bold">Ver Kanban</a>
        </div>
    </section>

    <section class="pm-grid-kpi">
        <?php dashboardCard('Solicitudes activas', (string)$activas, 'Solicitudes no finalizadas', '📌', '#0d6efd', base_url('/requests')); ?>
        <?php dashboardCard('En riesgo', (string)$enRiesgo, 'Atrasadas + bloqueadas', '🚨', '#dc3545', base_url('/requests?atrasadas=1')); ?>
        <?php dashboardCard('Pendientes documentales', (string)$pendientesDoc, 'Documentos por revisar/cargar', '📎', '#f59e0b', null); ?>
        <?php dashboardCard('Avance promedio', number_format($avancePromedio, 1) . '%', 'Promedio general del portafolio', '📈', '#16a34a', base_url('/requests')); ?>
    </section>

    <div class="row g-3 mb-3">
        <div class="col-xl-4">
            <div class="pm-card">
                <div class="pm-card-header">
                    <div>
                        <h2 class="pm-card-title">Acciones requeridas</h2>
                        <div class="pm-card-subtitle">Elementos que requieren atención inmediata</div>
                    </div>
                </div>
                <div class="pm-card-body">
                    <div class="pm-action-list">
                        <?php dashboardAction('Revisar solicitudes atrasadas', 'Fecha requerida vencida', $atrasadas, '#dc3545', base_url('/requests?atrasadas=1')); ?>
                        <?php dashboardAction('Resolver bloqueos', 'Solicitudes con impedimentos', $bloqueadas, '#f59e0b', base_url('/requests?bloqueadas=1')); ?>
                        <?php dashboardAction('Regularizar documentos', 'Documentos pendientes', $pendientesDoc, '#06b6d4', null); ?>
                        <?php dashboardAction('Reemplazar documentos rechazados', 'Requieren nueva carga', $rechazadosDoc, '#e11d48', null); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="pm-card">
                <div class="pm-card-header">
                    <div>
                        <h2 class="pm-card-title">Proyectos en riesgo</h2>
                        <div class="pm-card-subtitle">Top de solicitudes atrasadas o bloqueadas</div>
                    </div>
                    <span class="pm-badge pm-badge-danger"><?= e((string)$enRiesgo) ?> riesgo</span>
                </div>
                <div class="pm-card-body">
                    <?php if (!empty($topLate) || !empty($topBlocked)): ?>
                        <?php foreach (array_slice(array_merge($topLate, $topBlocked), 0, 5) as $row): ?>
                            <div class="pm-risk-item">
                                <div class="min-w-0">
                                    <a class="pm-code" href="<?= e(base_url('/requests/advance?id=' . ($row['id'] ?? ''))) ?>">
                                        <?= e($row['codigo'] ?? '') ?>
                                    </a>
                                    <div class="pm-row-title"><?= e(mb_strimwidth((string)($row['titulo'] ?? ''), 0, 65, '...')) ?></div>
                                    <div class="pm-row-meta">
                                        <?= e($row['fase'] ?? 'Sin fase') ?>
                                        <?php if (isset($row['dias_atraso'])): ?> · <?= e((string)$row['dias_atraso']) ?> días atraso<?php endif; ?>
                                    </div>
                                </div>
                                <span class="pm-badge pm-badge-danger">Riesgo</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="pm-empty">No hay proyectos en riesgo.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="pm-card">
                <div class="pm-card-header">
                    <div>
                        <h2 class="pm-card-title">Estado documental</h2>
                        <div class="pm-card-subtitle">Resumen de gestión documental</div>
                    </div>
                </div>
                <div class="pm-card-body">
                    <div class="pm-mini-grid">
                        <div class="pm-mini-card">
                            <div class="pm-mini-label">Pendientes</div>
                            <div class="pm-mini-value text-warning"><?= e((string)$pendientesDoc) ?></div>
                        </div>
                        <div class="pm-mini-card">
                            <div class="pm-mini-label">Rechazados</div>
                            <div class="pm-mini-value text-danger"><?= e((string)$rechazadosDoc) ?></div>
                        </div>
                        <div class="pm-mini-card">
                            <div class="pm-mini-label">Formalización</div>
                            <div class="pm-mini-value text-secondary"><?= e((string)$formalizacionPendiente) ?></div>
                        </div>
                        <div class="pm-mini-card">
                            <div class="pm-mini-label">Cerradas mes</div>
                            <div class="pm-mini-value text-success"><?= e((string)$cerradasMes) ?></div>
                        </div>
                    </div>
                    <?php if ($rechazadosDoc > 0): ?>
                        <div class="pm-insight mt-3">
                            <strong>Atención:</strong> existen documentos rechazados que pueden bloquear avance de fase.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="pm-card">
                <div class="pm-card-header">
                    <div>
                        <h2 class="pm-card-title">Flujo por fase</h2>
                        <div class="pm-card-subtitle">Distribución de solicitudes y avance promedio por etapa</div>
                    </div>
                </div>
                <div class="pm-card-body">
                    <?php if (!empty($byPhase)): ?>
                        <?php foreach ($byPhase as $row): ?>
                            <?php
                            $fase = (string)($row['fase'] ?? 'Sin fase');
                            $total = (int)($row['total'] ?? 0);
                            $pct = $totalFases > 0 ? round(($total / $totalFases) * 100, 1) : 0;
                            $avance = (float)($row['avance_promedio'] ?? 0);
                            $barColor = $phaseColors[$fase] ?? '#0d6efd';
                            ?>
                            <div class="pm-progress-row">
                                <div class="pm-progress-label">
                                    <strong><?= e($fase) ?></strong>
                                    <span class="text-muted"><?= e((string)$total) ?> solicitud(es) · avance <?= e(number_format($avance, 1)) ?>%</span>
                                </div>
                                <div class="pm-progress-track">
                                    <div class="pm-progress-fill" style="--value: <?= e((string)$pct) ?>%; --bar-color: <?= e($barColor) ?>;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="pm-empty">Sin datos por fase.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="pm-card">
                <div class="pm-card-header">
                    <div>
                        <h2 class="pm-card-title">Solicitudes por tipo</h2>
                        <div class="pm-card-subtitle">Tipos más demandados</div>
                    </div>
                </div>
                <div class="pm-card-body">
                    <?php if (!empty($byType)): ?>
                        <?php foreach ($byType as $row): ?>
                            <?php
                            $total = (int)($row['total'] ?? 0);
                            $pct = $maxTipo > 0 ? round(($total / $maxTipo) * 100, 1) : 0;
                            ?>
                            <div class="pm-progress-row">
                                <div class="pm-progress-label">
                                    <strong><?= e(mb_strimwidth((string)($row['tipo'] ?? 'Sin tipo'), 0, 42, '...')) ?></strong>
                                    <span class="text-muted"><?= e((string)$total) ?></span>
                                </div>
                                <div class="pm-progress-track">
                                    <div class="pm-progress-fill" style="--value: <?= e((string)$pct) ?>%; --bar-color:#0d6efd;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="pm-empty">Sin datos por tipo.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="pm-card">
                <div class="pm-card-header">
                    <div>
                        <h2 class="pm-card-title">Carga por responsable</h2>
                        <div class="pm-card-subtitle">Volumen, esfuerzo, desviación y avance por persona</div>
                    </div>
                </div>
                <div class="pm-card-body">
                    <?php if (!empty($byResponsible)): ?>
                        <div class="table-responsive">
                            <table class="table pm-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Responsable</th>
                                        <th class="text-end">Solicitudes</th>
                                        <th class="text-end">Horas est.</th>
                                        <th class="text-end">Horas reales</th>
                                        <th class="text-end">Desv.</th>
                                        <th class="text-end">Alertas</th>
                                        <th class="text-end">Avance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($byResponsible as $row): ?>
                                        <?php
                                        $est = (float)($row['horas_estimadas'] ?? 0);
                                        $real = (float)($row['horas_reales'] ?? 0);
                                        $desv = $est > 0 ? (($real - $est) / $est) * 100 : 0;
                                        $alertas = (int)($row['bloqueadas'] ?? 0) + (int)($row['atrasadas'] ?? 0);
                                        ?>
                                        <tr>
                                            <td class="fw-bold"><?= e($row['responsable'] ?? 'Sin responsable') ?></td>
                                            <td class="text-end"><?= e((string)($row['total'] ?? 0)) ?></td>
                                            <td class="text-end"><?= e(number_format($est, 1)) ?></td>
                                            <td class="text-end"><?= e(number_format($real, 1)) ?></td>
                                            <td class="text-end <?= $desv > 0 ? 'text-danger' : 'text-success' ?> fw-bold"><?= e(number_format($desv, 1)) ?>%</td>
                                            <td class="text-end"><span class="pm-badge <?= $alertas > 0 ? 'pm-badge-danger' : 'pm-badge-success' ?>"><?= e((string)$alertas) ?></span></td>
                                            <td class="text-end fw-bold"><?= e(number_format((float)($row['avance_promedio'] ?? 0), 1)) ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="pm-empty">Sin carga asignada.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="pm-card">
                <div class="pm-card-header">
                    <div>
                        <h2 class="pm-card-title">Pendientes documentales</h2>
                        <div class="pm-card-subtitle">Solicitudes con mayor carga documental</div>
                    </div>
                </div>
                <div class="pm-card-body">
                    <?php if (!empty($topPendingDocuments)): ?>
                        <?php foreach ($topPendingDocuments as $row): ?>
                            <div class="pm-doc-item">
                                <div>
                                    <a class="pm-code" href="<?= e(base_url('/requests/advance?id=' . ($row['id'] ?? ''))) ?>"><?= e($row['codigo'] ?? '') ?></a>
                                    <div class="pm-row-title"><?= e(mb_strimwidth((string)($row['titulo'] ?? ''), 0, 48, '...')) ?></div>
                                    <div class="pm-row-meta"><?= e($row['fase'] ?? 'Sin fase') ?></div>
                                </div>
                                <span class="pm-badge pm-badge-info"><?= e((string)($row['pendientes'] ?? 0)) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="pm-empty">No hay pendientes documentales.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
