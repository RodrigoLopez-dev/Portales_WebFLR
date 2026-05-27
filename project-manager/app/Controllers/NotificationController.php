<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
            return;
        }

        $model = new Notification();
        $userId = (int) Auth::user()['id'];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;

        $pagination = $model->paginateByUser($userId, $page, $perPage);

        $this->view('notifications/index', [
            'notifications' => $pagination['rows'],
            'pagination' => $pagination,
            'unreadCount' => $model->unreadCount($userId),
            'user' => Auth::user(),
        ]);
    }

    public function markRead(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
            return;
        }

        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $returnTo = $_POST['return_to'] ?? '/notifications';

        if ($id > 0) {
            (new Notification())->markAsRead($id, (int) Auth::user()['id']);
        }

        $this->redirect($this->safeReturnPath($returnTo));
    }

    public function markAllRead(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
            return;
        }

        verify_csrf();

        (new Notification())->markAllAsRead((int) Auth::user()['id']);
        $this->redirect('/notifications');
    }

    private function safeReturnPath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '/notifications';
        }

        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            return '/notifications';
        }

        return isset($path[0]) && $path[0] === '/' ? $path : '/notifications';
    }
}
