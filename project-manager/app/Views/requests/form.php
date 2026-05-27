<?php
$areas       = $catalogs['areas'] ?? [];
$categories  = $catalogs['categories'] ?? [];
$types       = $catalogs['types'] ?? [];
$priorities  = $catalogs['priorities'] ?? [];
$statuses    = $catalogs['statuses'] ?? [];
$users       = $catalogs['users'] ?? [];
?>

<div class="container mt-4">
    <h2 class="mb-4"><?= !empty($request['id']) ? 'Editar solicitud' : 'Nueva solicitud' ?></h2>

    <form method="POST" action="<?= e(base_url($action ?? '/requests/store')) ?>">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <?php if (!empty($request['id'])): ?>
            <input type="hidden" name="id" value="<?= e($request['id']) ?>">
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Título</label>
                <input
                    type="text"
                    name="titulo"
                    class="form-control"
                    value="<?= e($request['titulo'] ?? '') ?>"
                    required
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Categoría</label>
                <select id="category_id" name="category_id" class="form-select" required>
                    <option value="">Seleccione</option>
                    <?php foreach ($categories as $item): ?>
                        <option
                            value="<?= e($item['id']) ?>"
                            <?= (string)($request['category_id'] ?? '') === (string)$item['id'] ? 'selected' : '' ?>
                        >
                            <?= e($item['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Tipo de solicitud</label>
                <select id="tipo_id" name="tipo_id" class="form-select" required>
                    <option value="">Seleccione una categoría primero</option>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label">Descripción</label>
                <textarea
                    name="descripcion"
                    class="form-control"
                    rows="4"
                ><?= e($request['descripcion'] ?? '') ?></textarea>
            </div>

            <div class="col-md-3">
                <label class="form-label">Prioridad</label>
                <select name="prioridad_id" class="form-select" required>
                    <option value="">Seleccione</option>
                    <?php foreach ($priorities as $item): ?>
                        <option
                            value="<?= e($item['id']) ?>"
                            <?= (string)($request['prioridad_id'] ?? '') === (string)$item['id'] ? 'selected' : '' ?>
                        >
                            <?= e($item['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Área</label>
                <select name="area_id" class="form-select" required>
                    <option value="">Seleccione</option>
                    <?php foreach ($areas as $item): ?>
                        <option
                            value="<?= e($item['id']) ?>"
                            <?= (string)($request['area_id'] ?? '') === (string)$item['id'] ? 'selected' : '' ?>
                        >
                            <?= e($item['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Responsable</label>
                <select name="responsable_id" class="form-select">
                    <option value="">Sin asignar</option>
                    <?php foreach ($users as $item): ?>
                        <option
                            value="<?= e($item['id']) ?>"
                            <?= (string)($request['responsable_id'] ?? '') === (string)$item['id'] ? 'selected' : '' ?>
                        >
                            <?= e($item['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Fecha requerida</label>
                <input
                    type="date"
                    name="fecha_requerida"
                    class="form-control"
                    value="<?= e($request['fecha_requerida'] ?? '') ?>"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Impacto</label>
                <select name="impacto" class="form-select">
                    <?php foreach (['Alto', 'Medio', 'Bajo'] as $item): ?>
                        <option
                            value="<?= e($item) ?>"
                            <?= ($request['impacto'] ?? 'Medio') === $item ? 'selected' : '' ?>
                        >
                            <?= e($item) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Urgencia</label>
                <select name="urgencia" class="form-select">
                    <?php foreach (['Alta', 'Media', 'Baja'] as $item): ?>
                        <option
                            value="<?= e($item) ?>"
                            <?= ($request['urgencia'] ?? 'Media') === $item ? 'selected' : '' ?>
                        >
                            <?= e($item) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Complejidad</label>
                <select name="complejidad" class="form-select">
                    <?php foreach (['Alta', 'Media', 'Baja'] as $item): ?>
                        <option
                            value="<?= e($item) ?>"
                            <?= ($request['complejidad'] ?? 'Media') === $item ? 'selected' : '' ?>
                        >
                            <?= e($item) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Riesgo</label>
                <select name="riesgo" class="form-select">
                    <?php foreach (['Alto', 'Medio', 'Bajo'] as $item): ?>
                        <option
                            value="<?= e($item) ?>"
                            <?= ($request['riesgo'] ?? 'Medio') === $item ? 'selected' : '' ?>
                        >
                            <?= e($item) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="estado_id" class="form-select" required>
                    <option value="">Seleccione</option>
                    <?php foreach ($statuses as $item): ?>
                        <option
                            value="<?= e($item['id']) ?>"
                            <?= (string)($request['estado_id'] ?? '') === (string)$item['id'] ? 'selected' : '' ?>
                        >
                            <?= e($item['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Horas estimadas</label>
                <input
                    type="number"
                    step="0.01"
                    name="esfuerzo_estimado_horas"
                    class="form-control"
                    value="<?= e($request['esfuerzo_estimado_horas'] ?? '0') ?>"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Horas reales</label>
                <input
                    type="number"
                    step="0.01"
                    name="esfuerzo_real_horas"
                    class="form-control"
                    value="<?= e($request['esfuerzo_real_horas'] ?? '0') ?>"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Avance %</label>
                <input
                    type="number"
                    min="0"
                    max="100"
                    name="porcentaje_avance"
                    class="form-control"
                    value="<?= e($request['porcentaje_avance'] ?? '0') ?>"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Inicio estimado</label>
                <input
                    type="date"
                    name="fecha_inicio_estimada"
                    class="form-control"
                    value="<?= e($request['fecha_inicio_estimada'] ?? '') ?>"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Fin estimado</label>
                <input
                    type="date"
                    name="fecha_fin_estimada"
                    class="form-control"
                    value="<?= e($request['fecha_fin_estimada'] ?? '') ?>"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Inicio real</label>
                <input
                    type="date"
                    name="fecha_inicio_real"
                    class="form-control"
                    value="<?= e($request['fecha_inicio_real'] ?? '') ?>"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Fin real</label>
                <input
                    type="date"
                    name="fecha_fin_real"
                    class="form-control"
                    value="<?= e($request['fecha_fin_real'] ?? '') ?>"
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Dependencia externa</label>
                <input
                    type="text"
                    name="dependencia_externa"
                    class="form-control"
                    value="<?= e($request['dependencia_externa'] ?? '') ?>"
                >
            </div>

            <div class="col-md-6" id="formalizacion_block">
                <label class="form-label d-block">Formalización</label>
                <div class="form-check mt-2">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="requiere_formalizacion"
                        value="1"
                        id="requiere_formalizacion"
                        <?= !empty($request['requiere_formalizacion'] ?? null) ? 'checked' : '' ?>
                    >
                    <label class="form-check-label" for="requiere_formalizacion">
                        Requiere formalización con proveedor externo
                    </label>
                </div>
                <small class="text-muted">
                    Marcar cuando el proyecto requiera NDA, contrato de prestación de servicios, SLA u otros documentos formales.
                </small>
            </div>

            <div class="col-12">
                <label class="form-label">Motivo de bloqueo</label>
                <input
                    type="text"
                    name="motivo_bloqueo"
                    class="form-control"
                    value="<?= e($request['motivo_bloqueo'] ?? '') ?>"
                    placeholder="Completar si el estado es Bloqueada"
                >
            </div>
        </div>


        <script>
        const projectTypes = <?= json_encode($types, JSON_UNESCAPED_UNICODE) ?>;
        const selectedTipoId = "<?= e((string)($request['tipo_id'] ?? '')) ?>";

        function loadTypesByCategory(categoryId) {
            const tipoSelect = document.getElementById('tipo_id');

            let html = '<option value="">Seleccione</option>';

            projectTypes
                .filter(type => String(type.category_id) === String(categoryId))
                .forEach(type => {
                    const selected = String(type.id) === String(selectedTipoId) ? 'selected' : '';
                    html += `<option value="${type.id}" ${selected}>${type.nombre}</option>`;
                });

            tipoSelect.innerHTML = html;
        }

        document.getElementById('category_id').addEventListener('change', function () {
            loadTypesByCategory(this.value);
        });

        // Para edición (cuando ya hay valor seleccionado)
        if (document.getElementById('category_id').value) {
            loadTypesByCategory(document.getElementById('category_id').value);
        }
        </script>



        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <?= !empty($request['id']) ? 'Guardar cambios' : 'Guardar solicitud' ?>
            </button>

            <a href="<?= e(base_url('/requests')) ?>" class="btn btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
(function () {
    const categorySelect = document.getElementById('category_id');
    const formalizacionCheckbox = document.getElementById('requiere_formalizacion');
    const formalizacionBlock = document.getElementById('formalizacion_block');

    function toggleFormalizacionByCategory() {
        if (!categorySelect || !formalizacionCheckbox || !formalizacionBlock) return;

        const selectedText = categorySelect.options[categorySelect.selectedIndex]?.text.trim() || '';
        const isUxUi = selectedText === 'UX/UI y Frontend';

        if (isUxUi) {
            formalizacionCheckbox.checked = false;
            formalizacionCheckbox.disabled = true;
            formalizacionBlock.classList.add('text-muted');
            formalizacionBlock.style.opacity = '0.5';
        } else {
            formalizacionCheckbox.disabled = false;
            formalizacionBlock.classList.remove('text-muted');
            formalizacionBlock.style.opacity = '1';
        }
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', toggleFormalizacionByCategory);
    }

    toggleFormalizacionByCategory();
})();
</script>