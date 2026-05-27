<?php require_once __DIR__ . '/../../Helpers/functions.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($app['app_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('css/styles.css')) ?>">

    <style>
        .navbar-flr {
            background: linear-gradient(135deg, #AB0A3D 0%, #D70073 100%) !important;
            box-shadow: 0 6px 18px rgba(171, 10, 61, 0.25);
        }

        .navbar-flr .navbar-brand,
        .navbar-flr .nav-link,
        .navbar-flr .fw-semibold,
        .navbar-flr .small {
            color: #FFFFFF !important;
        }

        .navbar-flr .nav-link:hover,
        .navbar-flr .navbar-brand:hover {
            color: #FFC72C !important;
        }

        .navbar-flr .btn-outline-light {
            border-color: rgba(255, 255, 255, 0.65) !important;
            color: #FFFFFF !important;
        }

        .navbar-flr .btn-outline-light:hover {
            background: #FFFFFF !important;
            color: #AB0A3D !important;
        }

        .navbar-flr .badge.bg-danger {
            background-color: #FFC72C !important;
            color: #000000 !important;
        }
    </style>

</head>

<body>
    <?php if (!empty($_SESSION['user'])): ?>
        <!-- <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4"> -->
        <nav class="navbar navbar-expand-lg navbar-dark mb-4 navbar-flr">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?= e(base_url('/dashboard')) ?>">Gestor de Proyectos</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navMenu">
                    <ul class="navbar-nav me-auto">
                        <?php if (\App\Core\Auth::can('dashboard.view')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= e(base_url('/dashboard')) ?>">Dashboard</a>
                            </li>
                        <?php endif; ?>

                        <?php if (\App\Core\Auth::can('requests.view')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= e(base_url('/requests')) ?>">Solicitudes</a>
                            </li>
                        <?php endif; ?>

                        <?php if (\App\Core\Auth::can('requests.kanban')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= e(base_url('/requests/kanban')) ?>">Kanban</a>
                            </li>
                        <?php endif; ?>

                        <?php if (\App\Core\Auth::can('requests.create')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= e(base_url('/requests/create')) ?>">Nueva solicitud</a>
                            </li>
                        <?php endif; ?>

                        <?php if (\App\Core\Auth::can('planning.view')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= e(base_url('/planning')) ?>">Planificación</a>
                            </li>
                        <?php endif; ?>

                        <?php if (\App\Core\Auth::can('documents.view')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= e(base_url('/documents')) ?>">Documentos</a>
                            </li>
                        <?php endif; ?>

                        <?php if (\App\Core\Auth::can('users.view')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= e(base_url('/users')) ?>">Usuarios</a>
                            </li>
                        <?php endif; ?>

                        <?php if (\App\Core\Auth::role() === 'Administrador'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= e(base_url('/audit')) ?>">Auditoría</a>
                            </li>
                        <?php endif; ?>

                    </ul>

                    <?php
                    $notificationUnreadCount = 0;
                    try {
                        if (!empty($_SESSION['user']['id'])) {
                            $notificationUnreadCount = (new \App\Models\Notification())->unreadCount((int) $_SESSION['user']['id']);
                        }
                    } catch (\Throwable $e) {
                        $notificationUnreadCount = 0;
                    }
                    ?>
                    <a class="btn btn-outline-light btn-sm position-relative me-2"
                        href="<?= e(base_url('/notifications')) ?>">
                        Notificaciones
                        <?php if ($notificationUnreadCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $notificationUnreadCount > 99 ? '99+' : (int) $notificationUnreadCount ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <?php
                    $avatarUrl = trim((string) ($_SESSION['user']['avatar_url'] ?? ''));
                    $userName = (string) ($_SESSION['user']['nombre'] ?? 'Usuario');
                    $userRole = (string) ($_SESSION['user']['rol'] ?? 'Usuario');
                    $initial = strtoupper(mb_substr($userName, 0, 1, 'UTF-8'));
                    ?>

                    <div class="d-flex align-items-center gap-3 text-white me-3">
                        <?php if ($avatarUrl !== ''): ?>
                            <img src="<?= e($avatarUrl) ?>" class="rounded-circle border border-light" width="36" height="36"
                                style="object-fit: cover; display:block;" alt="Avatar" referrerpolicy="no-referrer"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                            <div class="rounded-circle bg-secondary text-white align-items-center justify-content-center border border-light"
                                style="width:36px;height:36px;font-size:15px;font-weight:bold;display:none;">
                                <?= e($initial) ?>
                            </div>
                        <?php else: ?>
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center border border-light"
                                style="width:36px;height:36px;font-size:15px;font-weight:bold;">
                                <?= e($initial) ?>
                            </div>
                        <?php endif; ?>

                        <div class="lh-sm">
                            <div class="fw-semibold text-white">
                                <?= e($userName) ?>
                            </div>
                            <div class="small text-white-50">
                                <?= e($userRole) ?>
                            </div>
                        </div>
                    </div>

                    <!-- <a class="btn btn-outline-light btn-sm" href="<?= e(base_url('/logout')) ?>">Salir</a> -->

                    <a class="btn btn-outline-light btn-sm" href="<?= e(base_url('/logout')) ?>">Salir</a>

                </div>
            </div>
        </nav>
    <?php endif; ?>

    <div class="container-fluid px-4">
        <?php if ($message = flash('success')): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
        <?php endif; ?>

        <?php if ($message = flash('error')): ?>
            <div class="alert alert-danger"><?= e($message) ?></div>
        <?php endif; ?>