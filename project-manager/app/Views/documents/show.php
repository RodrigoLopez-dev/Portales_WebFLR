<?php
$type = $documentType ?? [];
$usages = $usages ?? [];
$isActive = (int) ($type['activo'] ?? 0) === 1;
$isRequired = (int) ($type['obligatorio'] ?? 0) === 1;
?>

<style>
    .document-detail-card {
        border: 0;
        border-radius: 14px;
    }

    .document-detail-label {
        font-size: .8rem;
        color: #6c757d;
        margin-bottom: .15rem;
    }

    .document-detail-value {
        font-weight: 700;
    }

    .document-file-row-inactive {
        opacity: .65;
    }
</style>

<div class="mt-4">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="mb-1">Ver documento</h1>
            <div class="text-muted">
                Ficha del documento requerido por fase. Aquí se visualiza la configuración del catálogo y sus archivos
                asociados en solicitudes.
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= e(base_url('/documents')) ?>" class="btn btn-outline-secondary">Volver</a>
            <?php if (\App\Core\Auth::can('documents.review')): ?>
                <a href="<?= e(base_url('/documents/edit?id=' . ($type['id'] ?? 0))) ?>" class="btn btn-primary">Editar</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card document-detail-card shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Información del documento</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="document-detail-label">Nombre</div>
                            <div class="document-detail-value fs-5"><?= e($type['nombre'] ?? '-') ?></div>
                        </div>

                        <div class="col-md-3">
                            <div class="document-detail-label">Fase</div>
                            <span class="badge bg-dark"><?= e($type['fase_nombre'] ?? 'Sin fase') ?></span>
                        </div>

                        <div class="col-md-3">
                            <div class="document-detail-label">Estado</div>
                            <?php if ($isActive): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <div class="document-detail-label">Obligatoriedad</div>
                            <?php if ($isRequired): ?>
                                <span class="badge bg-danger">Obligatorio</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Opcional</span>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <div class="document-detail-label">Usos</div>
                            <div class="document-detail-value"><?= e((string) count($usages)) ?></div>
                        </div>

                        <div class="col-md-3">
                            <div class="document-detail-label">Creado</div>
                            <div><?= e($type['created_at'] ?? '-') ?></div>
                        </div>

                        <div class="col-md-3">
                            <div class="document-detail-label">Actualizado</div>
                            <div><?= e($type['updated_at'] ?? '-') ?></div>
                        </div>

                        <div class="col-12">
                            <div class="document-detail-label">Descripción</div>
                            <div class="border rounded p-3 bg-light">
                                <?= !empty($type['descripcion']) ? nl2br(e($type['descripcion'])) : '<span class="text-muted">Sin descripción registrada.</span>' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card document-detail-card shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Qué muestra este botón</div>
                <div class="card-body">
                    <p class="mb-2">
                        Este módulo administra el <strong>catálogo documental</strong>, por eso el botón
                        <strong>Ver</strong> muestra la ficha del documento requerido.
                    </p>
                    <p class="mb-0 text-muted">
                        Los archivos físicos aparecen abajo solo cuando este documento ya fue cargado desde una
                        solicitud/proyecto.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card document-detail-card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Archivos cargados asociados a este documento</strong>
            <span class="badge bg-secondary"><?= e((string) count($usages)) ?></span>
        </div>

        <div class="card-body">
            <?php if (!empty($usages)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Solicitud</th>
                                <th>Fase</th>
                                <th>Archivo</th>
                                <th>Versión</th>
                                <th>Estado</th>
                                <th>Subido por</th>
                                <th>Fecha</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usages as $file): ?>
                                <?php
                                $estado = strtolower((string) ($file['estado_documento'] ?? 'pendiente'));
                                $inactive = $estado === 'inactivo';
                                switch ($estado) {
                                    case 'aprobado':
                                        $badge = 'bg-success';
                                        break;

                                    case 'rechazado':
                                        $badge = 'bg-danger';
                                        break;

                                    case 'inactivo':
                                        $badge = 'bg-secondary';
                                        break;

                                    default:
                                        $badge = 'bg-warning text-dark';
                                        break;
                                }
                                ?>
                                <tr class="<?= $inactive ? 'document-file-row-inactive table-light' : '' ?>">
                                    <td>
                                        <a href="<?= e(base_url('/requests/advance?id=' . ($file['request_id'] ?? 0))) ?>"
                                            class="fw-bold text-decoration-none">
                                            <?= e($file['codigo'] ?? '-') ?>
                                        </a>
                                        <div class="small text-muted"><?= e($file['solicitud_titulo'] ?? '') ?></div>
                                    </td>
                                    <td><?= e($file['fase_nombre'] ?? '-') ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= e($file['nombre_original'] ?? '-') ?></div>
                                        <?php if (!empty($file['peso'])): ?>
                                            <div class="small text-muted"><?= e(number_format(((int) $file['peso']) / 1024, 1)) ?> KB
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>v<?= e((string) ($file['version_calculada'] ?? 1)) ?></td>
                                    <td><span class="badge <?= e($badge) ?>"><?= e(ucfirst($estado ?: 'pendiente')) ?></span>
                                    </td>
                                    <td><?= e($file['subido_por_nombre'] ?? '-') ?></td>
                                    <td><?= e($file['created_at'] ?? '-') ?></td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end flex-wrap gap-2">
                                            <a href="<?= e(base_url('/documents/view-file?id=' . ($file['id'] ?? 0))) ?>"
                                                target="_blank" class="btn btn-outline-primary btn-sm">Ver</a>
                                            <a href="<?= e(base_url('/documents/download?id=' . ($file['id'] ?? 0))) ?>"
                                                class="btn btn-success btn-sm">Descargar</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-light mb-0">
                    Este documento todavía no tiene archivos cargados en solicitudes/proyectos.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>