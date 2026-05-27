<?php
namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $app = require __DIR__ . '/../../config/app.php';
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/' . $view . '.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . base_url($path));
        exit;
    }
}
