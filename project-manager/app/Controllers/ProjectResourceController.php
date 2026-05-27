<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ProjectResource;
use App\Models\Request;
use App\Services\NotificationService;
use App\Services\AuditService;

class ProjectResourceController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('resources.view');

        $requestId = (int)($_GET['request_id'] ?? 0);
        $requestModel = new Request();
        $resourceModel = new ProjectResource();

        $request = $requestModel->find($requestId);
        if (!$request) {
            flash('error', 'Solicitud no encontrada.');
            $this->redirect('/requests');
            return;
        }

        $this->view('resources/index', [
            'request' => $request,
            'resources' => $resourceModel->byRequest($requestId, true),
            'resourceTypes' => $resourceModel->types(),
            'user' => Auth::user(),
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('resources.create');

        $requestId = (int)($_GET['request_id'] ?? 0);
        $request = (new Request())->find($requestId);
        if (!$request) {
            flash('error', 'Solicitud no encontrada.');
            $this->redirect('/requests');
            return;
        }

        $resourceModel = new ProjectResource();
        $this->view('resources/create', [
            'request' => $request,
            'resourceTypes' => $resourceModel->types(),
            'user' => Auth::user(),
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('resources.create');
        verify_csrf();

        $requestId = (int)($_POST['request_id'] ?? 0);
        $user = Auth::user();

        $requestModel = new Request();
        $request = $requestModel->find($requestId);

        if (!$request || empty($user['id'])) {
            flash('error', 'No fue posible asociar el material al proyecto.');
            $this->redirect('/requests');
            return;
        }

        $resourceModel = new ProjectResource();

        $ok = $resourceModel->create([
            'request_id' => $requestId,
            'resource_type' => $_POST['resource_type'] ?? 'otro',
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'external_url' => $_POST['external_url'] ?? '',
            'is_public' => !empty($_POST['is_public']) ? 1 : 0,
            'uploaded_by' => (int)$user['id'],
        ], $_FILES['resource_file'] ?? null);

        if ($ok) {
            try {
                AuditService::businessEvent(
                    'create',
                    'resources',
                    'project_resources',
                    $requestId,
                    'Material complementario agregado: ' . trim((string)($_POST['title'] ?? '')),
                    [],
                    [
                        'Solicitud' => $request['codigo'] ?? '',
                        'Título solicitud' => $request['titulo'] ?? '',
                        'Tipo material' => $_POST['resource_type'] ?? 'otro',
                        'Título material' => trim((string)($_POST['title'] ?? '')),
                        'Descripción' => trim((string)($_POST['description'] ?? '')),
                        'Enlace externo' => trim((string)($_POST['external_url'] ?? '')),
                        'Archivo' => $_FILES['resource_file']['name'] ?? '',
                        'Visible público' => !empty($_POST['is_public']) ? 'Sí' : 'No',
                    ],
                    'info'
                );
            } catch (\Throwable $e) {
                error_log('PROJECT_RESOURCE_AUDIT_ERROR ' . $e->getMessage());
            }

            try {
                (new NotificationService())->projectResourceAdded($request, (string)($_POST['title'] ?? ''));
            } catch (\Throwable $e) {
                error_log('PROJECT_RESOURCE_NOTIFICATION_ERROR ' . $e->getMessage());
            }

            flash('success', 'Material complementario agregado correctamente.');
        } else {
            flash('error', 'No se pudo agregar el material. Revisa que tenga título y un archivo o enlace válido.');
        }

        $this->redirect('/resources?request_id=' . $requestId);
    }

    public function edit(): void
    {
        Auth::requirePermission('resources.edit');

        $id = (int)($_GET['id'] ?? 0);
        $resourceModel = new ProjectResource();
        $resource = $resourceModel->find($id);

        if (!$resource) {
            flash('error', 'Material no encontrado.');
            $this->redirect('/requests');
            return;
        }

        $request = (new Request())->find((int)$resource['request_id']);

        $this->view('resources/edit', [
            'request' => $request,
            'resource' => $resource,
            'resourceTypes' => $resourceModel->types(),
            'user' => Auth::user(),
        ]);
    }

    public function update(): void
    {
        Auth::requirePermission('resources.edit');
        verify_csrf();

        $id = (int)($_POST['id'] ?? 0);
        $resourceModel = new ProjectResource();
        $resource = $resourceModel->find($id);

        if (!$resource) {
            flash('error', 'Material no encontrado.');
            $this->redirect('/requests');
            return;
        }

        $ok = $resourceModel->update($id, [
            'resource_type' => $_POST['resource_type'] ?? 'otro',
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'external_url' => $_POST['external_url'] ?? '',
            'is_public' => $_POST['is_public'] ?? 0,
        ]);

        if ($ok) {
            $after = $resourceModel->find($id);

            try {
                AuditService::logChanges(
                    'update',
                    'resources',
                    'project_resources',
                    $id,
                    'Material complementario actualizado: ' . trim((string)($_POST['title'] ?? '')),
                    $resource,
                    $after ?? [],
                    [
                        'resource_type' => 'Tipo material',
                        'title' => 'Título',
                        'description' => 'Descripción',
                        'external_url' => 'Enlace externo',
                        'is_public' => 'Visible público',
                    ],
                    'info'
                );
            } catch (\Throwable $e) {
                error_log('PROJECT_RESOURCE_AUDIT_UPDATE_ERROR ' . $e->getMessage());
            }
        }

        flash($ok ? 'success' : 'error', $ok ? 'Material actualizado correctamente.' : 'No se pudo actualizar el material.');
        $this->redirect('/resources?request_id=' . (int)$resource['request_id']);
    }

    public function inactivate(): void
    {
        Auth::requirePermission('resources.delete');
        verify_csrf();

        $id = (int)($_POST['id'] ?? 0);
        $user = Auth::user();
        $resourceModel = new ProjectResource();
        $resource = $resourceModel->find($id);

        if (!$resource) {
            flash('error', 'Material no encontrado.');
            $this->redirect('/requests');
            return;
        }

        $requestId = (int)$resource['request_id'];
        $request = (new Request())->find($requestId);
        $ok = $resourceModel->inactivate($id, (int)($user['id'] ?? 0));

        if ($ok) {
            try {
                AuditService::businessEvent(
                    'inactivate',
                    'resources',
                    'project_resources',
                    $id,
                    'Material complementario inhabilitado: ' . (string)($resource['title'] ?? ''),
                    ['is_active' => 1],
                    ['is_active' => 0, 'inactivated_by' => (int)($user['id'] ?? 0)],
                    'warning'
                );
            } catch (\Throwable $e) {
                error_log('PROJECT_RESOURCE_AUDIT_ERROR ' . $e->getMessage());
            }

            try {
                if ($request) {
                    (new NotificationService())->projectResourceInactivated($request, $resource, $user ?? []);
                }
            } catch (\Throwable $e) {
                error_log('PROJECT_RESOURCE_INACTIVATE_NOTIFICATION_ERROR ' . $e->getMessage());
            }
        }

        flash($ok ? 'success' : 'error', $ok ? 'Material inhabilitado correctamente. El archivo se mantiene para historial y auditoría.' : 'No se pudo inhabilitar el material.');
        $this->redirect('/resources?request_id=' . $requestId);
    }

    public function delete(): void
    {
        // Mantiene compatibilidad con rutas o formularios antiguos, sin eliminar físicamente el material.
        $this->inactivate();
    }

    public function file(): void
    {
        Auth::requirePermission('resources.view');

        $id = (int)($_GET['id'] ?? 0);
        $resource = (new ProjectResource())->find($id);

        if (!$resource || empty($resource['file_path'])) {
            http_response_code(404);
            die('Archivo no encontrado.');
        }

        $absolutePath = __DIR__ . '/../../public/' . ltrim((string)$resource['file_path'], '/');
        if (!is_file($absolutePath)) {
            http_response_code(404);
            die('Archivo no encontrado.');
        }

        $mode = $_GET['mode'] ?? 'view';
        $fileName = $resource['original_name'] ?: basename($absolutePath);
        $mime = $resource['mime_type'] ?: 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($absolutePath));
        header('Content-Disposition: ' . ($mode === 'download' ? 'attachment' : 'inline') . '; filename="' . basename($fileName) . '"');
        readfile($absolutePath);
        exit;
    }
}
