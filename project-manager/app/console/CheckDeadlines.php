<?php

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Database;
use App\Services\NotificationService;

$db = Database::connect();
$notification = new NotificationService();

/*
|--------------------------------------------------------------------------
| VENCIMIENTO PRÓXIMO
|--------------------------------------------------------------------------
| Envía una sola vez cuando la solicitud vence dentro de los próximos 2 días.
*/
$stmt = $db->query("
    SELECT
        r.id,
        r.codigo,
        r.titulo,
        r.solicitante_id,
        r.responsable_id,
        r.fecha_requerida AS fecha_req,
        s.nombre AS estado,
        p.nombre AS prioridad,
        ph.nombre AS fase_nombre,
        u.nombre AS responsable_nombre
    FROM requests r
    INNER JOIN statuses s ON s.id = r.estado_id
    INNER JOIN priorities p ON p.id = r.prioridad_id
    LEFT JOIN project_phases ph ON ph.id = r.phase_id
    LEFT JOIN users u ON u.id = r.responsable_id
    WHERE s.es_final = 0
      AND r.fecha_requerida IS NOT NULL
      AND r.fecha_requerida BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 2 DAY)
      AND r.notified_deadline_soon = 0
");

$soon = $stmt->fetchAll();

foreach ($soon as $req) {
    $notification->deadlineSoon($req);

    $update = $db->prepare("
        UPDATE requests
        SET notified_deadline_soon = 1
        WHERE id = :id
    ");

    $update->execute([
        'id' => $req['id'],
    ]);
}

/*
|--------------------------------------------------------------------------
| PLAZO VENCIDO
|--------------------------------------------------------------------------
| Envía una sola vez cuando la fecha requerida ya fue superada.
*/
$stmt = $db->query("
    SELECT
        r.id,
        r.codigo,
        r.titulo,
        r.solicitante_id,
        r.responsable_id,
        r.fecha_requerida AS fecha_req,
        s.nombre AS estado,
        p.nombre AS prioridad,
        ph.nombre AS fase_nombre,
        u.nombre AS responsable_nombre
    FROM requests r
    INNER JOIN statuses s ON s.id = r.estado_id
    INNER JOIN priorities p ON p.id = r.prioridad_id
    LEFT JOIN project_phases ph ON ph.id = r.phase_id
    LEFT JOIN users u ON u.id = r.responsable_id
    WHERE s.es_final = 0
      AND r.fecha_requerida IS NOT NULL
      AND r.fecha_requerida < CURDATE()
      AND r.notified_deadline_breached = 0
");

$expired = $stmt->fetchAll();

foreach ($expired as $req) {
    $notification->deadlineBreached($req);

    $update = $db->prepare("
        UPDATE requests
        SET notified_deadline_breached = 1
        WHERE id = :id
    ");

    $update->execute([
        'id' => $req['id'],
    ]);
}