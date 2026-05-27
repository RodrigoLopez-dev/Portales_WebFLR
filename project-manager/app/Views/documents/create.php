<?php
$isEdit = !empty($documentType);
$action = $isEdit ? base_url('/documents/update') : base_url('/documents/store');
$title = $isEdit ? 'Editar documento' : 'Nuevo documento';
?>

<div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><?= e($title) ?></h1>
            <div class="text-muted">Define el documento requerido para una fase del proyecto.</div>
        </div>
        <a href="<?= e(base_url('/documents')) ?>" class="btn btn-outline-secondary">Volver</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">Datos del documento</div>
                <div class="card-body">
                    <form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" class="row g-3">
                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= e($documentType['id']) ?>">
                        <?php endif; ?>

                        <div class="col-md-8">
                            <label class="form-label">Nombre del documento</label>
                            <input type="text" name="nombre" class="form-control" required value="<?= e($documentType['nombre'] ?? '') ?>" placeholder="Ejemplo: Contrato de prestación de servicios">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Fase</label>
                            <select name="phase_id" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach (($catalogs['phases'] ?? []) as $phase): ?>
                                    <option value="<?= e($phase['id']) ?>" <?= selected($phase['id'], $documentType['fase_id'] ?? '') ?>>
                                        <?= e($phase['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Descripción / uso</label>
                            <textarea name="descripcion" class="form-control" rows="3" placeholder="Describe cuándo se solicita este documento y qué debe contener."><?= e($documentType['descripcion'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Archivo base / plantilla</label>
                            <input type="file" name="archivo_base" class="form-control">
                            <div class="form-text">
                                Este archivo será visible para los usuarios en la pantalla de avance de la solicitud, para que puedan verlo o descargarlo antes de cargar su documento completado.
                            </div>

                            <?php
                            $archivoRutaActual = $documentType['archivo_base_ruta']
                                ?? $documentType['archivo_referencia']
                                ?? null;
                            $archivoNombreActual = $documentType['archivo_base_nombre']
                                ?? $documentType['archivo_original']
                                ?? 'Archivo base';
                            $archivoPesoActual = $documentType['archivo_base_peso']
                                ?? $documentType['archivo_peso']
                                ?? null;
                            ?>

                            <?php if ($isEdit && !empty($archivoRutaActual)): ?>
                                <div class="alert alert-info mt-2 mb-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Archivo actual:</strong>
                                        <?= e($archivoNombreActual) ?>
                                        <?php if (!empty($archivoPesoActual)): ?>
                                            <span class="text-muted small ms-2">
                                                <?= e(number_format(((float)$archivoPesoActual) / 1024, 1)) ?> KB
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="<?= e(base_url('/documents/type-file?id=' . ($documentType['id'] ?? 0) . '&mode=view')) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Ver</a>
                                        <a href="<?= e(base_url('/documents/type-file?id=' . ($documentType['id'] ?? 0) . '&mode=download')) ?>" class="btn btn-sm btn-success">Descargar</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>


                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="obligatorio" value="1" id="obligatorio" <?= !empty($documentType['obligatorio']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="obligatorio">Documento obligatorio para avanzar de fase</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" <?= $isEdit ? (!empty($documentType['activo']) ? 'checked' : '') : 'checked' ?>>
                                <label class="form-check-label" for="activo">Activo</label>
                            </div>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary"><?= $isEdit ? 'Actualizar documento' : 'Crear documento' ?></button>
                            <a href="<?= e(base_url('/documents')) ?>" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">Cómo funciona</div>
                <div class="card-body small">
                    <p class="mb-2"><strong>Este catálogo puede incluir archivos base o plantillas.</strong></p>
                    <p class="mb-2">Aquí defines los documentos que serán requeridos por fase y, opcionalmente, adjuntas el formato que debe usar el usuario.</p>
                    <p class="mb-2">Luego, desde una solicitud/proyecto, el sistema pide cargar estos documentos según la fase actual.</p>
                    <p class="mb-0">Si marcas un documento como obligatorio, puede bloquear el avance de fase hasta que esté cargado y aprobado.</p>
                </div>
            </div>
        </div>
    </div>
</div>
