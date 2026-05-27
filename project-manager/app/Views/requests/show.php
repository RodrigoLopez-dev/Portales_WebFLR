<div class="container mt-4">

    <style>
        .pm-doc-section {
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .pm-doc-section+.pm-doc-section {
            margin-top: 1rem;
        }

        .pm-doc-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            padding: .85rem 1rem;
            border-bottom: 1px solid #e5e7eb;
            background: #f8fafc;
        }

        .pm-doc-section-title {
            margin: 0;
            font-size: .98rem;
            font-weight: 700;
            color: #0f172a;
        }

        .pm-doc-section-subtitle {
            margin: .15rem 0 0;
            font-size: .82rem;
            color: #64748b;
        }

        .pm-doc-guide {
            border-left: 5px solid #0dcaf0;
        }

        .pm-doc-delivery {
            border-left: 5px solid #198754;
        }

        .pm-badge-template {
            background: #cff4fc;
            color: #055160;
            border: 1px solid #9eeaf9;
        }

        .pm-badge-delivery {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #a3cfbb;
        }

        .pm-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .02em;
            border-bottom: 1px solid #d9e2ec;
        }

        .pm-help-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            color: #475569;
            padding: .75rem .9rem;
        }
    </style>

    <h1 class="mb-3">Detalle de Solicitud</h1>

    <div class="card mb-4">
        <div class="card-body">
            <div><strong>Código:</strong> <?= e($request['codigo'] ?? '') ?></div>
            <div><strong>Título:</strong> <?= e($request['titulo'] ?? '') ?></div>
            <div><strong>Fase actual:</strong> <?= e($request['fase_nombre'] ?? 'Sin fase') ?></div>
            <div><strong>Avance:</strong> <?= e((string) ($request['porcentaje_avance'] ?? 0)) ?>%</div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Material complementario del proyecto</strong>
            <div class="d-flex gap-2">
                <a href="<?= e(base_url('/resources?request_id=' . (int) ($request['id'] ?? 0))) ?>"
                    class="btn btn-sm btn-outline-primary">Ver repositorio</a>
                <?php if (\App\Core\Auth::can('resources.create')): ?>
                    <a href="<?= e(base_url('/resources/create?request_id=' . (int) ($request['id'] ?? 0))) ?>"
                        class="btn btn-sm btn-primary">Agregar material</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($projectResources ?? [])): ?>
                <div class="text-muted small">Aún no existen anexos, imágenes, videos, transcripciones o enlaces
                    complementarios asociados.</div>
            <?php else: ?>
                <div class="row g-2">
                    <?php foreach (array_slice($projectResources, 0, 6) as $resource): ?>
                        <div class="col-md-4">
                            <div class="border rounded p-2 h-100">
                                <span
                                    class="badge bg-secondary mb-1"><?= e(($projectResourceTypes ?? [])[$resource['resource_type']] ?? 'Otro') ?></span>
                                <div class="fw-semibold small"><?= e($resource['title'] ?? '') ?></div>
                                <div class="text-muted small"><?= e($resource['created_at'] ?? '') ?></div>
                                <div class="mt-2">
                                    <?php if (!empty($resource['file_path'])): ?>
                                        <a class="btn btn-sm btn-outline-primary" target="_blank"
                                            href="<?= e(base_url('/resources/file?id=' . (int) $resource['id'] . '&mode=view')) ?>">Ver
                                            archivo</a>
                                    <?php endif; ?>
                                    <?php if (!empty($resource['external_url'])): ?>
                                        <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
                                            href="<?= e($resource['external_url']) ?>">Abrir enlace</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $currentPhaseId = (int) ($request['phase_id'] ?? 0);
    $currentPhaseName = (string) ($request['fase_nombre'] ?? 'Documentos de la fase');

    $uploadedByType = [];
    foreach (($documentsUploaded ?? []) as $phaseName => $docs) {
        foreach ($docs as $doc) {
            $typeId = (int) ($doc['document_type_id'] ?? 0);
            if ($typeId > 0 && !isset($uploadedByType[$typeId])) {
                $uploadedByType[$typeId] = $doc;
            }
        }
    }

    $mostrarDocumentosDeFase = true;
    if ($currentPhaseId === 3 && empty($request['requiere_formalizacion'])) {
        $mostrarDocumentosDeFase = false;
    }

    function renderPhaseReferenceDocuments(array $referenceDocuments): void
    {
        if (empty($referenceDocuments)) {
            return;
        }
        ?>
        <section class="pm-doc-section pm-doc-guide mb-3">
            <div class="pm-doc-section-header">
                <div>
                    <h2 class="pm-doc-section-title">📘 Documento guía / plantilla base</h2>
                    <p class="pm-doc-section-subtitle">Descarga o visualiza esta plantilla para usarla como estructura de
                        referencia.</p>
                </div>
                <span class="badge pm-badge-template"><?= count($referenceDocuments) ?> plantilla(s)</span>
            </div>

            <div class="p-3">
                <div class="pm-help-box small mb-3">
                    Este bloque corresponde al <strong>archivo base</strong>. No es el documento entregado por el usuario;
                    sirve como guía para completar la información requerida.
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0 pm-table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Documento guía</th>
                                <th>Obligatorio</th>
                                <th>Archivo base</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($referenceDocuments as $ref): ?>
                                <tr>
                                    <td><span class="badge pm-badge-template">Plantilla</span></td>
                                    <td>
                                        <div class="fw-bold"><?= e($ref['nombre'] ?? '') ?></div>
                                        <?php if (!empty($ref['descripcion'])): ?>
                                            <small class="text-muted"><?= e($ref['descripcion']) ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">Sin descripción registrada.</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($ref['obligatorio'])): ?>
                                            <span class="badge bg-danger">Obligatorio</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Opcional</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $archivoRuta = $ref['archivo_ruta'] ?? $ref['archivo_base_ruta'] ?? $ref['archivo_referencia'] ?? null; ?>
                                        <?php $archivoNombre = $ref['archivo_nombre'] ?? $ref['archivo_base_nombre'] ?? $ref['archivo_original'] ?? null; ?>
                                        <?php if (!empty($archivoRuta)): ?>
                                            <div class="fw-semibold small">
                                                <?= e($archivoNombre ?: basename((string) $archivoRuta)) ?>
                                            </div>
                                            <?php if (!empty($ref['archivo_peso'])): ?>
                                                <small
                                                    class="text-muted"><?= number_format(((int) $ref['archivo_peso']) / 1024, 1, ',', '.') ?>
                                                    KB</small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">Sin archivo base</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if (!empty($archivoRuta)): ?>
                                            <a href="<?= e(base_url('/documents/type-file?id=' . (int) $ref['id'] . '&mode=view')) ?>"
                                                target="_blank" class="btn btn-sm btn-outline-primary">Ver</a>
                                            <a href="<?= e(base_url('/documents/type-file?id=' . (int) $ref['id'] . '&mode=download')) ?>"
                                                class="btn btn-sm btn-success">Descargar</a>
                                        <?php else: ?>
                                            <span class="text-muted small">No disponible</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <?php
    }
    ?>

    <?php if (!empty($phases)): ?>
        <div class="card mb-4">
            <div class="card-header"><strong>Estado documental por fase</strong></div>
            <div class="card-body">
                <div class="row g-2">
                    <?php foreach ($phases as $phase): ?>
                        <?php
                        $pid = (int) ($phase['id'] ?? 0);
                        $docsPhase = $documentsByPhase[$pid] ?? [];
                        $required = 0;
                        $approved = 0;
                        foreach ($docsPhase as $docPhase) {
                            if ((int) ($docPhase['obligatorio'] ?? 0) === 1) {
                                $required++;
                                if ((int) ($docPhase['aprobado'] ?? 0) === 1) {
                                    $approved++;
                                }
                            }
                        }
                        ?>
                        <div class="col-md-3">
                            <div class="border rounded p-2 h-100 <?= $pid === $currentPhaseId ? 'bg-light' : '' ?>">
                                <div class="fw-bold"><?= e($phase['nombre'] ?? '') ?></div>
                                <small class="text-muted">
                                    Obligatorios aprobados: <?= $approved ?>/<?= $required ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$mostrarDocumentosDeFase): ?>
        <div class="alert alert-info">
            Esta solicitud no requiere formalización. El sistema omitirá esta fase en el avance automático.
        </div>
    <?php elseif (empty($phaseDocumentTypes)): ?>
        <div class="alert alert-warning">
            No hay tipos documentales activos configurados para la fase actual.
        </div>
    <?php else: ?>
        <div class="card mb-4">
            <div class="card-header">
                <strong><?= e($currentPhaseName) ?></strong>
            </div>

            <div class="card-body">
                <p class="mb-3"><strong>Documentos requeridos para avanzar automáticamente:</strong></p>

                <?php renderPhaseReferenceDocuments($phaseReferenceDocuments ?? []); ?>

                <section class="pm-doc-section pm-doc-delivery mb-3">
                    <div class="pm-doc-section-header">
                        <div>
                            <h2 class="pm-doc-section-title">📤 Documento a entregar</h2>
                            <p class="pm-doc-section-subtitle">Sube aquí el archivo completado correspondiente a la fase
                                actual.</p>
                        </div>
                        <span class="badge pm-badge-delivery">Entrega del usuario</span>
                    </div>

                    <div class="p-3">
                        <div class="pm-help-box small mb-3">
                            Este bloque corresponde al <strong>documento real que debe cargar el usuario</strong>. El estado
                            y la revisión se gestionan sobre este archivo.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle pm-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Documento a entregar</th>
                                        <th>Obligatorio</th>
                                        <th>Estado</th>
                                        <th>Archivo cargado</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($phaseDocumentTypes as $type): ?>
                                        <?php
                                        $typeId = (int) $type['id'];
                                        $uploaded = $uploadedByType[$typeId] ?? null;
                                        $estado = $uploaded['estado_documento'] ?? null;
                                        ?>
                                        <tr>
                                            <td><span class="badge pm-badge-delivery">Entrega</span></td>
                                            <td>
                                                <div><strong><?= e($type['nombre']) ?></strong></div>
                                                <?php if (!empty($type['descripcion'])): ?>
                                                    <small class="text-muted"><?= e($type['descripcion']) ?></small>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?php if (!empty($type['obligatorio'])): ?>
                                                    <span class="badge bg-danger">Sí</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No</span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?php if ($uploaded): ?>
                                                    <?php if ($estado === 'aprobado'): ?>
                                                        <span class="badge bg-success">Aprobado</span>
                                                    <?php elseif ($estado === 'rechazado'): ?>
                                                        <span class="badge bg-danger">Rechazado</span>
                                                    <?php elseif ($estado === 'inactivo'): ?>
                                                        <span class="badge bg-secondary">Inactivo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No cargado</span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?php if ($uploaded): ?>
                                                    <a href="<?= e(base_url('/assets/' . ($uploaded['ruta_archivo'] ?? ''))) ?>"
                                                        target="_blank" class="btn btn-sm btn-primary">Ver</a>
                                                    <div class="small text-muted mt-1"><?= e($uploaded['nombre_original'] ?? '') ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>

                                            <td style="min-width: 360px;">
                                                <?php
                                                $estadoDocumento = strtolower((string) ($uploaded['estado_documento'] ?? 'pendiente'));
                                                $puedeRevisarDocumento = $uploaded && $estadoDocumento === 'pendiente'; ?>

                                                <?php if ($puedeRevisarDocumento && \App\Core\Auth::can('documents.review')): ?>
                                                    <form method="POST" action="<?= e(base_url('/documents/review')) ?>"
                                                        class="mb-2">
                                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                        <input type="hidden" name="document_id" value="<?= e($uploaded['id']) ?>">
                                                        <input type="hidden" name="request_id" value="<?= e($request['id']) ?>">
                                                        <input type="hidden" name="return_to"
                                                            value="<?= e('/requests/advance?id=' . (int) $request['id']) ?>">

                                                        <div class="row g-2">
                                                            <div class="col-md-4">
                                                                <select name="decision" class="form-select form-select-sm" required>
                                                                    <option value="">Seleccione</option>
                                                                    <option value="aprobado">Aprobar</option>
                                                                    <option value="rechazado">Rechazar</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <input type="text" name="observacion_revision"
                                                                    class="form-control form-control-sm" placeholder="Observación">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <button class="btn btn-success btn-sm w-100">Guardar</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if ($uploaded && !$puedeRevisarDocumento): ?>
                                                    <?php if ($estadoDocumento === 'rechazado'): ?>
                                                        <div class="small text-danger mb-2">
                                                            Documento rechazado. Debe cargarse una nueva versión para volver a revisión.
                                                        </div>
                                                    <?php elseif ($estadoDocumento === 'aprobado'): ?>
                                                        <div class="small text-success mb-2">
                                                            Documento aprobado. Ya no requiere revisión.
                                                        </div>
                                                    <?php elseif ($estadoDocumento === 'inactivo'): ?>
                                                        <div class="small text-muted mb-2">
                                                            Documento inactivo. No puede ser revisado.
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                <?php if (\App\Core\Auth::can('documents.upload.phase')): ?>
                                                    <form method="POST" action="<?= e(base_url('/documents/upload-phase')) ?>"
                                                        enctype="multipart/form-data" class="row g-2">
                                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                        <input type="hidden" name="request_id" value="<?= e($request['id']) ?>">
                                                        <input type="hidden" name="phase_id" value="<?= e($currentPhaseId) ?>">
                                                        <input type="hidden" name="document_type_id" value="<?= e($typeId) ?>">

                                                        <div class="col-md-8">
                                                            <input type="file" name="archivo" class="form-control form-control-sm"
                                                                required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <button
                                                                class="btn <?= $uploaded ? 'btn-outline-primary' : 'btn-success' ?> btn-sm w-100">
                                                                <?= $uploaded ? 'Reemplazar' : 'Subir' ?>
                                                            </button>
                                                        </div>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php
                        $uploadedByPhaseAndType = [];

                        foreach (($documentsUploaded ?? []) as $group) {
                            foreach (($group ?? []) as $uploadedDoc) {
                                if (!is_array($uploadedDoc)) {
                                    continue;
                                }

                                $uploadedPhaseId = (int) ($uploadedDoc['phase_id'] ?? 0);
                                $uploadedTypeId = (int) ($uploadedDoc['document_type_id'] ?? 0);

                                if ($uploadedPhaseId <= 0 || $uploadedTypeId <= 0) {
                                    continue;
                                }

                                $key = $uploadedPhaseId . '_' . $uploadedTypeId;

                                if (
                                    !isset($uploadedByPhaseAndType[$key]) ||
                                    (int) ($uploadedDoc['id'] ?? 0) > (int) ($uploadedByPhaseAndType[$key]['id'] ?? 0)
                                ) {
                                    $uploadedByPhaseAndType[$key] = $uploadedDoc;
                                }
                            }
                        }
                        ?>
                        <?php if (!empty($previousOptionalDocuments)): ?>
                            <div class="card shadow-sm mt-4">
                                <div class="card-header bg-white">
                                    <strong>Documentos opcionales de fases anteriores</strong>
                                </div>

                                <div class="card-body">
                                    <p class="text-muted small mb-3">
                                        Puedes cargar documentos opcionales asociados a fases anteriores.
                                        Esta carga no modifica la fase actual de la solicitud.
                                    </p>

                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Fase</th>
                                                    <th>Documento</th>
                                                    <th>Estado</th>
                                                    <th style="min-width: 320px;">Revisión / Cargar archivo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($previousOptionalDocuments as $docType): ?>
                                                    <?php
                                                    $key = ((int) $docType['fase_id']) . '_' . ((int) $docType['id']);
                                                    $uploaded = $uploadedByPhaseAndType[$key] ?? null;
                                                    $estado = strtolower((string) ($uploaded['estado_documento'] ?? ''));
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                <?= e($docType['fase_nombre'] ?? 'Sin fase') ?>
                                                            </span>
                                                        </td>

                                                        <td>
                                                            <strong>
                                                                <?= e($docType['nombre'] ?? '-') ?>
                                                            </strong>
                                                            <?php if (!empty($docType['descripcion'])): ?>
                                                                <div class="small text-muted">
                                                                    <?= e($docType['descripcion']) ?>
                                                                </div>
                                                            <?php endif; ?>

                                                            <?php if (!empty($docType['archivo_ruta'])): ?>
                                                                <a href="<?= e(base_url('/assets/' . $docType['archivo_ruta'])) ?>"
                                                                    target="_blank" class="btn btn-outline-primary btn-sm">
                                                                    Ver plantilla
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted small">Sin plantilla</span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td style="max-width: 300px;">
                                                            <?php if ($uploaded): ?>
                                                                <?php if ($estado === 'aprobado'): ?>
                                                                    <span class="badge bg-success">Aprobado</span>
                                                                <?php elseif ($estado === 'rechazado'): ?>
                                                                    <span class="badge bg-danger">Rechazado</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                                                <?php endif; ?>

                                                                <div class="small text-muted mt-1">
                                                                    <?= e($uploaded['nombre_original'] ?? '') ?>
                                                                </div>

                                                                <?php if (!empty($uploaded['ruta_archivo'])): ?>
                                                                    <a href="<?= e(base_url('/assets/' . $uploaded['ruta_archivo'])) ?>"
                                                                        target="_blank" class="btn btn-outline-primary btn-sm mt-1">
                                                                        Ver archivo
                                                                    </a>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="text-muted small">No cargado</span>
                                                            <?php endif; ?>


                                                        </td>

                                                        <td>
                                                            <?php if ($uploaded && $estado === 'pendiente' && \App\Core\Auth::can('documents.review')): ?>
                                                                <form method="POST" action="<?= e(base_url('/documents/review')) ?>"
                                                                    class="mb-2">
                                                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                                    <input type="hidden" name="document_id"
                                                                        value="<?= e($uploaded['id']) ?>">
                                                                    <input type="hidden" name="request_id"
                                                                        value="<?= e($request['id']) ?>">
                                                                    <input type="hidden" name="return_to"
                                                                        value="<?= e('/requests/advance?id=' . (int) $request['id']) ?>">
                                                                    <div class="row g-2">
                                                                        <div class="col-md-4">
                                                                            <select name="decision" class="form-select form-select-sm"
                                                                                required>
                                                                                <option value="">Revisión</option>
                                                                                <option value="aprobado">Aprobar</option>
                                                                                <option value="rechazado">Rechazar</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-5">
                                                                            <input type="text" name="observacion_revision"
                                                                                class="form-control form-control-sm"
                                                                                placeholder="Observación">
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <button type="submit" class="btn btn-success btn-sm w-100">
                                                                                Guardar
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            <?php endif; ?>

                                                            <?php if (\App\Core\Auth::can('documents.upload.phase')): ?>
                                                                <form method="POST"
                                                                    action="<?= e(base_url('/documents/upload-phase')) ?>"
                                                                    enctype="multipart/form-data" class="row g-2">
                                                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                                    <input type="hidden" name="request_id"
                                                                        value="<?= e($request['id']) ?>">
                                                                    <input type="hidden" name="phase_id"
                                                                        value="<?= e($docType['fase_id']) ?>">
                                                                    <input type="hidden" name="document_type_id"
                                                                        value="<?= e($docType['id']) ?>">

                                                                    <div class="col-md-8">
                                                                        <input type="file" name="archivo"
                                                                            class="form-control form-control-sm" required>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                                                            Subir
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            <?php else: ?>
                                                                <span class="text-muted small">Sin permiso de
                                                                    carga</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>


                    </div>
                </section>

                <div class="alert alert-secondary mb-0 small">
                    <!-- El avance automático se ejecuta cuando los documentos obligatorios de la fase cumplen la regla definida: en Solicitud basta con cargar el documento obligatorio; desde Levantamiento en adelante deben estar aprobados. -->
                    El avance automático se ejecuta cuando los documentos obligatorios de la fase actual están aprobados.
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php
    $projectCompletedNotification = $_SESSION['project_completed'] ?? null;
    if (is_array($projectCompletedNotification)) {
        unset($_SESSION['project_completed']);
    } else {
        $projectCompletedNotification = null;
    }
    ?>

    <?php if ($projectCompletedNotification): ?>
        <div class="modal fade" id="projectCompletedModal" tabindex="-1" aria-labelledby="projectCompletedModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="projectCompletedModalLabel">Proyecto finalizado</h5>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2"><strong>✅ Se cumplió con todas las fases del proyecto.</strong></p>
                        <p class="mb-0">
                            La solicitud
                            <strong><?= e((string) ($projectCompletedNotification['codigo'] ?? '')) ?></strong>
                            <?= !empty($projectCompletedNotification['titulo']) ? ' - ' . e((string) $projectCompletedNotification['titulo']) : '' ?>
                            fue finalizada correctamente.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <a href="<?= e(base_url('/requests')) ?>" class="btn btn-success">Ir a solicitudes</a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            window.addEventListener('load', function () {
                const modalElement = document.getElementById('projectCompletedModal');
                const redirectUrl = <?= json_encode(base_url('/requests')) ?>;

                if (modalElement && window.bootstrap) {
                    const modal = new bootstrap.Modal(modalElement, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    modal.show();
                } else {
                    alert('✅ Se cumplió con todas las fases del proyecto.');
                }

                setTimeout(function () {
                    window.location.href = redirectUrl;
                }, 7500);
            });
        </script>
    <?php endif; ?>
</div>