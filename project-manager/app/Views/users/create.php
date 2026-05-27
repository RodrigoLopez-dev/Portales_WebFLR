<div class="mt-4">
    <h1 class="mb-4">Nuevo Usuario</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= e(base_url('/users/store')) ?>" class="row g-3">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <div class="col-md-6">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Correo</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Roles</label>

                    <select
                        name="role_ids[]"
                        class="form-select"
                        multiple
                        required
                        size="5"
                    >
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= e($role['id']) ?>">
                                <?= e($role['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div id="roleCombinationWarning" class="alert alert-danger py-2 mt-2 d-none">
                        La combinación de roles seleccionada no es válida.
                    </div>

                    <div class="form-text mt-2">
                        <div class="text-warning fw-bold mb-2">
                            Puede seleccionar múltiples roles utilizando Ctrl + Click.
                        </div>

                        <span class="text-danger fw-bold text-decoration-underline">
                            Reglas:
                        </span>

                        <ul class="mb-0 mt-1 ps-3">
                            <li>
                                <strong>Administrador:</strong>
                                debe ir solo.
                            </li>

                            <li>
                                <strong>Solicitante:</strong>
                                no debe combinarse con roles técnicos.
                            </li>

                            <li>
                                <strong>Jefe de Proyecto:</strong>
                                puede combinarse con Analista, Desarrollador y QA.
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="estado" class="form-check-input" id="estado" checked>
                        <label class="form-check-label" for="estado">Activo</label>
                    </div>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button id="btnSaveUser" class="btn btn-primary">Guardar</button>
                    <a href="<?= e(base_url('/users')) ?>" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>   
    document.addEventListener('DOMContentLoaded', function () {
    const rolesSelect = document.querySelector('select[name="role_ids[]"]');
    const warning = document.getElementById('roleCombinationWarning');
    const saveButton = document.getElementById('btnSaveUser');

    if (!rolesSelect || !warning || !saveButton) {
        return;
    }

    function getSelectedRoleNames() {
        return Array.from(rolesSelect.selectedOptions).map(function (option) {
            return option.textContent.replace('✓', '').trim();
        });
    }

    function validateRoleCombination() {
        const selectedRoles = getSelectedRoleNames();

        const hasAdmin = selectedRoles.includes('Administrador');
        const hasRequester = selectedRoles.includes('Solicitante');

        const technicalRoles = [
            'Jefe de Proyecto',
            'Analista',
            'Desarrollador',
            'QA'
        ];

        const hasTechnicalRole = selectedRoles.some(function (role) {
            return technicalRoles.includes(role);
        });

        let message = '';

        if (hasAdmin && selectedRoles.length > 1) {
            message = 'Administrador debe ir solo. No se debe combinar con otros roles.';
        } else if (hasRequester && hasTechnicalRole) {
            message = 'Solicitante no debe combinarse con roles técnicos u operativos.';
        }

        if (message !== '') {
            warning.textContent = message;
            warning.classList.remove('d-none');
            saveButton.disabled = true;
            return false;
        }

        warning.classList.add('d-none');
        warning.textContent = '';
        saveButton.disabled = false;
        return true;
    }

    rolesSelect.addEventListener('change', validateRoleCombination);
    validateRoleCombination();
});
</script>