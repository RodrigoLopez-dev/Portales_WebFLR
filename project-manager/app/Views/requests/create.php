<?php
/** @var array $catalogs */
/** @var array|null $user */
$categorias = $catalogs['categories'] ?? [];
$tipos = $catalogs['types'] ?? [];
$prioridades = $catalogs['priorities'] ?? [];
$areas = $catalogs['areas'] ?? [];
$usuarios = $catalogs['users'] ?? [];
$currentUser = $user ?? ($_SESSION['user'] ?? null);
$currentUserId = (int) ($currentUser['id'] ?? 0);
?>

<style>
    .request-create-wrapper {
        max-width: 1180px;
        margin: 0 auto;
    }

    .request-section-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 .125rem .45rem rgba(0, 0, 0, .06);
        overflow: hidden;
    }

    .request-section-card .card-header {
        background: #fff;
        font-weight: 700;
    }

    .form-help {
        font-size: .78rem;
        color: #6c757d;
        margin-top: .25rem;
    }

    .priority-preview {
        border-radius: 12px;
        padding: .75rem;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
    }

    .priority-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: .35rem;
    }

    .priority-alta {
        background: #dc3545;
    }

    .priority-media {
        background: #ffc107;
    }

    .priority-baja {
        background: #198754;
    }

    .required-label::after {
        content: " *";
        color: #dc3545;
        font-weight: 700;
    }

    .sticky-actions {
        position: sticky;
        bottom: 0;
        z-index: 2;
        background: rgba(248, 249, 250, .96);
        backdrop-filter: blur(4px);
        border-top: 1px solid #dee2e6;
        padding: 1rem 0;
    }
</style>

