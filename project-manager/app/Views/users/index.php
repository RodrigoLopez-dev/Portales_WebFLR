<?php
$currentUser = $user ?? ($_SESSION['user'] ?? []);
$currentUserId = (int)($currentUser['id'] ?? 0);
$filters = $filters ?? [];
$roles = $roles ?? [];

function usersUrl(array $extra = []): string
{
    $params = array_merge($_GET, $extra);

    $params = array_filter($params, function ($v) {
        return $v !== null && $v !== '';
    });

    $query = http_build_query($params);

    return base_url('/users' . ($query ? '?' . $query : ''));
}

function usersExportUrl(): string
{
    $params = array_filter($_GET, function ($v) {
        return $v !== null && $v !== '';
    });

    $query = http_build_query($params);

    return base_url('/users/export' . ($query ? '?' . $query : ''));
}

function userAccessInfo(array $u): array
{
    $hasGoogle = !empty($u['google_id']);
    $hasLocal = !empty($u['password_hash']);

    if ($hasGoogle && $hasLocal) {
        return ['Google + Local', 'bg-primary'];
    }

    if ($hasGoogle) {
        return ['Google', 'bg-danger'];
    }

    return ['Local', 'bg-secondary'];
}
?>

<style>
    .user-avatar-sm {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #dee2e6;
        background: #6c757d;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex: 0 0 38px;
    }

    .users-filter-card,
    .users-list-card {
        border: 0;
        border-radius: 14px;
    }

    .users-action-btn {
        min-width: 86px;
    }
</style>

