<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Controller;
use App\Models\Document;
use App\Models\Request;
use App\Models\Phase;
use App\Services\NotificationService;
use App\Services\AuditService;

class DocumentController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('documents.view');

        $doc = new Document();

        $filters = [
            'q' => $_GET['q'] ?? null,
            'phase_id' => $_GET['phase_id'] ?? null,
            'obligatorio' => $_GET['obligatorio'] ?? null,
            'activo' => $_GET['activo'] ?? null,
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) ($_GET['per_page'] ?? 15);
        if (!in_array($perPage, [10, 15, 25, 50], true)) {
            $perPage = 15;
        }

        $pagination = $doc->paginateTypes($filters, $page, $perPage);

        $this->view('documents/index', [
            'documentTypes' => $pagination['rows'],
            'pagination' => $pagination,
            'filters' => $filters,
            'catalogs' => [
                'phases' => $doc->allPhases(),
            ],
            'user' => Auth::user(),
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('documents.review');

        $doc = new Document();

        $this->view('documents/create', [
            'catalogs' => [
                'phases' => $doc->allPhases(),
            ],
            'user' => Auth::user(),
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('documents.review');
        verify_csrf();

        $nombre = trim($_POST['nombre'] ?? '');
        $phaseId = (int) ($_POST['phase_id'] ?? 0);
        $obligatorio = isset($_POST['obligatorio']) ? 1 : 0;
        $descripcion = trim($_POST['descripcion'] ?? '');
        $activo = isset($_POST['activo']) ? 1 : 0;

        if ($nombre === '' || $phaseId <= 0) {
            flash('error', 'Debe indicar nombre del documento y fase.');
            $this->redirect('/documents/create');
            return;
        }

        $doc = new Document();
        $typeId = $doc->createType([
            'nombre' => $nombre,
            'fase_id' => $phaseId,
            'obligatorio' => $obligatorio,
            'descripcion' => $descripcion ?: null,
            'activo' => $activo,
        ]);

        if (!$typeId) {
            flash('error', 'No fue posible crear el tipo documental.');
            $this->redirect('/documents/create');
            return;
        }

        $file = $_FILES['archivo_base'] ?? ($_FILES['archivo_referencia'] ?? null);
        if ($file && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if (!$doc->uploadTypeFile((int) $typeId, $file)) {
                flash('warning', 'El tipo documental fue creado, pero no fue posible subir el archivo asociado.');
                $this->redirect('/documents/edit?id=' . (int) $typeId);
                return;
            }
        }

        AuditService::businessEvent(
            'create',
            'documents',
            'document_type',
            $typeId,
            'Tipo documental creado: ' . $nombre,
            [],
            [
                'Nombre' => $nombre,
                'Fase ID' => $phaseId,
                'Obligatorio' => $obligatorio,
                'Activo' => $activo,
            ],
            'info'
        );

        flash('success', 'Tipo documental creado correctamente.');
        $this->redirect('/documents');
    }

    public function edit(): void
    {
        Auth::requirePermission('documents.review');

        $id = (int) ($_GET['id'] ?? 0);
        $doc = new Document();
        $type = $doc->findType($id);

        if (!$type) {
            flash('error', 'Tipo documental no encontrado.');
            $this->redirect('/documents');
            return;
        }

        $this->view('documents/create', [
            'documentType' => $type,
            'catalogs' => [
                'phases' => $doc->allPhases(),
            ],
            'user' => Auth::user(),
        ]);
    }

    public function update(): void
    {
        Auth::requirePermission('documents.review');
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $phaseId = (int) ($_POST['phase_id'] ?? 0);
        $obligatorio = isset($_POST['obligatorio']) ? 1 : 0;
        $descripcion = trim($_POST['descripcion'] ?? '');
        $activo = isset($_POST['activo']) ? 1 : 0;

        if ($id <= 0 || $nombre === '' || $phaseId <= 0) {
            flash('error', 'Debe completar los datos obligatorios.');
            $this->redirect('/documents');
            return;
        }

        $doc = new Document();
        $ok = $doc->updateType($id, [
            'nombre' => $nombre,
            'fase_id' => $phaseId,
            'obligatorio' => $obligatorio,
            'descripcion' => $descripcion ?: null,
            'activo' => $activo,
        ]);

        if ($ok && !empty($_POST['eliminar_archivo_referencia'])) {
            $doc->clearTypeFile($id);
        }

        $file = $_FILES['archivo_base'] ?? ($_FILES['archivo_referencia'] ?? null);
        if ($ok && $file && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if (!$doc->uploadTypeFile($id, $file)) {
                flash('warning', 'El tipo documental fue actualizado, pero no fue posible subir o reemplazar el archivo asociado.');
                $this->redirect('/documents/edit?id=' . $id);
                return;
            }
        }

        flash($ok ? 'success' : 'error', $ok ? 'Tipo documental actualizado correctamente.' : 'No fue posible actualizar el tipo documental.');
        $this->redirect('/documents');
    }

    public function toggle(): void
    {
        Auth::requirePermission('documents.review');
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', 'Tipo documental inválido.');
            $this->redirect('/documents');
            return;
        }

        $doc = new Document();
        $ok = $doc->toggleType($id);

        flash($ok ? 'success' : 'error', $ok ? 'Estado del tipo documental actualizado.' : 'No fue posible cambiar el estado.');
        $this->redirect('/documents');
    }

    public function typeFile(): void
    {
        Auth::requirePermission('documents.view');

        $id = (int) ($_GET['id'] ?? 0);
        $mode = $_GET['mode'] ?? (($_GET['disposition'] ?? '') === 'download' ? 'download' : 'view');

        $doc = new Document();
        $type = $doc->findType($id);

        $relativeFile = $type
            ? ($type['archivo_base_ruta'] ?? $type['archivo_referencia'] ?? null)
            : null;

        if (!$type || empty($relativeFile)) {
            flash('error', 'El documento no tiene archivo asociado.');
            $this->redirect('/documents');
            return;
        }

        $relative = ltrim((string) $relativeFile, '/');
        $base = realpath(__DIR__ . '/../../public');
        $path = realpath($base . '/' . $relative);

        if (!$base || !$path || strpos($path, $base) !== 0 || !is_file($path)) {
            flash('error', 'El archivo físico no existe o no está disponible.');
            $this->redirect('/documents');
            return;
        }

        $filename = ($type['archivo_base_nombre'] ?? null)
            ?: ($type['archivo_original'] ?? null)
            ?: basename($path);
        $mime = ($type['archivo_base_mime'] ?? null)
            ?: ($type['archivo_mime'] ?? null)
            ?: mime_content_type($path)
            ?: 'application/octet-stream';
        $disposition = $mode === 'download' ? 'attachment' : 'inline';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: ' . $disposition . '; filename="' . str_replace('"', '', $filename) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function download(): void
    {
        Auth::requirePermission('documents.view');

        $id = (int) ($_GET['id'] ?? 0);
        $doc = new Document();
        $item = $doc->findAttachment($id);

        if (!$item) {
            flash('error', 'Documento no encontrado.');
            $this->redirect('/documents');
            return;
        }

        $relative = ltrim((string) ($item['ruta_archivo'] ?? ''), '/');
        $base = realpath(__DIR__ . '/../../public');
        $path = realpath($base . '/' . $relative);

        if (!$base || !$path || strpos($path, $base) !== 0 || !is_file($path)) {
            flash('error', 'El archivo físico no existe o no está disponible.');
            $this->redirect('/documents');
            return;
        }

        $filename = $item['nombre_original'] ?: basename($path);
        $mime = $item['tipo_mime'] ?: 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . str_replace('"', '', $filename) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function inactivate(): void
    {
        Auth::requirePermission('documents.review');
        verify_csrf();

        $id = (int) ($_POST['document_id'] ?? 0);
        $returnTo = $_POST['return_to'] ?? '/documents';

        if (!$id) {
            flash('error', 'Documento inválido.');
            $this->redirect('/documents');
            return;
        }

        $doc = new Document();
        $ok = $doc->setInactive($id);

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Documento inactivado correctamente.' : 'No fue posible inactivar el documento.'
        );

        $this->redirect($this->safeReturnPath($returnTo));
    }

    public function reactivate(): void
    {
        Auth::requirePermission('documents.review');
        verify_csrf();

        $id = (int) ($_POST['document_id'] ?? 0);
        $returnTo = $_POST['return_to'] ?? '/documents';

        if (!$id) {
            flash('error', 'Documento inválido.');
            $this->redirect('/documents');
            return;
        }

        $doc = new Document();
        $ok = $doc->reactivate($id);

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Documento reactivado como pendiente.' : 'No fue posible reactivar el documento.'
        );

        $this->redirect($this->safeReturnPath($returnTo));
    }

    public function uploadPhaseDocument(): void
    {
        Auth::requirePermission('documents.upload.phase');
        verify_csrf();

        $doc = new Document();
        $requestModel = new Request();

        $requestId = (int) ($_POST['request_id'] ?? 0);
        $phaseId = (int) ($_POST['phase_id'] ?? 0);
        $documentTypeId = (int) ($_POST['document_type_id'] ?? 0);
        $file = $_FILES['archivo'] ?? null;
        $returnPath = '/requests/advance?id=' . $requestId;

        if (!$requestId || !$phaseId || !$documentTypeId || !$file) {
            flash('error', 'Datos incompletos para cargar documento.');
            $this->redirect($returnPath);
            return;
        }

        $request = $requestModel->find($requestId);

        if (!$request) {
            flash('error', 'Solicitud no encontrada.');
            $this->redirect('/requests');
            return;
        }

        $type = $doc->findTypeForPhase($documentTypeId, $phaseId);

        if (!$type) {
            flash('error', 'Tipo documental inválido para la fase seleccionada. Revise la configuración de documentos.');
            $this->redirect($returnPath);
            return;
        }

        $currentPhaseId = (int) ($request['phase_id'] ?? 0);
        $isCurrentPhase = $currentPhaseId === $phaseId;
        $isPastPhase = $phaseId < $currentPhaseId;
        $isOptional = (int) ($type['obligatorio'] ?? 0) === 0;

        if (!$isCurrentPhase && !($isPastPhase && $isOptional)) {
            flash('error', 'Solo se pueden cargar documentos de la fase actual u opcionales de fases anteriores.');
            $this->redirect($returnPath);
            return;
        }

        $ok = $doc->upload(
            $requestId,
            $phaseId,
            $documentTypeId,
            (int) Auth::user()['id'],
            $file
        );

        if (!$ok) {
            flash('error', 'Error al cargar el documento.');
            $this->redirect($returnPath);
            return;
        }

        AuditService::businessEvent(
            'upload',
            'documents',
            'request_attachment',
            $requestId,
            'Documento cargado en solicitud: ' . ($request['codigo'] ?? ''),
            [],
            [
                'Solicitud' => $request['codigo'] ?? '',
                'Título' => $request['titulo'] ?? '',
                'Documento' => $type['nombre'] ?? '',
                'Fase ID' => $phaseId,
                'Fase actual ID' => $currentPhaseId,
                'Documento opcional' => $isOptional ? 'Sí' : 'No',
                'Carga fase anterior' => ($isPastPhase && $isOptional) ? 'Sí' : 'No',
                'Archivo' => $file['name'] ?? '',
            ],
            'info'
        );

        (new NotificationService())->documentUploaded($request, $type['nombre'] ?? '');

        $advanced = false;

        if ($isCurrentPhase) {
            $advanced = $this->tryAutoAdvanceRequest($requestId, (int) Auth::user()['id'], false);

            if ($advanced) {
                $after = $requestModel->find($requestId);

                if ($after) {
                    (new NotificationService())->phaseChanged($request, $after);
                }
            }
        }

        if ($advanced) {
            flash('success', 'Documento cargado correctamente. La solicitud avanzó automáticamente a la siguiente fase.');
        } elseif ($isPastPhase && $isOptional) {
            flash('success', 'Documento opcional de fase anterior cargado correctamente.');
        } else {
            flash('success', 'Documento cargado correctamente.');
        }

        $this->redirect($returnPath);
    }

    public function review(): void
    {
        Auth::requirePermission('documents.review');
        verify_csrf();

        $doc = new Document();

        $documentId = (int) ($_POST['document_id'] ?? 0);
        $requestId = (int) ($_POST['request_id'] ?? 0);
        $decision = $_POST['decision'] ?? '';
        $observacion = trim($_POST['observacion_revision'] ?? '');
        $returnTo = $_POST['return_to'] ?? ('/requests/advance?id=' . $requestId);

        if (!$documentId || !$requestId || !in_array($decision, ['aprobado', 'rechazado'], true)) {
            flash('error', 'Datos inválidos para revisión.');
            $this->redirect($this->safeReturnPath($returnTo));
            return;
        }

        $ok = $doc->approve(
            $documentId,
            (int) Auth::user()['id'],
            $decision,
            $observacion
        );

        $advanced = false;
        $requestModel = new Request();
        $requestBeforeAdvance = $requestModel->find($requestId);

        if ($ok) {
            AuditService::businessEvent(
                $decision === 'aprobado' ? 'approve' : 'reject',
                'documents',
                'request_attachment',
                $documentId,
                'Documento ' . $decision . ' para solicitud ' . ($requestBeforeAdvance['codigo'] ?? ('#' . $requestId)),
                [],
                [
                    'Solicitud ID' => $requestId,
                    'Código solicitud' => $requestBeforeAdvance['codigo'] ?? '',
                    'Título solicitud' => $requestBeforeAdvance['titulo'] ?? '',
                    'Decisión' => $decision,
                    'Observación' => $observacion,
                ],
                $decision === 'rechazado' ? 'warning' : 'info'
            );

            if ($requestBeforeAdvance) {
                (new NotificationService())->documentReviewed($requestBeforeAdvance, $decision, $observacion);
            }
        }

        $completedFlow = false;
        $flashMessage = 'Documento revisado correctamente.';

        if ($ok && $decision === 'aprobado') {
            $advanced = $this->tryAutoAdvanceRequest($requestId, (int) Auth::user()['id'], true);

            if ($advanced && $requestBeforeAdvance) {
                $after = $requestModel->find($requestId);

                if ($after) {
                    $beforePhaseId = (int) ($requestBeforeAdvance['phase_id'] ?? 0);
                    $afterPhaseId = (int) ($after['phase_id'] ?? 0);
                    $afterStatus = mb_strtolower((string) ($after['estado'] ?? ''));
                    $afterIsFinal = (int) ($after['estado_es_final'] ?? 0) === 1;

                    $completedFlow = ($afterStatus === 'cerrada' || $afterIsFinal);

                    // Solo se notifica cambio de fase cuando efectivamente cambió la fase.
                    // En el cierre final cambia el estado a Cerrada, pero la fase sigue siendo Cierre.
                    if ($beforePhaseId !== $afterPhaseId) {
                        (new NotificationService())->phaseChanged($requestBeforeAdvance, $after);
                    }
                }
            }

            if ($completedFlow) {
                $codigo = trim((string) ($requestBeforeAdvance['codigo'] ?? ''));
                $flashMessage = 'Documento de cierre aprobado correctamente. ' .
                    ($codigo !== '' ? 'La solicitud ' . $codigo . ' ' : 'La solicitud ') .
                    'completó todo el flujo y quedó cerrada.';
            } elseif ($advanced) {
                $flashMessage = 'Documento revisado correctamente. La solicitud avanzó automáticamente a la siguiente fase.';
            }
        }

        flash(
            $ok ? 'success' : 'error',
            $ok ? $flashMessage : 'No fue posible revisar el documento.'
        );

        $this->redirect($this->safeReturnPath($returnTo));
    }

    public function changePhase(): void
    {
        Auth::requirePermission('requests.phase.change');
        verify_csrf();

        $requestId = (int) ($_POST['request_id'] ?? 0);
        $targetPhaseId = (int) ($_POST['target_phase_id'] ?? 0);
        $observation = trim($_POST['observacion_fase'] ?? '');

        if (!$requestId || !$targetPhaseId) {
            flash('error', 'Datos inválidos para cambiar de fase.');
            $this->redirect('/requests');
            return;
        }

        $requestModel = new Request();
        $documentModel = new Document();
        $phaseModel = new Phase();

        $request = $requestModel->find($requestId);

        if (!empty($request['motivo_bloqueo']) || mb_strtolower((string) ($request['estado'] ?? '')) === 'bloqueada') {
            flash('error', 'La solicitud está bloqueada. No se puede cambiar de fase hasta desbloquearla.');
            $this->redirect('/requests/advance?id=' . $requestId);
            return;
        }

        if (!$request) {
            flash('error', 'Solicitud no encontrada.');
            $this->redirect('/requests');
            return;
        }

        $currentPhaseId = (int) ($request['phase_id'] ?? 0);

        if ($currentPhaseId > 0) {
            $currentPhase = $phaseModel->find($currentPhaseId);
            $targetPhase = $phaseModel->find($targetPhaseId);

            if ($currentPhase && $targetPhase && (int) $targetPhase['orden'] > (int) $currentPhase['orden']) {
                if ((int) $currentPhaseId === 3 && empty($request['requiere_formalizacion'])) {
                    // Si no requiere formalización, no se valida esa fase.
                } else {
                    $validation = $documentModel->phaseCanBeApproved($requestId, $currentPhaseId);

                    if (!$validation['ok']) {
                        flash(
                            'error',
                            'No se puede avanzar de fase. Faltan documentos obligatorios aprobados en "' .
                            ($currentPhase['nombre'] ?? 'fase actual') . '": ' . implode(', ', $validation['missing'])
                        );
                        $this->redirect('/requests/advance?id=' . $requestId);
                        return;
                    }
                }
            }
        }

        $beforePhaseChange = $request;

        $ok = $requestModel->updatePhase(
            $requestId,
            $targetPhaseId,
            (int) Auth::user()['id'],
            $observation ?: null
        );

        if ($ok) {
            $progressMap = [
                1 => 0,
                2 => 15,
                3 => 30,
                4 => 40,
                5 => 70,
                6 => 85,
                7 => 95,
                8 => 100,
            ];

            $requestModel->updateProgress($requestId, $progressMap[$targetPhaseId] ?? 0);

            $afterPhaseChange = $requestModel->find($requestId);
            if ($afterPhaseChange) {
                AuditService::businessEvent(
                    'advance_phase',
                    'requests',
                    'request',
                    $requestId,
                    'Cambio de fase de solicitud.',
                    [
                        'Fase' => $beforePhaseChange['fase'] ?? '',
                        'Avance' => $beforePhaseChange['porcentaje_avance'] ?? '',
                    ],
                    [
                        'Fase' => $afterPhaseChange['fase'] ?? '',
                        'Avance' => $afterPhaseChange['porcentaje_avance'] ?? '',
                        'Observación' => $observation,
                    ],
                    'info'
                );
            }
            if ($afterPhaseChange) {
                (new NotificationService())->phaseChanged($beforePhaseChange, $afterPhaseChange);
            }
        }

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Fase actualizada correctamente.' : 'No fue posible actualizar la fase.'
        );

        $this->redirect('/requests/advance?id=' . $requestId);
    }

    private function tryAutoAdvanceRequest(int $requestId, int $userId, bool $requireApprovedDocuments = true): bool
    {
        $requestModel = new Request();
        $documentModel = new Document();

        $request = $requestModel->find($requestId);
        if (!$request) {
            return false;
        }

        if ($requestModel->stateGuardMessage($requestId, true) !== null) {
            return false;
        }

        $currentPhaseId = (int) ($request['phase_id'] ?? 0);
        if ($currentPhaseId <= 0) {
            return false;
        }

        // La fase 1 conserva el comportamiento histórico: al cargar el documento obligatorio avanza a Levantamiento.
        // Desde la fase 2 en adelante, el avance automático exige documentos obligatorios aprobados.
        //$validation = (!$requireApprovedDocuments && $currentPhaseId === 1)
        //    ? $documentModel->hasRequiredDocumentsLoaded($requestId, $currentPhaseId)
        //    : $documentModel->phaseCanBeApproved($requestId, $currentPhaseId);


        // Todas las fases, incluida Solicitud, exigen documentos obligatorios aprobados.
        $validation = $documentModel->phaseCanBeApproved($requestId, $currentPhaseId);

        if (!$validation['ok']) {
            return false;
        }

        $nextPhaseId = $this->resolveNextPhaseId($request, $currentPhaseId);
        if ($nextPhaseId === null || $nextPhaseId === $currentPhaseId) {
            // Última fase: no existe fase siguiente. Si sus documentos obligatorios están aprobados,
            // se marca el cierre final de la solicitud recién en este punto.
            return $documentModel->isLastPhase($currentPhaseId)
                ? $requestModel->closeAsCompleted($requestId, $userId)
                : false;
        }

        $progressMap = [
            1 => 0,
            2 => 15,
            3 => 30,
            4 => 40,
            5 => 70,
            6 => 85,
            7 => 95,
            8 => 100,
        ];

        $ok = $requestModel->updatePhase(
            $requestId,
            $nextPhaseId,
            $userId,
            'Avance automático por cumplimiento documental de la fase.'
        );

        if ($ok) {
            $requestModel->updateProgress($requestId, $progressMap[$nextPhaseId] ?? 0);
        }

        return $ok;
    }

    private function resolveNextPhaseId(array $request, int $currentPhaseId): ?int
    {
        $requiereFormalizacion = !empty($request['requiere_formalizacion']);

        // Regla especial:
        // Las solicitudes UX/UI y Frontend saltan desde Solicitud directamente a Desarrollo.
        //$documentModel = new \App\Models\Document();
        //$db = $documentModel->db;

        $db = Database::connect();

        $categoryStmt = $db->prepare(
            "SELECT pc.nombre
            FROM requests r
            LEFT JOIN project_types pt ON pt.id = r.tipo_id
            LEFT JOIN project_categories pc ON pc.id = pt.category_id
            WHERE r.id = :request_id
            LIMIT 1"
        );

        $categoryStmt->execute([
            'request_id' => (int) $request['id'],
        ]);

        $categoryName = trim((string) $categoryStmt->fetchColumn());

        if ($currentPhaseId === 1 && $categoryName === 'UX/UI y Frontend') {
            $developmentStmt = $db->prepare(
                "SELECT id
                FROM project_phases
                WHERE nombre = 'Desarrollo'
                AND activo = 1
                LIMIT 1"
            );

            $developmentStmt->execute();
            $developmentPhaseId = (int) $developmentStmt->fetchColumn();

            if ($developmentPhaseId > 0) {
                return $developmentPhaseId;
            }
        }

        $nextMap = [
            1 => 2,
            2 => $requiereFormalizacion ? 3 : 4,
            3 => 4,
            4 => 5,
            5 => 6,
            6 => 7,
            7 => 8,
        ];

        return $nextMap[$currentPhaseId] ?? null;
    }

    private function safeReturnPath(string $path): string
    {
        $path = trim($path);

        if ($path === '' || strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            return '/documents';
        }

        return $path[0] === '/' ? $path : '/documents';
    }
}
