<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Plan;
use App\Models\Milestone;
use App\Models\Task;
use App\Services\AuditService;

class PlanningController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('planning.view');

        $model = new Plan();

        $filters = [
            'q' => $_GET['q'] ?? null,
            'phase_id' => $_GET['phase_id'] ?? null,
            'estado' => $_GET['estado'] ?? null,
            'responsable_id' => $_GET['responsable_id'] ?? null,
            'atrasados' => $_GET['atrasados'] ?? null,
            'sin_tareas' => $_GET['sin_tareas'] ?? null,
        ];

        $plans = $model->all($filters);

        $this->view('planning/index', [
            'plans' => $plans,
            'summary' => $model->summary($plans),
            'filters' => $filters,
            'phases' => $model->phases(),
            'users' => $model->users(),
            'user' => Auth::user(),
        ]);
    }

    public function export(): void
    {
        Auth::requirePermission('planning.view');

        $model = new Plan();

        $filters = [
            'q' => $_GET['q'] ?? null,
            'phase_id' => $_GET['phase_id'] ?? null,
            'estado' => $_GET['estado'] ?? null,
            'responsable_id' => $_GET['responsable_id'] ?? null,
            'atrasados' => $_GET['atrasados'] ?? null,
            'sin_tareas' => $_GET['sin_tareas'] ?? null,
        ];

        $rows = $model->all($filters);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="planificacion_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'Proyecto',
            'Nombre plan',
            'Fase actual',
            'Inicio plan',
            'Fin plan',
            'Estado',
            'Avance %',
            'Responsable solicitud',
            'Tareas',
            'Tareas completadas',
            'Tareas pendientes',
            'Tareas atrasadas',
            'Hitos',
            'Hitos pendientes',
            'Hitos atrasados',
            'Horas estimadas',
            'Horas reales',
            'Días atraso plan',
        ], ';');

        foreach ($rows as $r) {
            fputcsv($out, [
                ($r['codigo'] ?? '') . ' - ' . ($r['proyecto'] ?? ''),
                $r['nombre'] ?? '',
                $r['fase_nombre'] ?? '',
                $r['fecha_inicio_plan'] ?? '',
                $r['fecha_fin_plan'] ?? '',
                $r['estado'] ?? '',
                $r['avance_general'] ?? 0,
                $r['responsable_solicitud'] ?? '',
                $r['total_tareas'] ?? 0,
                $r['tareas_completadas'] ?? 0,
                $r['tareas_pendientes'] ?? 0,
                $r['tareas_atrasadas'] ?? 0,
                $r['total_hitos'] ?? 0,
                $r['hitos_pendientes'] ?? 0,
                $r['hitos_atrasados'] ?? 0,
                $r['horas_estimadas'] ?? 0,
                $r['horas_reales'] ?? 0,
                $r['dias_atraso_plan'] ?? 0,
            ], ';');
        }

        fclose($out);
        exit;
    }

    public function create(): void
    {
        Auth::requirePermission('planning.create');

        $model = new Plan();

        $this->view('planning/create', [
            'requests' => $model->requestsWithoutPlan(),
            'user' => Auth::user(),
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('planning.create');
        verify_csrf();

        $model = new Plan();

        $requestId = (int)($_POST['request_id'] ?? 0);
        $inicio = $_POST['fecha_inicio_plan'] ?? null;
        $fin = $_POST['fecha_fin_plan'] ?? null;
        $nombre = trim($_POST['nombre'] ?? '');

        if ($requestId <= 0) {
            flash('error', 'Debe seleccionar un proyecto.');
            $this->redirect('/planning/create');
            return;
        }

        if ($nombre === '') {
            flash('error', 'Debe ingresar el nombre del plan.');
            $this->redirect('/planning/create');
            return;
        }

        if (!empty($inicio) && !empty($fin) && $fin < $inicio) {
            flash('error', 'La fecha de fin no puede ser menor que la fecha de inicio.');
            $this->redirect('/planning/create');
            return;
        }

        $existingPlan = $model->findByRequest($requestId);
        if ($existingPlan) {
            flash('error', 'Este proyecto ya tiene una planificación creada. Se redirigió al Gantt correspondiente.');
            $this->redirect('/planning/gantt?id=' . (int)$existingPlan['id']);
            return;
        }

        if (!$model->requestCanBePlanned($requestId)) {
            flash('error', 'Solo se puede crear planificación para solicitudes desde la fase Desarrollo en adelante.');
            $this->redirect('/planning/create');
            return;
        }

        $ok = $model->create($_POST, (int)Auth::user()['id']);

        if ($ok) {
            AuditService::businessEvent(
                'create',
                'planning',
                'project_plan',
                $requestId,
                'Planificación creada: ' . $nombre,
                [],
                [
                    'Solicitud ID' => $requestId,
                    'Nombre plan' => $nombre,
                    'Inicio' => $inicio,
                    'Fin' => $fin,
                ],
                'info'
            );
        }

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Planificación creada correctamente.' : 'No fue posible crear la planificación.'
        );

        $this->redirect('/planning');
    }

    public function show(): void
    {
        Auth::requirePermission('planning.view');

        $id = (int)($_GET['id'] ?? 0);

        $model = new Plan();
        $plan = $model->find($id);

        if (!$plan) {
            flash('error', 'Planificación no encontrada.');
            $this->redirect('/planning');
            return;
        }

        $this->view('planning/show', [
            'plan' => $plan,
            'milestones' => $model->milestones($id),
            'tasks' => $model->tasks($id),
            'stats' => $model->stats($id),
            'users' => $model->users(),
            'history' => $model->history($id),
            'user' => Auth::user(),
        ]);
    }

    public function gantt(): void
    {
        Auth::requirePermission('planning.view');

        $planId = (int)($_GET['id'] ?? 0);

        $model = new Plan();
        $plans = $model->all();

        if ($planId <= 0 && !empty($plans)) {
            $planId = (int)$plans[0]['id'];
        }

        if ($planId <= 0) {
            flash('error', 'No hay planificaciones disponibles.');
            $this->redirect('/planning');
            return;
        }

        $plan = $model->find($planId);

        if (!$plan) {
            flash('error', 'Planificación no encontrada.');
            $this->redirect('/planning');
            return;
        }

        $tasks = $model->tasks($planId);
        $milestones = $model->milestones($planId);

        $minDate = null;
        $maxDate = null;

        foreach ($tasks as $task) {
            $start = $task['fecha_inicio_plan'] ?? null;
            $end = $task['fecha_fin_plan'] ?? null;

            if ($start && (!$minDate || $start < $minDate)) {
                $minDate = $start;
            }
            if ($end && (!$maxDate || $end > $maxDate)) {
                $maxDate = $end;
            }
        }

        foreach ($milestones as $milestone) {
            $date = $milestone['fecha_hito'] ?? null;
            if ($date && (!$minDate || $date < $minDate)) {
                $minDate = $date;
            }
            if ($date && (!$maxDate || $date > $maxDate)) {
                $maxDate = $date;
            }
        }

        if (!$minDate) {
            $minDate = date('Y-m-01');
        }

        if (!$maxDate) {
            $maxDate = date('Y-m-t', strtotime($minDate));
        }

        $timelineStart = date('Y-m-d', strtotime($minDate . ' -3 days'));
        $timelineEnd = date('Y-m-d', strtotime($maxDate . ' +3 days'));

        $days = [];
        $cursor = strtotime($timelineStart);
        $endTs = strtotime($timelineEnd);

        while ($cursor <= $endTs) {
            $days[] = [
                'date' => date('Y-m-d', $cursor),
                'day' => date('d', $cursor),
                'month' => date('m', $cursor),
                'is_weekend' => in_array(date('N', $cursor), ['6', '7'], true),
            ];
            $cursor = strtotime('+1 day', $cursor);
        }

        $this->view('planning/gantt', [
            'plans' => $plans,
            'plan' => $plan,
            'tasks' => $tasks,
            'milestones' => $milestones,
            'days' => $days,
            'timelineStart' => $timelineStart,
            'timelineEnd' => $timelineEnd,
            'user' => Auth::user(),
        ]);
    }

    public function storeMilestone(): void
    {
        Auth::requirePermission('planning.edit');
        verify_csrf();

        $milestoneModel = new Milestone();
        $planModel = new Plan();

        $planId = (int)($_POST['project_plan_id'] ?? 0);

        if ($planModel->isClosedForChanges($planId)) {
            flash('error', 'El plan está cerrado o completado. No permite agregar nuevos hitos.');
            $this->redirect('/planning/show?id=' . $planId);
            return;
        }

        $ok = $milestoneModel->create($_POST);

        if ($ok) {

            AuditService::businessEvent(
                'create_milestone',
                'planning',
                'milestone',
                $planId,
                'Hito creado: ' . trim($_POST['nombre'] ?? ''),
                [],
                [
                    'Plan ID' => $planId,
                    'Nombre' => trim($_POST['nombre'] ?? ''),
                    'Fecha' => $_POST['fecha_hito'] ?? '',
                ],
                'info'
            );

         $planModel->log($planId, (int)Auth::user()['id'], 'Nuevo hito', trim($_POST['nombre'] ?? ''));
        }

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Hito agregado correctamente.' : 'No fue posible agregar el hito.'
        );

        $this->redirect('/planning/show?id=' . $planId);
    }

    public function storeTask(): void
    {
        Auth::requirePermission('planning.edit');
        verify_csrf();

        $taskModel = new Task();
        $planModel = new Plan();

        $planId = (int)($_POST['project_plan_id'] ?? 0);

        if ($planModel->isClosedForChanges($planId)) {
            flash('error', 'El plan está cerrado o completado. No permite agregar nuevas tareas.');
            $this->redirect('/planning/show?id=' . $planId);
            return;
        }

        $ok = $taskModel->create($_POST);

        if ($ok) {
                AuditService::businessEvent(
                    'create_task',
                    'planning',
                    'task',
                    $planId,
                    'Tarea creada: ' . trim($_POST['nombre'] ?? ''),
                    [],
                    [
                        'Plan ID' => $planId,
                        'Nombre' => trim($_POST['nombre'] ?? ''),
                        'Inicio' => $_POST['fecha_inicio_plan'] ?? '',
                        'Fin' => $_POST['fecha_fin_plan'] ?? '',
                        'Responsable ID' => $_POST['responsable_id'] ?? '',
                    ],
                    'info'
                );

            $planModel->recalculateProgress($planId);
            $planModel->log($planId, (int)Auth::user()['id'], 'Nueva tarea', trim($_POST['nombre'] ?? ''));
        }

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Tarea agregada correctamente.' : 'No fue posible agregar la tarea.'
        );

        $this->redirect('/planning/show?id=' . $planId);
    }

    public function updateTask(): void
    {
        Auth::requirePermission('planning.edit');
        verify_csrf();

        $taskModel = new Task();
        $planModel = new Plan();

        $planId = (int)($_POST['project_plan_id'] ?? 0);

        if ($planModel->isClosedForChanges($planId)) {
            flash('error', 'El plan está cerrado o completado. No permite actualizar tareas.');
            $this->redirect('/planning/show?id=' . $planId);
            return;
        }

        $taskId = (int)($_POST['id'] ?? 0);
        $ok = $taskModel->update($_POST);

        if ($ok) {

            AuditService::businessEvent(
                'update_task',
                'planning',
                'task',
                $taskId,
                'Tarea actualizada: ' . trim($_POST['nombre'] ?? ''),
                [],
                [
                    'Plan ID' => $planId,
                    'Nombre' => trim($_POST['nombre'] ?? ''),
                    'Estado' => $_POST['estado'] ?? '',
                    'Avance' => $_POST['avance'] ?? '',                    
                ],
                'info'
            );

            $planModel->recalculateProgress($planId);
            $planModel->log($planId, (int)Auth::user()['id'], 'Actualización de tarea', trim($_POST['nombre'] ?? ''));
        }

        flash(
            $ok ? 'success' : 'error',
            $ok ? 'Tarea actualizada correctamente.' : 'No fue posible actualizar la tarea.'
        );

        $this->redirect('/planning/show?id=' . $planId);
    }
}
