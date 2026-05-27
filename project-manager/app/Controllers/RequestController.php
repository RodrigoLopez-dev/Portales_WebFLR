<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Request;
use App\Models\Document;
use App\Models\Phase;
use App\Models\ProjectResource;
use App\Services\NotificationService;
use App\Services\AuditService;

class RequestController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('requests.view');

        $model = new Request();

        $filters = [
            'q' => $_GET['q'] ?? null,
            'estado_id' => $_GET['estado_id'] ?? null,
            'category_id' => $_GET['category_id'] ?? null,
            'tipo_id' => $_GET['tipo_id'] ?? null,
            'prioridad_id' => $_GET['prioridad_id'] ?? null,
            'responsable_id' => $_GET['responsable_id'] ?? null,
            'phase_id' => $_GET['phase_id'] ?? null,
            'bloqueadas' => $_GET['bloqueadas'] ?? null,
            'atrasadas' => $_GET['atrasadas'] ?? null,
            'pendientes_doc' => $_GET['pendientes_doc'] ?? null,
            'rechazados_doc' => $_GET['rechazados_doc'] ?? null,
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) ($_GET['per_page'] ?? 10);

        if (!in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $pagination = $model->paginate($filters, $page, $perPage);

        $this->view('requests/index', [
            'requests' => $pagination['rows'],
            'pagination' => $pagination,
            'filters' => $filters,
            'catalogs' => $model->catalogs(),
            'user' => Auth::user(),
        ]);
    }

    public function export(): void
    {
        Auth::requirePermission('requests.view');

        $model = new Request();

        $filters = [
            'q' => $_GET['q'] ?? null,
            'estado_id' => $_GET['estado_id'] ?? null,
            'category_id' => $_GET['category_id'] ?? null,
            'tipo_id' => $_GET['tipo_id'] ?? null,
            'prioridad_id' => $_GET['prioridad_id'] ?? null,
            'responsable_id' => $_GET['responsable_id'] ?? null,
            'phase_id' => $_GET['phase_id'] ?? null,
            'bloqueadas' => $_GET['bloqueadas'] ?? null,
            'atrasadas' => $_GET['atrasadas'] ?? null,
            'pendientes_doc' => $_GET['pendientes_doc'] ?? null,
            'rechazados_doc' => $_GET['rechazados_doc'] ?? null,
        ];

        $rows = $model->exportRows($filters);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="solicitudes_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');

        // BOM para que Excel abra correctamente tildes y eñes.
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'Código',
            'Título',
            'Tipo',
            'Prioridad',
            'Estado',
            'Responsable',
            'Fase',
            'Avance %',
            'Horas reales',
            'Horas estimadas',
            'Fecha requerida',
            'Fecha fin estimada',
            'Docs pendientes',
            'Docs rechazados',
            'Días atraso',
            'Horas excedidas',
            'Motivo bloqueo',
        ], ';');

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['codigo'] ?? '',
                $r['titulo'] ?? '',
                $r['tipo'] ?? '',
                $r['prioridad'] ?? '',
                $r['estado'] ?? '',
                $r['responsable'] ?? '',
                $r['fase'] ?? '',
                $r['porcentaje_avance'] ?? 0,
                $r['esfuerzo_real_horas'] ?? 0,
                $r['esfuerzo_estimado_horas'] ?? 0,
                $r['fecha_requerida'] ?? '',
                $r['fecha_fin_estimada'] ?? '',
                $r['docs_pendientes'] ?? 0,
                $r['docs_rechazados'] ?? 0,
                $r['dias_atraso'] ?? 0,
                !empty($r['horas_excedidas']) ? 'Sí' : 'No',
                $r['motivo_bloqueo'] ?? '',
            ], ';');
        }

        fclose($out);
        exit;
    }

    public function kanban(): void
    {
        Auth::requirePermission('requests.view');

        $model = new Request();

        $filters = [
            'q' => $_GET['q'] ?? null,
            'tipo_id' => $_GET['tipo_id'] ?? null,
            'prioridad_id' => $_GET['prioridad_id'] ?? null,
            'responsable_id' => $_GET['responsable_id'] ?? null,
        ];

        $board = $model->kanbanData($filters);

        $this->view('requests/kanban', [
            'board' => $board,
            'filters' => $filters,
            'catalogs' => $model->catalogs(),
            'user' => Auth::user(),
        ]);
    }

    public function advance(): void
    {
        Auth::requirePermission('requests.view');

        $id = (int) ($_GET['id'] ?? 0);

        $model = new Request();
        $documentModel = new Document();
        $phaseModel = new Phase();

        $request = $model->find($id);

        if (!$request) {
            flash('error', 'Solicitud no encontrada.');
            $this->redirect('/requests');
            return;
        }

        $guardMessage = $model->stateGuardMessage($id, true);

        if ($guardMessage !== null) {
            flash('error', $guardMessage);
            $this->redirect('/requests');
            return;
        }

        $categoryId = isset($request['category_id']) ? (int) $request['category_id'] : null;

        $phases = $phaseModel->all();

        $documentsByPhase = [];
        foreach ($phases as $phase) {
            $documentsByPhase[$phase['id']] = $documentModel->requiredByPhase($id, (int) $phase['id']);
        }

        $currentPhaseId = (int) ($request['phase_id'] ?? 0);
        $phaseDocumentTypes = [];
        $phaseReferenceDocuments = [];
        $previousOptionalDocuments = [];

        if ($currentPhaseId > 0) {
            $phaseDocumentTypes = $documentModel->typesByPhase($currentPhaseId, true, $categoryId);
            $phaseReferenceDocuments = $documentModel->typeTemplatesByPhase($currentPhaseId, $categoryId);
            $previousOptionalDocuments = $documentModel->optionalTypeTemplatesBeforePhase($currentPhaseId, $categoryId);
        }

        $this->view('requests/show', [
            'request' => $request,
            'comments' => $model->comments($id),
            'history' => $model->history($id),
            'attachments' => $model->attachments($id),
            'catalogs' => $model->catalogs(),
            'phaseHistory' => $model->phaseHistory($id),
            'phases' => $phases,
            'documentsByPhase' => $documentsByPhase,
            'documentsUploaded' => $documentModel->groupedDocumentsByRequest($id),
            'phaseDocumentTypes' => $phaseDocumentTypes,
            'phaseReferenceDocuments' => $phaseReferenceDocuments,
            'previousOptionalDocuments' => $previousOptionalDocuments,
            'projectResources' => (new ProjectResource())->byRequest($id),
            'projectResourceTypes' => (new ProjectResource())->types(),
            'user' => Auth::user(),
        ]);
    }

    public function history(): void
    {
        Auth::requirePermission('requests.view');

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            flash('error', 'Solicitud inválida.');
            $this->redirect('/requests');
            return;
        }

        $model = new Request();
        $request = $model->find($id);

        if (!$request) {
            flash('error', 'Solicitud no encontrada.');
            $this->redirect('/requests');
            return;
        }

        $attachments = $model->attachments($id);

        $attachmentsGrouped = [];
        foreach ($attachments as $item) {
            $fase = $item['fase_nombre'] ?? 'Sin fase';
            if (!isset($attachmentsGrouped[$fase])) {
                $attachmentsGrouped[$fase] = [];
            }
            $attachmentsGrouped[$fase][] = $item;
        }

        $this->view('requests/view', [
            'request' => $request,
            'comments' => $model->comments($id),
            'history' => $model->history($id),
            'phaseHistory' => $model->phaseHistory($id),
            'attachments' => $attachments,
            'attachmentsGrouped' => $attachmentsGrouped,
            'user' => Auth::user(),
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('requests.create');

        $model = new Request();

        $this->view('requests/create', [
            'catalogs' => $model->catalogs(),
            'user' => Auth::user(),
        ]);
    }

    public function edit(): void
    {
        Auth::requirePermission('requests.edit');

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            flash('error', 'ID de solicitud inválido.');
            $this->redirect('/requests');
            return;
        }

        $model = new Request();
        $request = $model->find($id);

        if (!$request) {
            flash('error', 'Solicitud no encontrada.');
            $this->redirect('/requests');
            return;
        }

        $guardMessage = $model->stateGuardMessage($id, true);
        if ($guardMessage !== null) {
            flash('error', $guardMessage);
            $this->redirect('/requests');
            return;
        }

        $this->view('requests/form', [
            'catalogs' => $model->catalogs(),
            'request' => $request,
            'action' => '/requests/update',
            'user' => Auth::user(),
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('requests.create');
        verify_csrf();

        $model = new Request();
        $data = $_POST;

        $ok = $model->create($data, (int) Auth::user()['id']);

        if ($ok && $model->lastCreatedId()) {
            $request = $model->find((int) $model->lastCreatedId());

            AuditService::businessEvent(
                'create',
                'requests',
                'request',
                $model->lastCreatedId(),
                'Solicitud creada: ' . ($request['codigo'] ?? '') . ' - ' . ($request['titulo'] ?? ''),
                [],
                [
                    'Código' => $request['codigo'] ?? '',
                    'Título' => $request['titulo'] ?? '',
                    'Categoría' => $request['categoria'] ?? '',
                    'Tipo' => $request['tipo'] ?? '',
                    'Prioridad' => $request['prioridad'] ?? '',
                    'Responsable' => $request['responsable'] ?? '',
                ],
                'info'
            );

            if ($request) {
                (new NotificationService())->requestCreated($request);
            }
        }

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Solicitud creada correctamente.' : 'No fue posible crear la solicitud.'
        );

        $this->redirect('/requests');
    }

    public function update(): void
    {
        Auth::requirePermission('requests.edit');
        verify_csrf();

        $model = new Request();

        $id = (int) ($_POST['id'] ?? 0);
        $data = $_POST;
        $before = $model->find($id);

        if (!$before) {
            flash('error', 'Solicitud no encontrada.');
            $this->redirect('/requests');
            return;
        }

        $guardMessage = $model->stateGuardMessage($id, true);
        if ($guardMessage !== null) {
            flash('error', $guardMessage);
            $this->redirect('/requests');
            return;
        }

        $ok = $model->update($id, $data, (int) Auth::user()['id']);

        /* if ($ok && $before) {
            $after = $model->find($id);
            if ($after) {
                (new NotificationService())->requestUpdated($before, $after);
            }
        } */

        if ($ok && $before) {
            $after = $model->find($id);

            if ($after) {

                AuditService::logChanges(
                    'update',
                    'requests',
                    'request',
                    $id,
                    'Solicitud actualizada.',
                    $before,
                    $after,
                    [
                        'titulo' => 'Título',
                        'descripcion' => 'Descripción',
                        'categoria' => 'Categoría',
                        'tipo' => 'Tipo',
                        'prioridad' => 'Prioridad',
                        'estado' => 'Estado',
                        'fase' => 'Fase',
                        'responsable' => 'Responsable',
                        'fecha_requerida' => 'Fecha requerida',
                        'esfuerzo_estimado_horas' => 'Horas estimadas',
                        'esfuerzo_real_horas' => 'Horas reales',
                        'porcentaje_avance' => 'Avance',
                        'requiere_formalizacion' => 'Requiere formalización',
                        'motivo_bloqueo' => 'Motivo bloqueo',
                    ],
                    'info'
                );

                $notificationService = new NotificationService();

                $notificationService->requestUpdated($before, $after);

                if ((int) ($before['responsable_id'] ?? 0) !== (int) ($after['responsable_id'] ?? 0)) {
                    $notificationService->responsibleChanged($before, $after);
                }
            }
        }

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Solicitud actualizada.' : 'No fue posible actualizar.'
        );

        $this->redirect('/requests/advance?id=' . $id);
    }

    public function changeStatus(): void
    {
        Auth::requirePermission('requests.change_status');
        verify_csrf();

        $model = new Request();

        $id = (int) ($_POST['request_id'] ?? 0);
        $estadoId = (int) ($_POST['estado_id'] ?? 0);
        $motivo = $_POST['motivo_bloqueo'] ?? null;
        $before = $model->find($id);

        $guardMessage = $model->stateGuardMessage($id, true);
        if ($guardMessage !== null) {
            flash('error', $guardMessage);
            $this->redirect('/requests/advance?id=' . $id);
            return;
        }

        $ok = $model->changeStatus($id, $estadoId, (int) Auth::user()['id'], $motivo);

        if ($ok && $before) {
            $after = $model->find($id);
            if ($after) {
                (new NotificationService())->statusChanged($before, $after);
            }
        }

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Estado actualizado.' : 'No fue posible cambiar estado.'
        );

        $this->redirect('/requests/advance?id=' . $id);
    }

    public function block(): void
    {
        Auth::requirePermission('requests.change_status');
        verify_csrf();

        $model = new Request();

        $id = (int) ($_POST['request_id'] ?? 0);
        $motivo = trim((string) ($_POST['motivo_bloqueo'] ?? ''));
        $returnTo = $_POST['return_to'] ?? '/requests';

        if ($id <= 0) {
            flash('error', 'Solicitud inválida.');
            $this->redirect('/requests');
            return;
        }

        if ($motivo === '') {
            flash('error', 'Debe indicar el motivo del bloqueo.');
            $this->redirect($returnTo ?: '/requests');
            return;
        }

        $ok = $model->block($id, $motivo, (int) Auth::user()['id']);

        if ($ok) {
            AuditService::businessEvent(
                'block',
                'requests',
                'request',
                $id,
                'Solicitud bloqueada.',
                [],
                ['Motivo' => $motivo],
                'warning'
            );

            $request = $model->find($id);
            if ($request) {
                (new NotificationService())->requestBlocked($request, $motivo);
            }
        }

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Solicitud bloqueada correctamente.' : 'No fue posible bloquear la solicitud.'
        );

        $this->redirect($returnTo ?: '/requests');
    }

    public function unblock(): void
    {
        Auth::requirePermission('requests.change_status');
        verify_csrf();

        $model = new Request();

        $id = (int) ($_POST['request_id'] ?? 0);
        $returnTo = $_POST['return_to'] ?? '/requests';
        $motivoDesbloqueo = trim((string) ($_POST['motivo_desbloqueo'] ?? ''));

        if ($id <= 0) {
            flash('error', 'Solicitud inválida.');
            $this->redirect('/requests');
            return;
        }

        if ($motivoDesbloqueo === '') {
            flash('error', 'Debe indicar el motivo de desbloqueo.');
            $this->redirect($returnTo ?: '/requests');
            return;
        }

        $requestBefore = $model->find($id);

        $ok = $model->unblock($id, (int) Auth::user()['id']);

        if ($ok) {
            AuditService::businessEvent(
                'unblock',
                'requests',
                'request',
                $id,
                'Solicitud desbloqueada.',
                [
                    'Motivo bloqueo anterior' => $requestBefore['motivo_bloqueo'] ?? '',
                ],
                [
                    'Motivo desbloqueo' => $motivoDesbloqueo,
                    'Desbloqueado por' => Auth::user()['nombre'] ?? '',
                ],
                'info'
            );

            $request = $model->find($id);
            if ($request) {
                (new NotificationService())->requestUnblocked($request);
            }
        }

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Bloqueo resuelto correctamente.' : 'No fue posible desbloquear la solicitud.'
        );

        $this->redirect($returnTo ?: '/requests');
    }

    public function comment(): void
    {
        Auth::requirePermission('requests.comment');
        verify_csrf();

        $model = new Request();

        $id = (int) ($_POST['request_id'] ?? 0);
        $comentario = $_POST['comentario'] ?? '';
        $tipo = $_POST['tipo_comentario'] ?? 'interno';

        $ok = $model->addComment($id, (int) Auth::user()['id'], $comentario, $tipo);

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Comentario agregado.' : 'Error al comentar.'
        );

        $this->redirect('/requests/advance?id=' . $id);
    }
}
