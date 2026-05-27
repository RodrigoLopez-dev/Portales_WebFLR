<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;
use App\Services\AuditService;

class UserController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('users.view');

        $model = new User();

        $filters = [
            'q' => $_GET['q'] ?? null,
            'rol_id' => $_GET['rol_id'] ?? null,
            'estado' => $_GET['estado'] ?? null,
            'access' => $_GET['access'] ?? null,
        ];

        $this->view('users/index', [
            'users' => $model->findAllWithRoles($filters),
            'roles' => $model->roles(),
            'filters' => $filters,
            'user' => Auth::user(),
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('users.create');

        $model = new User();

        $this->view('users/create', [
            'roles' => $model->roles(),
            'user' => Auth::user(),
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('users.create');
        verify_csrf();

        $model = new User();

        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $roleIds = array_map('intval', $_POST['role_ids'] ?? []);

        if (empty($roleIds)) {
            flash('error', 'Debe seleccionar al menos un rol.');
            $this->redirect('/users/create');
            return;
        }


        $rolesCatalog = $model->roles();
        $roleError = $this->validateRoleCombination($roleIds, $rolesCatalog);

        if ($roleError !== null) {
            flash('error', $roleError);
            $this->redirect('/users/create');
            return;
        }


        $rolId = $roleIds[0]; // rol principal temporal para compatibilidad con users.rol_id


        $estado = isset($_POST['estado']) ? 1 : 0;

        if ($nombre === '' || $email === '' || $password === '' || $rolId <= 0) {
            flash('error', 'Todos los campos obligatorios deben completarse.');
            $this->redirect('/users/create');
            return;
        }

        if ($model->existsByEmail($email)) {
            flash('error', 'Ya existe un usuario con ese correo.');
            $this->redirect('/users/create');
            return;
        }

        $ok = $model->createUser([
            'nombre' => $nombre,
            'email' => $email,
            'password' => $password,
            'rol_id' => $rolId,
            'estado' => $estado,
        ]);


        if ($ok) {
            $userId = (int) $model->lastCreatedId();
            $model->syncRoles($userId, $roleIds);
        }

        if ($ok) {
            AuditService::businessEvent(
                'create',
                'users',
                'user',
                null,
                'Usuario creado: ' . $nombre,
                [],
                [
                    'Nombre' => $nombre,
                    'Email' => $email,
                    'Roles ID' => implode(', ', $roleIds),
                    'Estado' => $estado ? 'Activo' : 'Inactivo',
                ],
                'info'
            );
        }

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Usuario creado correctamente.' : 'No fue posible crear el usuario.'
        );

        $this->redirect('/users');
    }

    public function edit(): void
    {
        Auth::requirePermission('users.edit');

        $id = (int) ($_GET['id'] ?? 0);

        $model = new User();
        $userToEdit = $model->find($id);

        if (!$userToEdit) {
            flash('error', 'Usuario no encontrado.');
            $this->redirect('/users');
            return;
        }

        $this->view('users/edit', [
            'editUser' => $userToEdit,
            'roles' => $model->roles(),
            'userRoles' => $model->rolesByUser($id),
            'user' => Auth::user(),
        ]);
    }

    public function update(): void
    {
        Auth::requirePermission('users.edit');
        verify_csrf();

        $model = new User();

        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $roleIds = array_map('intval', $_POST['role_ids'] ?? []);

        if (empty($roleIds)) {
            flash('error', 'Debe seleccionar al menos un rol.');
            $this->redirect('/users/edit?id=' . $id);
            return;
        }

        $rolesCatalog = $model->roles();
        $roleError = $this->validateRoleCombination($roleIds, $rolesCatalog);

        if ($roleError !== null) {
            flash('error', $roleError);
            $this->redirect('/users/edit?id=' . $id);
            return;
        }

        $rolId = $roleIds[0]; // rol principal temporal para compatibilidad con users.rol_id


        $estado = isset($_POST['estado']) ? 1 : 0;

        if ($id <= 0 || $nombre === '' || $email === '' || $rolId <= 0) {
            flash('error', 'Debe completar los campos obligatorios.');
            $this->redirect('/users');
            return;
        }

        $currentUser = Auth::user();
        if ((int) ($currentUser['id'] ?? 0) === $id && $estado !== 1) {
            flash('error', 'No puedes desactivar tu propio usuario.');
            $this->redirect('/users/edit?id=' . $id);
            return;
        }

        if ($model->existsByEmailForAnotherUser($email, $id)) {
            flash('error', 'Ya existe otro usuario con ese correo.');
            $this->redirect('/users/edit?id=' . $id);
            return;
        }

        $before = $model->find($id);

        $ok = $model->updateUser([
            'id' => $id,
            'nombre' => $nombre,
            'email' => $email,
            'password' => $password,
            'rol_id' => $rolId,
            'estado' => $estado,
        ]);

        if ($ok) {
            $model->syncRoles($id, $roleIds);

            if ($ok) {
                AuditService::businessEvent(
                    'update_roles',
                    'users',
                    'user',
                    $id,
                    'Roles de usuario actualizados: ' . $nombre,
                    [],
                    [
                        'Roles ID' => implode(', ', $roleIds),
                    ],
                    'info'
                );
            }
        }

        if ($ok) {
            $after = $model->find($id);

            AuditService::logChanges(
                'update',
                'users',
                'user',
                $id,
                'Usuario actualizado: ' . $nombre . '.',
                $before ?? [],
                $after ?? [],
                [
                    'nombre' => 'Nombre',
                    'email' => 'Email',
                    'rol_id' => 'Rol ID',
                    'estado' => 'Estado',
                ],
                'info'
            );

            if ($password !== '') {
                AuditService::businessEvent(
                    'password_update',
                    'users',
                    'user',
                    $id,
                    'Contraseña de usuario actualizada: ' . $nombre,
                    [],
                    ['Contraseña' => 'Actualizada'],
                    'warning'
                );
            }
        }

        if ($ok && (int) ($currentUser['id'] ?? 0) === $id) {
            Auth::refreshUser($id);
        }

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Usuario actualizado correctamente.' : 'No fue posible actualizar el usuario.'
        );

        $this->redirect('/users');
    }

    public function roles(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT role_id
            FROM user_roles
            WHERE user_id = :user_id
        ");

        $stmt->execute([
            'user_id' => $userId
        ]);

        return $stmt->fetchAll();
    }



    public function toggle(): void
    {
        Auth::requirePermission('users.toggle');
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $returnTo = (string) ($_POST['return_to'] ?? '/users');

        $model = new User();

        if ($id <= 0) {
            flash('error', 'Usuario inválido.');
            $this->redirectSafe($returnTo);
            return;
        }

        $currentUser = Auth::user();
        if ((int) ($currentUser['id'] ?? 0) === $id) {
            flash('error', 'No puedes desactivar tu propio usuario.');
            $this->redirectSafe($returnTo);
            return;
        }

        $ok = $model->toggleStatus($id);

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Estado del usuario actualizado.' : 'No fue posible cambiar el estado.'
        );

        $this->redirectSafe($returnTo);
    }

    public function unlinkGoogle(): void
    {
        Auth::requirePermission('users.edit');
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $returnTo = (string) ($_POST['return_to'] ?? '/users');

        if ($id <= 0) {
            flash('error', 'Usuario inválido.');
            $this->redirectSafe($returnTo);
            return;
        }

        $currentUser = Auth::user();
        if ((int) ($currentUser['id'] ?? 0) === $id) {
            flash('error', 'Por seguridad, no puedes desvincular Google de tu propia cuenta desde este listado.');
            $this->redirectSafe($returnTo);
            return;
        }

        $model = new User();
        $ok = $model->unlinkGoogle($id);

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Cuenta Google desvinculada correctamente.' : 'No fue posible desvincular la cuenta Google.'
        );

        $this->redirectSafe($returnTo);
    }

    public function export(): void
    {
        Auth::requirePermission('users.view');

        $model = new User();

        $filters = [
            'q' => $_GET['q'] ?? null,
            'rol_id' => $_GET['rol_id'] ?? null,
            'estado' => $_GET['estado'] ?? null,
            'access' => $_GET['access'] ?? null,
        ];

        $rows = $model->findAllWithRoles($filters);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="usuarios_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'Nombre',
            'Email',
            'Roles',
            'Estado',
            'Acceso',
            'Google vinculado',
        ], ';');

        foreach ($rows as $row) {
            $hasGoogle = !empty($row['google_id']);
            $hasLocal = !empty($row['password_hash']);

            if ($hasGoogle && $hasLocal) {
                $access = 'Google + Local';
            } elseif ($hasGoogle) {
                $access = 'Google';
            } else {
                $access = 'Local';
            }

            fputcsv($out, [
                $row['nombre'] ?? '',
                $row['email'] ?? '',
                $row['roles'] ?? $row['rol'] ?? '',
                ((int) ($row['estado'] ?? 0) === 1) ? 'Activo' : 'Inactivo',
                $access,
                $hasGoogle ? 'Sí' : 'No',
            ], ';');
        }

        fclose($out);
        exit;
    }

    private function redirectSafe(string $path): void
    {
        $path = trim($path);

        if ($path === '' || strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            $path = '/users';
        }

        if (strpos($path, '/') !== 0) {
            $path = '/users';
        }

        $this->redirect($path);
    }

    private function validateRoleCombination(array $roleIds, array $rolesCatalog): ?string
    {
        $roleIds = array_unique(array_map('intval', $roleIds));

        $roleNames = [];

        foreach ($rolesCatalog as $role) {
            if (in_array((int) $role['id'], $roleIds, true)) {
                $roleNames[] = $role['nombre'];
            }
        }

        $hasAdmin = in_array('Administrador', $roleNames, true);
        $hasRequester = in_array('Solicitante', $roleNames, true);

        $technicalRoles = [
            'Jefe de Proyecto',
            'Analista',
            'Desarrollador',
            'QA',
        ];

        $hasTechnicalRole = count(array_intersect($technicalRoles, $roleNames)) > 0;

        if ($hasAdmin && count($roleNames) > 1) {
            return 'El rol Administrador debe asignarse solo, sin otros roles adicionales.';
        }

        if ($hasRequester && $hasTechnicalRole) {
            return 'El rol Solicitante no debe combinarse con roles técnicos u operativos.';
        }

        return null;
    }


}
