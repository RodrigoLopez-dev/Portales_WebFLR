<?php
namespace App\Services;

use App\Core\Database;
use App\Models\Notification;
use Throwable;

class NotificationService
{
    private Notification $notifications;
    private array $mailConfig;
    private array $appConfig;

    public function __construct()
    {
        $this->notifications = new Notification();
        $this->mailConfig = require __DIR__ . '/../../config/mail.php';
        $this->appConfig = require __DIR__ . '/../../config/app.php';
    }

    public function notifyUsers(array $userIds, string $titulo, string $mensaje, bool $sendEmail = true): void
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));

        foreach ($userIds as $userId) {
            try {
                $this->notifications->create($userId, $titulo, $mensaje);

                if ($sendEmail && !empty($this->mailConfig['enabled'])) {
                    $user = $this->findUser($userId);
                    if ($user && !empty($user['email'])) {
                        $this->sendMail($user['email'], $user['nombre'] ?? '', $titulo, $mensaje);
                    }
                }
            } catch (Throwable $e) {
                error_log('NOTIFICATION_ERROR user_id=' . $userId . ' err=' . $e->getMessage());
            }
        }
    }


    public function requestCreated(array $request): void
    {
        $title = 'Nueva solicitud creada';

        $message =
            "Se ha creado una nueva solicitud en el sistema:\n\n" .
            "Detalle de la solicitud:\n" .
            "- Código: " . ($request['codigo'] ?? '') . "\n" .
            "- Título: " . ($request['titulo'] ?? '') . "\n" .
            "- Tipo: " . ($request['tipo_nombre'] ?? $request['tipo'] ?? 'No definido') . "\n" .
            "- Prioridad: " . ($request['prioridad'] ?? 'No definida') . "\n" .
            "- Estado inicial: " . ($request['estado'] ?? 'Sin estado') . "\n" .
            "- Fase actual: " . ($request['fase_nombre'] ?? 'Sin fase') . "\n" .
            "- Solicitante: " . ($request['solicitante_nombre'] ?? 'No informado') . "\n" .
            "- Responsable: " . ($request['responsable_nombre'] ?? 'No asignado') . "\n" .
            "- Fecha requerimiento: " . ($request['fecha_req'] ?? 'No definida') . "\n" .
            "- Horas estimadas: " . ($request['horas_estimadas'] ?? 'No definidas') . "\n\n" .
            "Acción requerida:\n" .
            "Revisar la solicitud creada, validar sus antecedentes y continuar con el flujo correspondiente.\n\n" .
            "Acceso directo:\n" .
            $this->requestAdvanceUrl($request['id'] ?? '');

        $recipients = array_merge(
            $this->usersByRoles(['Administrador', 'Jefe de Proyecto']),
            $this->requestParticipants($request)
        );

        $this->notifyUsers($recipients, $title, $message);
    }


    public function requestUpdated(array $before, array $after): void
    {
        $title = 'Solicitud actualizada';

        $message =
            "Se han actualizado datos de la solicitud:\n\n" .
            "Cambios detectados:\n" .
            "- Estado anterior: " . ($before['estado'] ?? 'Sin estado') . "\n" .
            "- Estado actual: " . ($after['estado'] ?? 'Sin estado') . "\n" .
            "- Fase anterior: " . ($before['fase_nombre'] ?? 'Sin fase') . "\n" .
            "- Fase actual: " . ($after['fase_nombre'] ?? 'Sin fase') . "\n\n" .
            "Detalle de la solicitud:\n" .
            "- Código: " . ($after['codigo'] ?? '') . "\n" .
            "- Título: " . ($after['titulo'] ?? '') . "\n" .
            "- Responsable: " . ($after['responsable_nombre'] ?? 'No asignado') . "\n" .
            "- Prioridad: " . ($after['prioridad'] ?? 'No definida') . "\n" .
            "- Fecha requerimiento: " . ($after['fecha_req'] ?? 'No definida') . "\n\n" .
            "Acción requerida:\n" .
            "Revisar los cambios realizados en la solicitud y continuar con el flujo correspondiente.\n\n" .
            "Acceso directo:\n" .
            $this->requestAdvanceUrl($after['id'] ?? '');
        ;

        $this->notifyUsers(
            $this->withAdministrators($this->requestParticipants($after)),
            $title,
            $message
        );
    }


    public function statusChanged(array $before, array $after): void
    {
        $title = 'Cambio de estado de solicitud';

        $message =
            "La solicitud ha cambiado de estado:\n\n" .
            "Cambio realizado:\n" .
            "- Estado anterior: " . ($before['estado'] ?? 'Sin estado') . "\n" .
            "- Estado actual: " . ($after['estado'] ?? 'Sin estado') . "\n\n" .
            "Detalle de la solicitud:\n" .
            "- Código: " . ($after['codigo'] ?? '') . "\n" .
            "- Título: " . ($after['titulo'] ?? '') . "\n" .
            "- Fase actual: " . ($after['fase_nombre'] ?? 'Sin fase') . "\n" .
            "- Responsable: " . ($after['responsable_nombre'] ?? 'No asignado') . "\n" .
            "- Prioridad: " . ($after['prioridad'] ?? 'No definida') . "\n" .
            "- Fecha requerimiento: " . ($after['fecha_req'] ?? 'No definida') . "\n\n" .
            "Acción requerida:\n" .
            "Revisar el nuevo estado de la solicitud y continuar con el flujo correspondiente.\n\n" .
            "Acceso directo:\n" .
            $this->requestAdvanceUrl($after['id'] ?? '');
        ;

        $this->notifyUsers(
            $this->withAdministrators($this->requestParticipants($after)),
            $title,
            $message
        );
    }


    public function phaseChanged(array $before, array $after): void
    {
        $title = 'Cambio de fase de solicitud';

        $message =
            "La solicitud ha cambiado de fase:\n\n" .
            "Fase anterior: " . ($before['fase_nombre'] ?? 'Sin fase') . "\n" .
            "Nueva fase: " . ($after['fase_nombre'] ?? 'Sin fase') . "\n\n" .
            "Detalle de la solicitud:\n" .
            "- Código: " . ($after['codigo'] ?? '') . "\n" .
            "- Título: " . ($after['titulo'] ?? '') . "\n" .
            "- Estado: " . ($after['estado'] ?? 'Sin estado') . "\n" .
            "- Responsable: " . ($after['responsable_nombre'] ?? 'No asignado') . "\n" .
            "- Prioridad: " . ($after['prioridad'] ?? 'No definida') . "\n" .
            "- Fecha compromiso: " . ($after['fecha_req'] ?? 'No definida') . "\n\n" .
            "Acción requerida:\n" .
            "Revisar la nueva fase y continuar con el flujo del proceso.\n\n" .
            "Acceso directo:\n" .
            $this->requestAdvanceUrl($after['id'] ?? '');
        ;

        $this->notifyUsers($this->withAdministrators($this->requestParticipants($after)), $title, $message);
    }


    public function requestBlocked(array $request, string $motivo): void
    {
        $title = 'Solicitud bloqueada';

        $message =
            "La solicitud ha sido bloqueada:\n\n" .
            "Motivo del bloqueo:\n" .
            ($motivo !== '' ? $motivo : 'No especificado') . "\n\n" .
            "Detalle de la solicitud:\n" .
            "- Código: " . ($request['codigo'] ?? '') . "\n" .
            "- Título: " . ($request['titulo'] ?? '') . "\n" .
            "- Estado: " . ($request['estado'] ?? 'Sin estado') . "\n" .
            "- Fase actual: " . ($request['fase_nombre'] ?? 'Sin fase') . "\n" .
            "- Responsable: " . ($request['responsable_nombre'] ?? 'No asignado') . "\n" .
            "- Prioridad: " . ($request['prioridad'] ?? 'No definida') . "\n" .
            "- Fecha requerimiento: " . ($request['fecha_req'] ?? 'No definida') . "\n\n" .
            "Impacto:\n" .
            "El flujo de la solicitud queda detenido hasta resolver el bloqueo.\n\n" .
            "Acción requerida:\n" .
            "Revisar el motivo del bloqueo y tomar las acciones necesarias para su resolución.\n\n" .
            "Acceso directo:\n" .
            $this->requestAdvanceUrl($request['id'] ?? '');

        $this->notifyUsers(
            $this->withAdministrators($this->requestParticipants($request)),
            $title,
            $message
        );
    }


    public function requestUnblocked(array $request): void
    {
        $title = 'Solicitud desbloqueada';

        $message =
            "La solicitud ha sido desbloqueada:\n\n" .
            "Detalle de la solicitud:\n" .
            "- Código: " . ($request['codigo'] ?? '') . "\n" .
            "- Título: " . ($request['titulo'] ?? '') . "\n" .
            "- Estado: " . ($request['estado'] ?? 'Sin estado') . "\n" .
            "- Fase actual: " . ($request['fase_nombre'] ?? 'Sin fase') . "\n" .
            "- Responsable: " . ($request['responsable_nombre'] ?? 'No asignado') . "\n" .
            "- Prioridad: " . ($request['prioridad'] ?? 'No definida') . "\n" .
            "- Fecha requerimiento: " . ($request['fecha_req'] ?? 'No definida') . "\n\n" .
            "Impacto:\n" .
            "El bloqueo fue resuelto y el flujo de la solicitud puede continuar.\n\n" .
            "Acción requerida:\n" .
            "Retomar la gestión de la solicitud y continuar con las actividades correspondientes.\n\n" .
            "Acceso directo:\n" .
            $this->requestAdvanceUrl($request['id'] ?? '');

        $this->notifyUsers(
            $this->withAdministrators($this->requestParticipants($request)),
            $title,
            $message
        );
    }


    public function documentUploaded(array $request, string $documentName = ''): void
    {
        $title = 'Documento cargado';

        $message =
            "Se ha cargado un documento asociado a la solicitud:\n\n" .
            "Detalle del documento:\n" .
            "- Documento: " . (trim($documentName) !== '' ? trim($documentName) : 'No informado') . "\n\n" .
            "Detalle de la solicitud:\n" .
            "- Código: " . ($request['codigo'] ?? '') . "\n" .
            "- Título: " . ($request['titulo'] ?? '') . "\n" .
            "- Estado: " . ($request['estado'] ?? 'Sin estado') . "\n" .
            "- Fase actual: " . ($request['fase_nombre'] ?? 'Sin fase') . "\n" .
            "- Responsable: " . ($request['responsable_nombre'] ?? 'No asignado') . "\n" .
            "- Prioridad: " . ($request['prioridad'] ?? 'No definida') . "\n" .
            "- Fecha requerimiento: " . ($request['fecha_req'] ?? 'No definida') . "\n\n" .
            "Acción requerida:\n" .
            "Revisar el documento cargado y aprobarlo o rechazarlo según corresponda.\n\n" .
            "Acceso directo:\n" .
            $this->documentsReviewUrl();

        $recipients = array_merge(
            $this->usersByRoles(['Administrador', 'Jefe de Proyecto', 'QA']),
            $this->requestParticipants($request)
        );

        $this->notifyUsers($recipients, $title, $message);
    }


    public function documentReviewed(array $request, string $decision, string $observacion = ''): void
    {
        $isApproved = $decision === 'aprobado';

        $title = $isApproved ? 'Documento aprobado' : 'Documento rechazado';

        $message =
            "Resultado de revisión documental:\n\n" .
            "Decisión: " . ($isApproved ? 'Aprobado' : 'Rechazado') . "\n\n" .
            "Detalle de la solicitud:\n" .
            "- Código: " . ($request['codigo'] ?? '') . "\n" .
            "- Título: " . ($request['titulo'] ?? '') . "\n" .
            "- Estado: " . ($request['estado'] ?? 'Sin estado') . "\n" .
            "- Fase actual: " . ($request['fase_nombre'] ?? 'Sin fase') . "\n" .
            "- Responsable: " . ($request['responsable_nombre'] ?? 'No asignado') . "\n" .
            "- Prioridad: " . ($request['prioridad'] ?? 'No definida') . "\n" .
            "- Fecha requerimiento: " . ($request['fecha_req'] ?? 'No definida') . "\n\n";

        if (trim($observacion) !== '') {
            $message .=
                "Observación de revisión:\n" .
                trim($observacion) . "\n\n";
        }

        $message .=
            "Acción requerida:\n" .
            ($isApproved
                ? "Continuar con el flujo del proceso según la fase actual.\n\n"
                : "Revisar la observación, corregir el documento y volver a cargarlo.\n\n"
            ) .
            "Acceso directo:\n" .
            $this->requestAdvanceUrl($request['id'] ?? '');

        $this->notifyUsers(
            $this->withAdministrators($this->requestParticipants($request)),
            $title,
            $message
        );
    }


    public function projectResourceAdded(array $request, string $resourceTitle = ''): void
    {
        $title = 'Nuevo material complementario';

        $message =
            "Se ha agregado material complementario al proyecto:\n\n" .
            "Detalle del material:\n" .
            "- Material: " . (trim($resourceTitle) !== '' ? trim($resourceTitle) : 'No informado') . "\n\n" .
            "Detalle de la solicitud:\n" .
            "- Código: " . ($request['codigo'] ?? '') . "\n" .
            "- Título: " . ($request['titulo'] ?? '') . "\n" .
            "- Estado: " . ($request['estado'] ?? 'Sin estado') . "\n" .
            "- Fase actual: " . ($request['fase_nombre'] ?? 'Sin fase') . "\n" .
            "- Responsable: " . ($request['responsable_nombre'] ?? 'No asignado') . "\n" .
            "- Prioridad: " . ($request['prioridad'] ?? 'No definida') . "\n" .
            "- Fecha requerimiento: " . ($request['fecha_req'] ?? 'No definida') . "\n\n" .
            "Acción requerida:\n" .
            "Revisar el material complementario agregado y considerarlo dentro del desarrollo del proyecto.\n\n" .
            "Acceso directo:\n" .
            $this->requestAdvanceUrl($request['id'] ?? '');

        $recipients = array_merge(
            $this->usersByRoles(['Administrador', 'Jefe de Proyecto']),
            $this->requestParticipants($request)
        );

        $this->notifyUsers($recipients, $title, $message);
    }


    public function projectResourceInactivated(array $request, array $resource, array $user = []): void
    {
        $title = 'Material complementario inhabilitado';

        $resourceTitle = trim((string) ($resource['title'] ?? ''));
        $fileName = (string) ($resource['original_name'] ?? '');
        $userName = (string) ($user['nombre'] ?? '');

        $message =
            "Se ha inhabilitado material complementario del proyecto:\n\n" .
            "Detalle del material:\n" .
            "- Material: " . ($resourceTitle !== '' ? $resourceTitle : 'No informado') . "\n" .
            "- Archivo: " . ($fileName !== '' ? $fileName : 'No informado') . "\n" .
            "- Inhabilitado por: " . ($userName !== '' ? $userName : 'No informado') . "\n\n" .
            "Detalle de la solicitud:\n" .
            "- Código: " . ($request['codigo'] ?? '') . "\n" .
            "- Título: " . ($request['titulo'] ?? '') . "\n" .
            "- Estado: " . ($request['estado'] ?? 'Sin estado') . "\n" .
            "- Fase actual: " . ($request['fase_nombre'] ?? 'Sin fase') . "\n" .
            "- Responsable: " . ($request['responsable_nombre'] ?? 'No asignado') . "\n" .
            "- Prioridad: " . ($request['prioridad'] ?? 'No definida') . "\n" .
            "- Fecha requerimiento: " . ($request['fecha_req'] ?? 'No definida') . "\n\n" .
            "Información importante:\n" .
            "El material fue inhabilitado y no debe considerarse como vigente dentro del proyecto.\n" .
            "Se mantiene disponible únicamente para efectos de historial y trazabilidad.\n\n" .
            "Acción requerida:\n" .
            "Revisar si es necesario reemplazar el material o actualizar la documentación del proyecto.\n\n" .
            "Acceso directo:\n" .
            $this->requestAdvanceUrl($request['id'] ?? '');

        $recipients = array_merge(
            $this->usersByRoles(['Administrador', 'Jefe de Proyecto']),
            $this->requestParticipants($request)
        );

        $this->notifyUsers($recipients, $title, $message);
    }


    private function requestParticipants(array $request): array
    {
        return [
            (int) ($request['solicitante_id'] ?? 0),
            (int) ($request['responsable_id'] ?? 0),
        ];
    }

    private function withAdministrators(array $userIds): array
    {
        return array_merge(
            $this->usersByRoles(['Administrador']),
            $userIds
        );
    }

    private function usersByRoles(array $roles): array
    {
        if (!$roles) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT u.id
             FROM users u
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.estado = 1 AND r.nombre IN ($placeholders)"
        );
        $stmt->execute($roles);

        return array_map('intval', array_column($stmt->fetchAll(), 'id'));
    }

    private function findUser(int $userId): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare('SELECT id, nombre, email FROM users WHERE id = :id AND estado = 1 LIMIT 1');
        $stmt->execute(['id' => $userId]);

        return $stmt->fetch() ?: null;
    }

    private function sendMail(string $to, string $name, string $subject, string $message): bool
    {
        require_once __DIR__ . '/../../lib/mail_flr.php';

        $html = '<p>Hola ' . htmlspecialchars($name ?: 'usuario', ENT_QUOTES, 'UTF-8') . ',</p>'
            . '<p>' . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . '</p>'
            . '<p>Este mensaje fue generado automáticamente por el Gestor de Proyectos.</p>';

        return flr_send_mail([
            'enabled' => $this->mailConfig['enabled'] ?? false,
            'smtp_host' => $this->mailConfig['smtp_host'] ?? null,
            'smtp_port' => $this->mailConfig['smtp_port'] ?? null,
            'smtp_user' => $this->mailConfig['smtp_user'] ?? null,
            'smtp_pass' => $this->mailConfig['smtp_pass'] ?? null,
            'from_email' => $this->mailConfig['from_email'] ?? null,
            'from_name' => $this->mailConfig['from_name'] ?? null,
            'reply_to' => $this->mailConfig['reply_to'] ?? null,
            'reply_name' => $this->mailConfig['reply_name'] ?? null,
            'bcc' => $this->mailConfig['bcc'] ?? null,
            'to' => $to,
            'subject' => $subject,
            'html' => $html,
            'alt' => $message,
        ]);
    }

    private function formatRequestMessage(array $request, string $detail): string
    {
        $code = $request['codigo'] ?? ('#' . ($request['id'] ?? ''));
        $title = $request['titulo'] ?? 'Sin título';

        return trim($detail . "\n\nSolicitud: " . $code . ' - ' . $title);
    }


    public function deadlineSoon(array $request): void
    {
        $title = 'Vencimiento próximo de solicitud';

        $message =
            "La solicitud está próxima a su fecha de compromiso:\n\n" .
            "- Código: " . ($request['codigo'] ?? '') . "\n" .
            "- Título: " . ($request['titulo'] ?? '') . "\n" .
            "- Fecha requerimiento: " . ($request['fecha_req'] ?? 'No definida') . "\n" .
            "- Responsable: " . ($request['responsable_nombre'] ?? 'No asignado') . "\n\n" .
            "Acción requerida:\n" .
            "Priorizar actividades para cumplir el plazo.\n\n" .
            "Acceso directo:\n" .
            $this->requestAdvanceUrl($request['id'] ?? '');

        $this->notifyUsers(
            $this->withAdministrators([$request['responsable_id'] ?? 0]),
            $title,
            $message
        );
    }

    public function deadlineBreached(array $request): void
    {
        $title = 'Plazo vencido de solicitud';

        $message =
            "La solicitud ha superado su fecha de compromiso:\n\n" .
            "- Código: " . ($request['codigo'] ?? '') . "\n" .
            "- Título: " . ($request['titulo'] ?? '') . "\n" .
            "- Fecha requerimiento: " . ($request['fecha_req'] ?? 'No definida') . "\n\n" .
            "Impacto:\n" .
            "Riesgo de incumplimiento del proyecto.\n\n" .
            "Acción requerida:\n" .
            "Replanificar y/o justificar el atraso.\n\n" .
            "Acceso directo:\n" .
            $this->requestAdvanceUrl($request['id'] ?? '');

        $recipients = array_merge(
            $this->usersByRoles(['Administrador', 'Jefe de Proyecto']),
            [$request['responsable_id'] ?? 0]
        );

        $this->notifyUsers($recipients, $title, $message);
    }

    public function responsibleChanged(array $before, array $after): void
    {
        $title = 'Cambio de responsable de solicitud';

        $message =
            "Se ha reasignado la solicitud:\n\n" .
            "- Código: " . ($after['codigo'] ?? '') . "\n" .
            "- Título: " . ($after['titulo'] ?? '') . "\n" .
            "- Responsable anterior: " . ($before['responsable_nombre'] ?? 'No asignado') . "\n" .
            "- Nuevo responsable: " . ($after['responsable_nombre'] ?? 'No asignado') . "\n\n" .
            "Acción requerida:\n" .
            "El nuevo responsable debe revisar y continuar la gestión.\n\n" .
            "Acceso directo:\n" .
            $this->requestAdvanceUrl($after['id'] ?? '');
        ;

        $recipients = array_unique(array_filter([
            $after['responsable_id'] ?? 0,
            $before['responsable_id'] ?? 0,
        ]));

        $this->notifyUsers(
            $this->withAdministrators($recipients),
            $title,
            $message
        );
    }

    private function requestAdvanceUrl($requestId): string
    {
        $appUrl = rtrim((string) ($this->appConfig['app_url'] ?? ''), '/');
        return $appUrl . '/requests/advance?id=' . urlencode((string) $requestId);
    }

    private function documentsReviewUrl(): string
    {
        $appUrl = rtrim((string) ($this->appConfig['app_url'] ?? ''), '/');
        return $appUrl . '/documents/review';
    }
}
