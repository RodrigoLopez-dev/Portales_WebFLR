<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\AuditLog;
use App\Services\AuditService;

class AuditController extends Controller
{
    private function requireAdmin(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }

        if (Auth::role() !== 'Administrador') {
            http_response_code(403);
            die('Acceso denegado. Solo administradores pueden consultar la auditoría.');
        }
    }

    public function index(): void
    {
        $this->requireAdmin();

        AuditService::log([
            'action' => 'view',
            'module' => 'audit',
            'entity_type' => 'audit_logs',
            'description' => 'Ingreso al módulo de auditoría',
            'severity' => 'info',
        ]);

        $filters = [
            'q' => $_GET['q'] ?? '',
            'module' => $_GET['module'] ?? '',
            'action' => $_GET['action'] ?? '',
            'severity' => $_GET['severity'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $page = (int)($_GET['page'] ?? 1);
        $result = (new AuditLog())->paginate($filters, $page, 50);

        $this->view('audit/index', [
            'user' => Auth::user(),
            'filters' => $filters,
            'result' => $result,
        ]);
    }
}
