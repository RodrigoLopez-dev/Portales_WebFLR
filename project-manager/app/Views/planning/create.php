<style>
    .planning-create-card {
        border: 0;
        border-radius: 16px;
    }
    .planning-section-title {
        font-weight: 800;
        font-size: .9rem;
        text-transform: uppercase;
        color: #6c757d;
        letter-spacing: .04em;
        margin-bottom: .75rem;
    }
    .planning-summary {
        position: sticky;
        top: 1rem;
    }
</style>

<div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Nueva planificación</h1>
            <div class="text-muted">Crea un plan asociado a una solicitud que aún no tenga planificación.</div>
        </div>
        <a href="<?= e(base_url('/planning')) ?>" class="btn btn-outline-secondary">Volver</a>
    </div>

    <?php if (empty($requests)): ?>
        <div class="alert alert-info">
            No existen solicitudes disponibles para planificar. Todas las solicitudes ya tienen una planificación asociada.
        </div>
    <?php else: ?>
        <form method="POST" action="<?= e(base_url('/planning/store')) ?>" id="planningCreateForm" class="row g-4 needs-validation" novalidate>
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <div class="col-lg-8">
                <div class="card planning-create-card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="planning-section-title">Proyecto</div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Proyecto / solicitud</label>
                                <select name="request_id" id="request_id" class="form-select" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach (($requests ?? []) as $item): ?>
                                        <option
                                            value="<?= e((string)$item['id']) ?>"
                                            data-codigo="<?= e($item['codigo'] ?? '') ?>"
                                            data-titulo="<?= e($item['titulo'] ?? '') ?>"
                                            data-descripcion="<?= e($item['descripcion'] ?? '') ?>"
                                            data-fase="<?= e($item['fase_nombre'] ?? '') ?>"
                                            data-responsable="<?= e($item['responsable_nombre'] ?? '') ?>"
                                            data-fecha-requerida="<?= e($item['fecha_requerida'] ?? '') ?>"
                                            data-horas="<?= e((string)($item['esfuerzo_estimado_horas'] ?? 0)) ?>"
                                            data-avance="<?= e((string)($item['porcentaje_avance'] ?? 0)) ?>"
                                        >
                                            <?= e($item['codigo']) ?> - <?= e($item['titulo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Debe seleccionar un proyecto.</div>
                                <div class="form-text">Solo se muestran solicitudes sin planificación existente.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nombre del plan</label>
                                <input type="text" name="nombre" id="nombre" class="form-control" required placeholder="Ej: Plan de implementación - Proyecto X">
                                <div class="invalid-feedback">Debe ingresar el nombre del plan.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Estado inicial</label>
                                <select name="estado" id="estado" class="form-select">
                                    <option value="planificado">Planificado</option>
                                    <option value="en_ejecucion">En ejecución</option>
                                    <option value="pausado">Pausado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card planning-create-card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="planning-section-title">Fechas</div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Inicio plan</label>
                                <input type="date" name="fecha_inicio_plan" id="fecha_inicio_plan" class="form-control" required>
                                <div class="invalid-feedback">Debe ingresar la fecha de inicio.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Fin plan</label>
                                <input type="date" name="fecha_fin_plan" id="fecha_fin_plan" class="form-control" required>
                                <div class="invalid-feedback">Debe ingresar una fecha de fin válida.</div>
                                <div class="form-text" id="fechaHelp">La fecha fin no puede ser menor a la fecha de inicio.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card planning-create-card shadow-sm">
                    <div class="card-body">
                        <div class="planning-section-title">Descripción</div>

                        <label class="form-label">Descripción del plan</label>
                        <textarea name="descripcion" id="descripcion" class="form-control" rows="5" placeholder="Describe el alcance del plan, hitos principales, dependencias y consideraciones relevantes."></textarea>
                        <div class="form-text">Sugerencia: indica objetivo, entregables principales, riesgos y supuestos.</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card planning-create-card shadow-sm planning-summary">
                    <div class="card-header bg-white fw-bold">Resumen</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="small text-muted">Proyecto</div>
                            <div id="summaryProject" class="fw-bold">Sin seleccionar</div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted">Fase actual</div>
                            <div id="summaryPhase">-</div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted">Responsable solicitud</div>
                            <div id="summaryResponsible">-</div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted">Fecha requerida solicitud</div>
                            <div id="summaryRequiredDate">-</div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted">Horas estimadas solicitud</div>
                            <div id="summaryHours">-</div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted">Avance solicitud</div>
                            <div class="progress" style="height: 18px;">
                                <div class="progress-bar" id="summaryProgress" style="width: 0%;">0%</div>
                            </div>
                        </div>

                        <div class="alert alert-light small mb-3">
                            Después de crear la planificación podrás agregar tareas, hitos y revisar el Gantt.
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-primary">Guardar planificación</button>
                            <a href="<?= e(base_url('/planning')) ?>" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
(function () {
    const form = document.getElementById('planningCreateForm');
    const requestSelect = document.getElementById('request_id');
    const nombre = document.getElementById('nombre');
    const descripcion = document.getElementById('descripcion');
    const inicio = document.getElementById('fecha_inicio_plan');
    const fin = document.getElementById('fecha_fin_plan');

    const summaryProject = document.getElementById('summaryProject');
    const summaryPhase = document.getElementById('summaryPhase');
    const summaryResponsible = document.getElementById('summaryResponsible');
    const summaryRequiredDate = document.getElementById('summaryRequiredDate');
    const summaryHours = document.getElementById('summaryHours');
    const summaryProgress = document.getElementById('summaryProgress');

    if (!form || !requestSelect) return;

    function selectedOption() {
        return requestSelect.options[requestSelect.selectedIndex];
    }

    function updateSummary() {
        const opt = selectedOption();
        if (!opt || !opt.value) {
            summaryProject.textContent = 'Sin seleccionar';
            summaryPhase.textContent = '-';
            summaryResponsible.textContent = '-';
            summaryRequiredDate.textContent = '-';
            summaryHours.textContent = '-';
            summaryProgress.style.width = '0%';
            summaryProgress.textContent = '0%';
            return;
        }

        const codigo = opt.dataset.codigo || '';
        const titulo = opt.dataset.titulo || '';
        const fase = opt.dataset.fase || '-';
        const responsable = opt.dataset.responsable || '-';
        const requerida = opt.dataset.fechaRequerida || '-';
        const horas = opt.dataset.horas || '0';
        const avance = parseInt(opt.dataset.avance || '0', 10);
        const desc = opt.dataset.descripcion || '';

        summaryProject.textContent = codigo + ' - ' + titulo;
        summaryPhase.textContent = fase;
        summaryResponsible.textContent = responsable;
        summaryRequiredDate.textContent = requerida;
        summaryHours.textContent = horas + ' horas';
        summaryProgress.style.width = avance + '%';
        summaryProgress.textContent = avance + '%';

        if (!nombre.value) {
            nombre.value = 'Plan ' + titulo;
        }

        if (!descripcion.value && desc) {
            descripcion.value = 'Objetivo del plan:\n' + desc + '\n\nHitos principales:\n- \n\nRiesgos / dependencias:\n- ';
        }

        if (!fin.value && requerida && requerida !== '-') {
            fin.value = requerida;
        }
    }

    function validateDates() {
        if (inicio.value && fin.value && fin.value < inicio.value) {
            fin.setCustomValidity('La fecha fin no puede ser menor que la fecha inicio.');
        } else {
            fin.setCustomValidity('');
        }
    }

    requestSelect.addEventListener('change', updateSummary);
    inicio.addEventListener('change', validateDates);
    fin.addEventListener('change', validateDates);

    form.addEventListener('submit', function (event) {
        validateDates();
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
})();
</script>