<div class="request-create-wrapper mt-4">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="mb-1">Nueva Solicitud</h1>
            <div class="text-muted">
                Registra la información base, clasificación y estimación inicial de la solicitud.
            </div>
        </div>

        <a href="<?= e(base_url('/requests')) ?>" class="btn btn-outline-secondary">
            Volver
        </a>
    </div>

    <form method="POST" action="<?= e(base_url('/requests/store')) ?>" id="requestCreateForm" class="needs-validation"
        novalidate>
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card request-section-card mb-4">
                    <div class="card-header">1. Información básica</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label required-label">Título</label>
                                <input type="text" name="titulo" id="titulo" class="form-control" maxlength="180"
                                    required placeholder="Ej: Automatización de cartolas BCI">
                                <div class="invalid-feedback">Ingrese un título para la solicitud.</div>
                                <div class="form-help">Usa un título breve y específico.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label required-label">Categoría</label>
                                <select name="category_id" id="category_id" class="form-select" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($categorias as $c): ?>
                                        <option value="<?= e($c['id']) ?>">
                                            <?= e($c['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Seleccione la categoría.</div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label required-label">Tipo</label>
                                <select name="tipo_id" id="tipo_id" class="form-select" required>
                                    <option value="">Seleccione una categoría</option>
                                </select>
                                <div class="invalid-feedback">Seleccione el tipo de solicitud.</div>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label required-label">Área</label>
                                <select name="area_id" id="area_id" class="form-select" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($areas as $a): ?>
                                        <option value="<?= e($a['id']) ?>"><?= e($a['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Seleccione el área solicitante.</div>
                            </div>

                            <div class="col-md-7">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" id="descripcion" class="form-control" rows="6"
                                    placeholder="Describe:\n- Qué se necesita\n- Qué problema resuelve\n- Alcance inicial\n- Consideraciones o restricciones"></textarea>
                                <div class="form-help">Mientras más claro sea el alcance, más fácil será estimar y
                                    avanzar.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card request-section-card mb-4">
                    <div class="card-header">2. Clasificación y prioridad</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Impacto</label>
                                <select name="impacto" id="impacto" class="form-select scoring-field">
                                    <option value="Alto">Alto</option>
                                    <option value="Medio" selected>Medio</option>
                                    <option value="Bajo">Bajo</option>
                                </select>
                                <div class="form-help">Afectación al negocio.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Urgencia</label>
                                <select name="urgencia" id="urgencia" class="form-select scoring-field">
                                    <option value="Alta">Alta</option>
                                    <option value="Media" selected>Media</option>
                                    <option value="Baja">Baja</option>
                                </select>
                                <div class="form-help">Qué tan pronto se necesita.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Complejidad</label>
                                <select name="complejidad" id="complejidad" class="form-select scoring-field">
                                    <option value="Alta">Alta</option>
                                    <option value="Media" selected>Media</option>
                                    <option value="Baja">Baja</option>
                                </select>
                                <div class="form-help">Dificultad técnica.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Riesgo</label>
                                <select name="riesgo" id="riesgo" class="form-select scoring-field">
                                    <option value="Alto">Alto</option>
                                    <option value="Medio" selected>Medio</option>
                                    <option value="Bajo">Bajo</option>
                                </select>
                                <div class="form-help">Probabilidad de fallas o dependencias.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required-label">Prioridad</label>
                                <select name="prioridad_id" id="prioridad_id" class="form-select" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($prioridades as $p): ?>
                                        <option value="<?= e($p['id']) ?>"
                                            data-name="<?= e(mb_strtolower($p['nombre'])) ?>">
                                            <?= e($p['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Seleccione la prioridad.</div>
                                <div class="form-help">Se sugiere automáticamente según impacto, urgencia, complejidad y
                                    riesgo. Puedes modificarla.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Resumen de prioridad sugerida</label>
                                <div class="priority-preview" id="priorityPreview">
                                    <div class="fw-bold">
                                        <span class="priority-dot priority-media" id="priorityDot"></span>
                                        <span id="priorityText">Prioridad sugerida: Media</span>
                                    </div>
                                    <div class="small text-muted mt-1" id="priorityReason">
                                        Clasificación equilibrada para una solicitud estándar.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card request-section-card mb-4">
                    <div class="card-header">3. Asignación y planificación</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Responsable</label>
                            <select name="responsable_id" id="responsable_id" class="form-select">
                                <option value="">Sin asignar</option>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?= e($u['id']) ?>" <?= ((int) $u['id'] === $currentUserId) ? 'selected' : '' ?>>
                                        <?= e($u['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-help">Por defecto se asigna al usuario actual si está disponible en el
                                catálogo.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fecha requerida</label>
                            <input type="date" name="fecha_requerida" id="fecha_requerida" class="form-control">
                            <div class="form-help">Fecha objetivo esperada por el solicitante.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Horas estimadas</label>
                            <input type="number" step="0.01" min="0" name="esfuerzo_estimado_horas"
                                id="esfuerzo_estimado_horas" class="form-control" placeholder="Ej: 24">
                            <div class="form-help" id="hoursSuggestion">Sugerido por complejidad media: 24 horas.</div>
                        </div>

                        <!--  <div class="form-check mb-2">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="requiere_formalizacion"
                                value="1"
                                id="requiere_formalizacion"
                            >
                            <label class="form-check-label" for="requiere_formalizacion">
                                Requiere formalización
                            </label>
                        </div>
                        <div class="form-help mb-3">
                            Marcar si requiere NDA, contrato, SLA u otros documentos formales.
                        </div> -->

                        <div id="formalizacion_block">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="requiere_formalizacion"
                                    id="requiere_formalizacion" value="1">
                                <label class="form-check-label" for="requiere_formalizacion">
                                    Requiere formalización
                                </label>
                            </div>

                            <div class="form-text" id="formalizacion_text">
                                Marcar si requiere NDA, contrato, SLA u otros documentos formales.
                            </div>
                        </div>


                    </div>
                </div>

                <div class="card request-section-card mb-4">
                    <div class="card-header">Resumen</div>
                    <div class="card-body small">
                        <div class="mb-2"><strong>Título:</strong> <span id="summaryTitle" class="text-muted">Sin
                                título</span></div>
                        <div class="mb-2"><strong>Tipo:</strong> <span id="summaryType" class="text-muted">No
                                seleccionado</span></div>
                        <div class="mb-2"><strong>Área:</strong> <span id="summaryArea" class="text-muted">No
                                seleccionada</span></div>
                        <div class="mb-2"><strong>Responsable:</strong> <span id="summaryResponsible"
                                class="text-muted">Sin asignar</span></div>
                        <div class="mb-2"><strong>Prioridad:</strong> <span id="summaryPriority"
                                class="badge bg-warning text-dark">Media</span></div>
                        <div><strong>Horas:</strong> <span id="summaryHours" class="text-muted">No estimadas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sticky-actions">
            <div class="request-create-wrapper d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                    data-bs-target="#previewRequestModal">
                    Previsualizar
                </button>

                <a href="<?= e(base_url('/requests')) ?>" class="btn btn-outline-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Guardar solicitud
                </button>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="previewRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Previsualización de solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Título</dt>
                    <dd class="col-sm-9" id="previewTitle">-</dd>

                    <dt class="col-sm-3">Tipo</dt>
                    <dd class="col-sm-9" id="previewType">-</dd>

                    <dt class="col-sm-3">Área</dt>
                    <dd class="col-sm-9" id="previewArea">-</dd>

                    <dt class="col-sm-3">Responsable</dt>
                    <dd class="col-sm-9" id="previewResponsible">-</dd>

                    <dt class="col-sm-3">Prioridad</dt>
                    <dd class="col-sm-9" id="previewPriority">-</dd>

                    <dt class="col-sm-3">Fecha requerida</dt>
                    <dd class="col-sm-9" id="previewDate">-</dd>

                    <dt class="col-sm-3">Horas estimadas</dt>
                    <dd class="col-sm-9" id="previewHours">-</dd>

                    <dt class="col-sm-3">Clasificación</dt>
                    <dd class="col-sm-9" id="previewScore">-</dd>

                    <dt class="col-sm-3">Descripción</dt>
                    <dd class="col-sm-9" id="previewDescription">-</dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Seguir editando</button>
                <button type="submit" form="requestCreateForm" class="btn btn-primary">Guardar solicitud</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const form = document.getElementById('requestCreateForm');
        const fields = {
            titulo: document.getElementById('titulo'),
            categoria: document.getElementById('category_id'),
            tipo: document.getElementById('tipo_id'),
            area: document.getElementById('area_id'),
            responsable: document.getElementById('responsable_id'),
            prioridad: document.getElementById('prioridad_id'),
            impacto: document.getElementById('impacto'),
            urgencia: document.getElementById('urgencia'),
            complejidad: document.getElementById('complejidad'),
            riesgo: document.getElementById('riesgo'),
            horas: document.getElementById('esfuerzo_estimado_horas'),
            fecha: document.getElementById('fecha_requerida'),
            descripcion: document.getElementById('descripcion'),
            formalizacion: document.getElementById('requiere_formalizacion')
        };
        
        let autoPriorityEnabled = true;

        function selectedText(select) {
            if (!select || select.selectedIndex < 0) return '';
            return select.options[select.selectedIndex].text.trim();
        }

        const projectTypes = <?= json_encode($tipos, JSON_UNESCAPED_UNICODE) ?>;

        function loadTiposByCategory(categoryId) {
            let html = '<option value="">Seleccione</option>';

            projectTypes
                .filter(type => String(type.category_id) === String(categoryId))
                .forEach(type => {
                    html += `<option value="${type.id}">${type.nombre}</option>`;
                });

            fields.tipo.innerHTML = html;
        }

        function toggleFormalizacionByCategory() {
            if (!fields.categoria || !fields.formalizacion) return;

            const selectedCategoryText = selectedText(fields.categoria);
            const isUxUi = selectedCategoryText === 'UX/UI y Frontend';

            if (isUxUi) {
                fields.formalizacion.checked = false;
                fields.formalizacion.disabled = true;
            } else {
                fields.formalizacion.disabled = false;
            }
        }
        /* function toggleFormalizacionByCategory() {
            const categoriaSelect = document.getElementById('category_id');
            const checkbox = document.getElementById('requiere_formalizacion');
    
            if (!categoriaSelect || !checkbox) return;
    
            const selectedText = categoriaSelect.options[categoriaSelect.selectedIndex]?.text.trim() || '';
    
            const isUxUi = selectedText === 'UX/UI y Frontend';
    
            if (isUxUi) {
                checkbox.checked = false;
                checkbox.disabled = true;
            } else {
                checkbox.disabled = false;
            }
        }*/



        function scoreValue(value) {
            const normalized = String(value || '').toLowerCase();
            if (normalized === 'alto' || normalized === 'alta') return 3;
            if (normalized === 'medio' || normalized === 'media') return 2;
            return 1;
        }

        function suggestedPriority() {
            const total = scoreValue(fields.impacto.value)
                + scoreValue(fields.urgencia.value)
                + scoreValue(fields.complejidad.value)
                + scoreValue(fields.riesgo.value);

            if (total >= 10) {
                return {
                    name: 'alta',
                    label: 'Alta',
                    dot: 'priority-alta',
                    badge: 'bg-danger',
                    reason: 'La combinación de impacto, urgencia, complejidad o riesgo es alta.'
                };
            }

            if (total >= 7) {
                return {
                    name: 'media',
                    label: 'Media',
                    dot: 'priority-media',
                    badge: 'bg-warning text-dark',
                    reason: 'Clasificación equilibrada para una solicitud estándar.'
                };
            }

            return {
                name: 'baja',
                label: 'Baja',
                dot: 'priority-baja',
                badge: 'bg-success',
                reason: 'La solicitud presenta bajo impacto, baja urgencia o menor complejidad.'
            };
        }

        function setPriorityByName(name) {
            if (!fields.prioridad) return;
            const options = Array.from(fields.prioridad.options);
            const option = options.find(opt => String(opt.dataset.name || '').toLowerCase().includes(name));
            if (option) {
                fields.prioridad.value = option.value;
            }
        }

        function suggestHours() {
            const complexity = String(fields.complejidad.value || '').toLowerCase();
            let suggested = 24;

            if (complexity === 'alta') suggested = 80;
            if (complexity === 'media') suggested = 24;
            if (complexity === 'baja') suggested = 8;

            const help = document.getElementById('hoursSuggestion');
            if (help) help.textContent = `Sugerido por complejidad ${fields.complejidad.value.toLowerCase()}: ${suggested} horas.`;

            if (!fields.horas.value) {
                fields.horas.placeholder = String(suggested);
            }
        }


/*         function toggleFormalizacionByCategory() {
            const selectedCategoryText = fields.categoria.options[fields.categoria.selectedIndex]?.text.trim() || '';

            const isUxUi = selectedCategoryText === 'UX/UI y Frontend';

            if (isUxUi) {
                fields.formalizacion.checked = false;
                fields.formalizacion.disabled = true;
                fields.formalizacionBlock.classList.add('text-muted');
                fields.formalizacionBlock.style.opacity = '0.5';
            } else {
                fields.formalizacion.disabled = false;
                fields.formalizacionBlock.classList.remove('text-muted');
                fields.formalizacionBlock.style.opacity = '1';
            }
        } */


        function updatePriorityPreview() {
            const priority = suggestedPriority();
            const dot = document.getElementById('priorityDot');
            const text = document.getElementById('priorityText');
            const reason = document.getElementById('priorityReason');

            if (dot) dot.className = `priority-dot ${priority.dot}`;
            if (text) text.textContent = `Prioridad sugerida: ${priority.label}`;
            if (reason) reason.textContent = priority.reason;

            if (autoPriorityEnabled) {
                setPriorityByName(priority.name);
            }
        }

        function updateSummary() {
            const priorityText = selectedText(fields.prioridad) || suggestedPriority().label;
            const priorityNormalized = priorityText.toLowerCase();
            const priorityBadge = document.getElementById('summaryPriority');

            document.getElementById('summaryTitle').textContent = fields.titulo.value || 'Sin título';
            document.getElementById('summaryType').textContent = selectedText(fields.tipo) || 'No seleccionado';
            document.getElementById('summaryArea').textContent = selectedText(fields.area) || 'No seleccionada';
            document.getElementById('summaryResponsible').textContent = selectedText(fields.responsable) || 'Sin asignar';
            document.getElementById('summaryHours').textContent = fields.horas.value ? `${fields.horas.value} horas` : 'No estimadas';

            if (priorityBadge) {
                priorityBadge.textContent = priorityText || 'Media';
                priorityBadge.className = 'badge ' + (
                    priorityNormalized.includes('alta') || priorityNormalized.includes('crítica') || priorityNormalized.includes('critica')
                        ? 'bg-danger'
                        : (priorityNormalized.includes('baja') ? 'bg-success' : 'bg-warning text-dark')
                );
            }
        }

        function updatePreview() {
            document.getElementById('previewTitle').textContent = fields.titulo.value || '-';
            document.getElementById('previewType').textContent = selectedText(fields.tipo) || '-';
            document.getElementById('previewArea').textContent = selectedText(fields.area) || '-';
            document.getElementById('previewResponsible').textContent = selectedText(fields.responsable) || 'Sin asignar';
            document.getElementById('previewPriority').textContent = selectedText(fields.prioridad) || '-';
            document.getElementById('previewDate').textContent = fields.fecha.value || '-';
            document.getElementById('previewHours').textContent = fields.horas.value ? `${fields.horas.value} horas` : '-';
            document.getElementById('previewScore').textContent = `Impacto: ${fields.impacto.value} · Urgencia: ${fields.urgencia.value} · Complejidad: ${fields.complejidad.value} · Riesgo: ${fields.riesgo.value}`;
            document.getElementById('previewDescription').textContent = fields.descripcion.value || '-';
        }

        function refreshAll() {
            updatePriorityPreview();
            suggestHours();
            updateSummary();
            updatePreview();
        }

        Object.values(fields).forEach(field => {
            if (!field || field === fields.categoria || field === fields.prioridad) return;

            field.addEventListener('input', refreshAll);

            field.addEventListener('change', function () {
                if (
                    field === fields.impacto ||
                    field === fields.urgencia ||
                    field === fields.complejidad ||
                    field === fields.riesgo
                ) {
                    autoPriorityEnabled = true;
                }

                refreshAll();
            });
        });

        if (fields.prioridad) {
            fields.prioridad.addEventListener('change', function () {
                autoPriorityEnabled = false;
                updateSummary();
                updatePreview();
            });
        }

        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });

        if (fields.categoria) {
            fields.categoria.addEventListener('change', function () {
                loadTiposByCategory(this.value);
                toggleFormalizacionByCategory();
                refreshAll();
            });
        }

        toggleFormalizacionByCategory();
        refreshAll();
    })();
</script>