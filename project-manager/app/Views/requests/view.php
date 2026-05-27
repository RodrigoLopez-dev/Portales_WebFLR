<div class="container mt-4 mb-4">
    <h1 class="mb-3">Historial de Solicitud</h1>

    <div class="card mb-4">
        <div class="card-body">
            <div><strong>Código:</strong> <?= e($request['codigo'] ?? '') ?></div>
            <div><strong>Título:</strong> <?= e($request['titulo'] ?? '') ?></div>
            <div><strong>Estado:</strong> <?= e($request['estado'] ?? '-') ?></div>
            <div><strong>Fase actual:</strong> <?= e($request['fase_nombre'] ?? 'Sin fase') ?></div>
            <div><strong>Responsable:</strong> <?= e($request['responsable'] ?? '-') ?></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Historial general</strong>
                </div>
                <div class="card-body">
                    <?php if (!empty($history)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                        <th>Acción</th>
                                        <th>Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $item): ?>
                                        <tr>
                                            <td><?= e($item['created_at'] ?? '') ?></td>
                                            <td><?= e($item['nombre'] ?? '-') ?></td>
                                            <td><?= e($item['accion'] ?? '-') ?></td>
                                            <td><?= e($item['detalle'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-muted">No hay historial general registrado.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Historial de fases</strong>
                </div>
                <div class="card-body">
                    <?php if (!empty($phaseHistory)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                        <th>Origen</th>
                                        <th>Destino</th>
                                        <th>Observación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($phaseHistory as $item): ?>
                                        <tr>
                                            <td><?= e($item['created_at'] ?? '') ?></td>
                                            <td><?= e($item['nombre'] ?? '-') ?></td>
                                            <td><?= e($item['fase_origen_id'] ?? '-') ?></td>
                                            <td><?= e($item['fase_destino_id'] ?? '-') ?></td>
                                            <td><?= e($item['observacion'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-muted">No hay cambios de fase registrados.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Comentarios</strong>
                </div>
                <div class="card-body">
                    <?php if (!empty($comments)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                        <th>Tipo</th>
                                        <th>Comentario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($comments as $item): ?>
                                        <tr>
                                            <td><?= e($item['created_at'] ?? '') ?></td>
                                            <td><?= e($item['nombre'] ?? '-') ?></td>
                                            <td><?= e($item['tipo_comentario'] ?? '-') ?></td>
                                            <td><?= e($item['comentario'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-muted">No hay comentarios registrados.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Documentos / adjuntos</strong>
                </div>
                <div class="card-body">
                    <?php if (!empty($attachmentsGrouped)): ?>
                        <div class="accordion" id="accordionDocumentosPorFase">
                            <?php $faseIndex = 0; ?>
                            <?php foreach ($attachmentsGrouped as $fase => $docs): ?>
                                <?php
                                $faseIndex++;
                                $collapseId = 'faseDocs' . $faseIndex;
                                $headingId = 'headingFase' . $faseIndex;
                                ?>
                                <div class="card mb-2 border">
                                    <div class="card-header d-flex justify-content-between align-items-center" id="<?= e($headingId) ?>">
                                        <div>
                                            <strong><?= e($fase) ?></strong>
                                            <span class="badge bg-secondary ms-2"><?= e((string)count($docs)) ?> documento(s)</span>
                                        </div>

                                        <button
                                            class="btn btn-sm btn-outline-primary toggle-phase-btn"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#<?= e($collapseId) ?>"
                                            aria-expanded="false"
                                            aria-controls="<?= e($collapseId) ?>"
                                        >
                                            +
                                        </button>
                                    </div>

                                    <div id="<?= e($collapseId) ?>" class="collapse" aria-labelledby="<?= e($headingId) ?>" data-bs-parent="#accordionDocumentosPorFase">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-striped mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha</th>
                                                            <th>Documento</th>
                                                            <th>Estado</th>
                                                            <th>Archivo</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($docs as $item): ?>
                                                            <tr>
                                                                <td><?= e($item['created_at'] ?? '') ?></td>
                                                                <td><?= e($item['nombre_original'] ?? '-') ?></td>
                                                                <td><?= e($item['estado_documento'] ?? '-') ?></td>
                                                                <td>
                                                                    <?php if (!empty($item['ruta_archivo'])): ?>
                                                                        <a href="<?= e(base_url('/assets/' . $item['ruta_archivo'])) ?>" target="_blank">Ver</a>
                                                                    <?php else: ?>
                                                                        -
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const buttons = document.querySelectorAll('.toggle-phase-btn');

                                buttons.forEach(function (button) {
                                    const targetSelector = button.getAttribute('data-bs-target');
                                    const target = document.querySelector(targetSelector);

                                    if (!target) return;

                                    target.addEventListener('show.bs.collapse', function () {
                                        button.textContent = '-';
                                    });

                                    target.addEventListener('hide.bs.collapse', function () {
                                        button.textContent = '+';
                                    });
                                });
                            });
                        </script>
                    <?php else: ?>
                        <div class="text-muted">No hay documentos registrados.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <a href="<?= e(base_url('/requests/advance?id=' . ($request['id'] ?? 0))) ?>" class="btn btn-success">
            Ir a Avanzar
        </a>
        <a href="<?= e(base_url('/requests')) ?>" class="btn btn-secondary">
            Volver al listado
        </a>
    </div>
</div>