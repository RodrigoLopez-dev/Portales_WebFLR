<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="mb-1">Material complementario</h1>
            <div class="text-muted">
                <?= e($request['codigo'] ?? '') ?> - <?= e($request['titulo'] ?? '') ?>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= e(base_url('/requests/advance?id=' . (int)($request['id'] ?? 0))) ?>" class="btn btn-outline-secondary">Volver al proyecto</a>
            <?php if (\App\Core\Auth::can('resources.create')): ?>
                <a href="<?= e(base_url('/resources/create?request_id=' . (int)($request['id'] ?? 0))) ?>" class="btn btn-primary">Agregar material</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($resources)): ?>
                <div class="alert alert-info mb-0">
                    Este proyecto aún no tiene material complementario asociado.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Título</th>
                                <th>Estado</th>
                                <th>Origen</th>
                                <th>Subido por</th>
                                <th>Fecha</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resources as $resource): ?>
                                <?php $isActive = !isset($resource['is_active']) || (int)$resource['is_active'] === 1; ?>
                                <tr class="<?= $isActive ? '' : 'table-warning opacity-75' ?>">
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= e($resourceTypes[$resource['resource_type']] ?? 'Otro') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">
                                            <?= e($resource['title'] ?? '') ?>
                                            <?php if (!$isActive): ?>
                                                <span class="badge bg-warning text-dark ms-1">Inhabilitado</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($resource['description'])): ?>
                                            <small class="text-muted"><?= e($resource['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isActive): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Inhabilitado</span>
                                            <div class="small text-muted mt-1">
                                                <?php if (!empty($resource['inactivated_at'])): ?>
                                                    Fecha: <?= e($resource['inactivated_at']) ?><br>
                                                <?php endif; ?>
                                                <?php if (!empty($resource['inactivated_by_name'])): ?>
                                                    Por: <?= e($resource['inactivated_by_name']) ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($resource['file_path'])): ?>
                                            <div class="small">Archivo: <?= e($resource['original_name'] ?? basename($resource['file_path'])) ?></div>
                                            <?php if (!empty($resource['file_size'])): ?>
                                                <small class="text-muted"><?= number_format(((int)$resource['file_size']) / 1024, 1, ',', '.') ?> KB</small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if (!empty($resource['external_url'])): ?>
                                            <div class="small"><a href="<?= e($resource['external_url']) ?>" target="_blank" rel="noopener">Enlace externo</a></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= e($resource['uploaded_by_name'] ?? 'Sin registro') ?></td>
                                    <td><small><?= e($resource['created_at'] ?? '') ?></small></td>
                                    <td class="text-end">
                                        <div class="d-flex flex-nowrap justify-content-end align-items-center gap-1">
                                            <?php if (!empty($resource['file_path'])): ?>
                                                <a class="btn btn-sm btn-outline-primary" target="_blank" href="<?= e(base_url('/resources/file?id=' . (int)$resource['id'] . '&mode=view')) ?>">Ver</a>
                                                <a class="btn btn-sm btn-success" href="<?= e(base_url('/resources/file?id=' . (int)$resource['id'] . '&mode=download')) ?>">Descargar</a>
                                            <?php endif; ?>
                                            <?php if ($isActive && \App\Core\Auth::can('resources.edit')): ?>
                                                <a class="btn btn-sm btn-outline-secondary" href="<?= e(base_url('/resources/edit?id=' . (int)$resource['id'])) ?>">Editar</a>
                                            <?php endif; ?>
                                            <?php if ($isActive && \App\Core\Auth::can('resources.delete')): ?>
                                                <form method="post" action="<?= e(base_url('/resources/inactivate')) ?>" class="m-0 p-0" onsubmit="return confirm('¿Desea inhabilitar este material? El archivo no será eliminado, pero dejará de considerarse dentro del proyecto.');">
                                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                    <input type="hidden" name="id" value="<?= (int)$resource['id'] ?>">
                                                    <button class="btn btn-sm btn-outline-danger text-nowrap" type="submit">Inhabilitar</button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if (!$isActive): ?>
                                                <span class="btn btn-sm btn-outline-secondary disabled text-nowrap" aria-disabled="true">Inhabilitado</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
