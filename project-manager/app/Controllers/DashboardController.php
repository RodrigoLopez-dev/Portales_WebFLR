<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Dashboard;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('dashboard.view');

        $model = new Dashboard();

        $this->view('dashboard/index', [
            'totals' => $model->totals(),
            'byStatus' => $model->requestsByStatus(),
            'byPhase' => $model->requestsByPhase(),
            'byType' => $model->requestsByType(),
            'byResponsible' => $model->requestsByResponsible(),
            'topLate' => $model->topLate(),
            'topBlocked' => $model->topBlocked(),
            'topPendingDocuments' => $model->topPendingDocuments(),
            'user' => Auth::user(),
        ]);
    }
}
