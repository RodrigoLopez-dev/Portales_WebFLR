<div class="container mt-4">
    <h1 class="mb-1">Agregar material complementario</h1>
    <div class="text-muted mb-3"><?= e($request['codigo'] ?? '') ?> - <?= e($request['titulo'] ?? '') ?></div>

    <div class="card">
        <div class="card-body">
            <form method="post" action="<?= e(base_url('/resources/store')) ?>" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="request_id" value="<?= (int)($request['id'] ?? 0) ?>">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tipo</label>
                        <select name="resource_type" class="form-select" required>
                            <?php foreach ($resourceTypes as $key => $label): ?>
                                <option value="<?= e($key) ?>"><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Título</label>
                        <input type="text" name="title" class="form-control" maxlength="180" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Archivo</label>
                        <input type="file" name="resource_file" class="form-control">
                        <small class="text-muted">Puedes subir documentos, imágenes, videos, audios o comprimidos. Máximo sugerido: 200 MB.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Enlace externo</label>
                        <input type="url" name="external_url" class="form-control" placeholder="https://...">
                        <small class="text-muted">Útil para Drive, SharePoint, YouTube, Meet, Teams, etc.</small>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="is_public" value="1" class="form-check-input" id="is_public">
                            <label for="is_public" class="form-check-label">Visible como material público del proyecto</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Guardar material</button>
                    <a href="<?= e(base_url('/resources?request_id=' . (int)($request['id'] ?? 0))) ?>" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