<div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Gestión de Usuarios</h1>
            <div class="text-muted">Administra accesos locales, vínculo con Google, roles y estado de usuarios.</div>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= e(usersExportUrl()) ?>" class="btn btn-success">Exportar Excel</a>
            <a href="<?= e(base_url('/users/create')) ?>" class="btn btn-primary">Nuevo usuario</a>
        </div>
    </div>

    <div class="card users-filter-card shadow-sm mb-4">
        <div class="card-header bg-white">Filtros</div>
        <div class="card-body">
            <form method="GET" action="<?= e(base_url('/users')) ?>" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        placeholder="Nombre o email"
                        value="<?= e($filters['q'] ?? '') ?>"
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">Rol</label>
                    <select name="rol_id" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= (int)$role['id'] ?>" <?= (string)($filters['rol_id'] ?? '') === (string)$role['id'] ? 'selected' : '' ?>>
                                <?= e($role['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="" <?= (string)($filters['estado'] ?? '') === '' ? 'selected' : '' ?>>Todos</option>
                        <option value="1" <?= (string)($filters['estado'] ?? '') === '1' ? 'selected' : '' ?>>Activos</option>
                        <option value="0" <?= (string)($filters['estado'] ?? '') === '0' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Acceso</label>
                    <select name="access" class="form-select">
                        <option value="" <?= (string)($filters['access'] ?? '') === '' ? 'selected' : '' ?>>Todos</option>
                        <option value="google" <?= (string)($filters['access'] ?? '') === 'google' ? 'selected' : '' ?>>Con Google</option>
                        <option value="local" <?= (string)($filters['access'] ?? '') === 'local' ? 'selected' : '' ?>>Solo local</option>
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="<?= e(base_url('/users')) ?>" class="btn btn-outline-secondary">Limpiar</a>
                    <button class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card users-list-card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span>Listado de usuarios <span class="text-muted">(<?= count($users ?? []) ?>)</span></span>
        </div>

        <div class="card-body">
            <?php if (!empty($users)): ?>
                <div class="w-100">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Roles</th>
                                <th>Acceso</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <?php
                                $id = (int)($u['id'] ?? 0);
                                $isCurrentUser = $id === $currentUserId;
                                [$accessLabel, $accessClass] = userAccessInfo($u);
                                $hasGoogle = !empty($u['google_id']);
                                $isActive = (int)($u['estado'] ?? 0) === 1;
                                $modalToggleId = 'toggleUserModal' . $id;
                                $modalUnlinkId = 'unlinkGoogleModal' . $id;
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!empty($u['avatar_url'])): ?>
                                                <img src="<?= e($u['avatar_url']) ?>" class="user-avatar-sm" alt="Avatar">
                                            <?php else: ?>
                                                <div class="user-avatar-sm">
                                                    <?= e(mb_strtoupper(mb_substr((string)($u['nombre'] ?? 'U'), 0, 1))) ?>
                                                </div>
                                            <?php endif; ?>

                                            <div>
                                                <div class="fw-semibold">
                                                    <?= e($u['nombre']) ?>
                                                    <?php if ($isCurrentUser): ?>
                                                        <span class="badge bg-info text-dark ms-1">Tú</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="small text-muted">ID #<?= e((string)$id) ?></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td><?= e($u['email']) ?></td>

                                    <td>
                                        <?php foreach (array_filter(array_map('trim', explode(',', (string)($u['roles'] ?? $u['rol'] ?? '')))) as $roleName): ?>
                                            <span class="badge bg-dark me-1 mb-1"><?= e($roleName) ?></span>
                                        <?php endforeach; ?>
                                    </td>

                                    <td>
                                        <span class="badge <?= e($accessClass) ?>"><?= e($accessLabel) ?></span>
                                        <?php if ($hasGoogle): ?>
                                            <div class="small text-muted mt-1">Cuenta Google vinculada</div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($isActive): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end">
                                        <div class="d-flex flex-wrap justify-content-end gap-2">
                                            <a href="<?= e(base_url('/users/edit?id=' . $id)) ?>" class="btn btn-outline-primary btn-sm users-action-btn">
                                                Editar
                                            </a>

                                            <?php if ($hasGoogle): ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-danger btn-sm users-action-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#<?= e($modalUnlinkId) ?>"
                                                    <?= $isCurrentUser ? 'disabled title="No puedes desvincular tu propia cuenta desde aquí"' : '' ?>
                                                >
                                                    Desvincular Google
                                                </button>
                                            <?php endif; ?>

                                            <button
                                                type="button"
                                                class="btn <?= $isActive ? 'btn-outline-secondary' : 'btn-outline-success' ?> btn-sm users-action-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#<?= e($modalToggleId) ?>"
                                                <?= $isCurrentUser ? 'disabled title="No puedes desactivar tu propio usuario"' : '' ?>
                                            >
                                                <?= $isActive ? 'Desactivar' : 'Activar' ?>
                                            </button>
                                        </div>

                                        <div class="modal fade text-start" id="<?= e($modalToggleId) ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="POST" action="<?= e(base_url('/users/toggle')) ?>" class="modal-content">
                                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                    <input type="hidden" name="id" value="<?= e((string)$id) ?>">
                                                    <input type="hidden" name="return_to" value="<?= e('/users' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')) ?>">

                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <?= $isActive ? 'Desactivar usuario' : 'Activar usuario' ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <?php if ($isActive): ?>
                                                            <p class="mb-1">¿Seguro que deseas desactivar a este usuario?</p>
                                                            <strong><?= e($u['nombre']) ?></strong>
                                                            <div class="small text-muted mt-2">
                                                                Al desactivarlo, no podrá iniciar sesión hasta que sea activado nuevamente.
                                                            </div>
                                                        <?php else: ?>
                                                            <p class="mb-1">¿Seguro que deseas activar a este usuario?</p>
                                                            <strong><?= e($u['nombre']) ?></strong>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button class="btn <?= $isActive ? 'btn-danger' : 'btn-success' ?>">
                                                            <?= $isActive ? 'Desactivar' : 'Activar' ?>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <?php if ($hasGoogle): ?>
                                            <div class="modal fade text-start" id="<?= e($modalUnlinkId) ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form method="POST" action="<?= e(base_url('/users/unlink-google')) ?>" class="modal-content">
                                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                        <input type="hidden" name="id" value="<?= e((string)$id) ?>">
                                                        <input type="hidden" name="return_to" value="<?= e('/users' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')) ?>">

                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Desvincular Google</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                        </div>

                                                        <div class="modal-body">
                                                            <p class="mb-1">¿Seguro que deseas desvincular Google de este usuario?</p>
                                                            <strong><?= e($u['nombre']) ?></strong>
                                                            <div class="small text-muted mt-2">
                                                                El usuario conservará su cuenta local, pero deberá volver a vincular Google en un próximo ingreso.
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button class="btn btn-danger">Desvincular</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-light mb-0">No hay usuarios que coincidan con los filtros.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
