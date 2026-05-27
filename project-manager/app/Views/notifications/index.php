<?php
function notification_message_html(string $message): string
{
    $escaped = e($message);

    $escaped = preg_replace_callback(
        '~(https?://[^\s<]+)~i',
        function ($matches) {
            $url = $matches[1];

            return '<a href="' . e($url) . '" target="_blank" rel="noopener noreferrer">'
                . e($url)
                . '</a>';
        },
        $escaped
    );

    return nl2br($escaped);
}
?>

<style>
    .notification-content {
        font-size: 0.9rem;
        line-height: 1.28;
        max-height: 280px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .notification-card .card-body {
        padding: 0.85rem 1rem;
    }

    .notification-card .card-title {
        font-size: 1rem;
        line-height: 1.2;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Notificaciones</h1>
        <p class="text-muted mb-0">Avisos internos generados por eventos relevantes del sistema.</p>
    </div>

    <?php if (!empty($notifications)): ?>
        <form method="post" action="<?= e(base_url('/notifications/mark-all-read')) ?>">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <button type="submit" class="btn btn-outline-primary btn-sm">Marcar todas como leídas</button>
        </form>
    <?php endif; ?>
</div>

<?php if (empty($notifications)): ?>
    <div class="alert alert-info">No tienes notificaciones registradas.</div>
<?php else: ?>

    <div class="row g-3">
        <?php foreach ($notifications as $notification): ?>
            <?php
            $mensaje = preg_replace("/(\r\n|\r|\n){3,}/", "\n\n", (string) $notification['mensaje']);
            ?>

            <div class="col-md-6">
                <div
                    class="card shadow-sm h-100 notification-card <?= empty($notification['leida']) ? 'border-primary' : '' ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <h5 class="card-title mb-1">
                                    <?= e($notification['titulo']) ?>
                                    <?php if (empty($notification['leida'])): ?>
                                        <span class="badge bg-primary">Nueva</span>
                                    <?php endif; ?>
                                </h5>

                                <small class="text-muted">
                                    <?= e(date('d-m-Y H:i', strtotime($notification['created_at']))) ?>
                                </small>
                            </div>

                            <?php if (empty($notification['leida'])): ?>
                                <form method="POST" action="<?= e(base_url('/notifications/mark-read')) ?>">
                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $notification['id'] ?>">
                                    <input type="hidden" name="return_to"
                                        value="<?= e('/notifications?page=' . ((int) ($pagination['page'] ?? 1))) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        Marcar leída
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <div class="notification-content">
                            <?= notification_message_html($mensaje) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php
    $page = (int) ($pagination['page'] ?? 1);
    $pages = (int) ($pagination['pages'] ?? 1);
    ?>

    <?php if ($pages > 1): ?>
        <nav class="mt-3">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= e(base_url('/notifications?page=' . max(1, $page - 1))) ?>">Anterior</a>
                </li>

                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <?php if ($i === 1 || $i === $pages || abs($i - $page) <= 2): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= e(base_url('/notifications?page=' . $i)) ?>">
                                <?= e((string) $i) ?>
                            </a>
                        </li>
                    <?php elseif (abs($i - $page) === 3): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>

                <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link"
                        href="<?= e(base_url('/notifications?page=' . min($pages, $page + 1))) ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

<?php endif; ?>