<!-- File: /app/Views/notifications/index.php -->
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Notifications</h4>
            <small class="text-muted">
                <?= (int)$unread ?> unread · <?= number_format((int)$total) ?> total
            </small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-secondary" id="markAllBtn">
                <i class="fas fa-check-double me-1"></i> Mark All as Read
            </button>
            <button type="button" class="btn btn-sm btn-secondary" id="clearAllBtn">
                <i class="fas fa-trash me-1"></i> Clear All
            </button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <span class="small text-muted me-2">Filter:</span>
                <?php
                    $filters = [
                        ''       => 'All',
                        'unread' => 'Unread',
                        'read'   => 'Read',
                    ];
                ?>
                <?php foreach ($filters as $key => $label): ?>
                    <a href="<?= BASE_URL ?>/notifications<?= $key !== '' ? '?filter=' . $key : '' ?>"
                       class="btn btn-sm <?= $filter === $key ? 'btn-primary' : 'btn-secondary' ?>">
                        <?= $label ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($items)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-bell-slash fa-2x mb-3 d-block opacity-50"></i>
                    <div>No notifications to display.</div>
                </div>
            <?php else: ?>
                <?php foreach ($items as $n): ?>
                    <?php
                        $isUnread = empty($n['read_at']);
                        $type     = (string)($n['type'] ?? 'info');
                        $accent   = match ($type) {
                            'success' => 'var(--success-color)',
                            'warning' => 'var(--warning-color)',
                            'danger'  => 'var(--danger-color)',
                            'message' => 'var(--accent-color)',
                            'system'  => 'var(--secondary-color, #6f42c1)',
                            default   => 'var(--accent-color)',
                        };
                        $icon = $n['icon'] ?? match ($type) {
                            'success' => 'fas fa-check-circle',
                            'warning' => 'fas fa-exclamation-triangle',
                            'danger'  => 'fas fa-times-circle',
                            'message' => 'fas fa-comment-dots',
                            'system'  => 'fas fa-cog',
                            default   => 'fas fa-info-circle',
                        };
                    ?>
                    <div class="d-flex gap-3 align-items-start p-3 border-bottom"
                         style="border-color: var(--border-color) !important;
                                <?= $isUnread ? 'background: rgba(37, 99, 235, 0.04);' : '' ?>">

                        <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 42px; height: 42px; background: <?= $accent ?>; color: #fff;">
                            <i class="<?= htmlspecialchars($icon) ?>"></i>
                        </div>

                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-bold" style="color: var(--text-color);">
                                    <?= htmlspecialchars($n['title'] ?? 'Notification') ?>
                                </span>
                                <?php if ($isUnread): ?>
                                    <span class="badge bg-primary-subtle">New</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($n['message'])): ?>
                                <div class="small" style="color: var(--text-muted);">
                                    <?= htmlspecialchars($n['message']) ?>
                                </div>
                            <?php endif; ?>

                            <div class="small mt-1" style="color: var(--text-muted);">
                                <?= date('d M Y, H:i', strtotime($n['created_at'])) ?>
                            </div>
                        </div>

                        <div class="d-flex gap-1 flex-shrink-0">
                            <?php if (!empty($n['action_url'])): ?>
                                <a href="<?= htmlspecialchars($n['action_url']) ?>"
                                   class="btn btn-sm btn-secondary"
                                   data-notif-open="<?= (int)$n['id'] ?>">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($isUnread): ?>
                                <button type="button"
                                        class="btn btn-sm btn-secondary"
                                        data-notif-read="<?= (int)$n['id'] ?>"
                                        title="Mark as read">
                                    <i class="fas fa-check"></i>
                                </button>
                            <?php endif; ?>
                            <button type="button"
                                    class="btn btn-sm btn-secondary"
                                    data-notif-delete="<?= (int)$n['id'] ?>"
                                    title="Delete">
                                <i class="fas fa-trash" style="color: var(--danger-color);"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($perPage > 0 && $total > $perPage): ?>
        <?php $totalPages = (int)ceil($total / $perPage); ?>
        <nav class="mt-3">
            <ul class="pagination pagination-sm justify-content-end mb-0">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php
                        $query = $filter !== '' ? '?filter=' . $filter . '&page=' . $i : '?page=' . $i;
                    ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/notifications<?= $query ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

</div>

<script>
(function () {
    const url = '<?= BASE_URL ?>';

    function post(path, body) {
        return fetch(url + path, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new URLSearchParams(body || {}).toString(),
        }).then(r => r.json());
    }

    document.querySelectorAll('[data-notif-read]').forEach(btn => {
        btn.addEventListener('click', function () {
            post('/notifications/mark-read', { id: this.dataset.notifRead })
                .then(() => location.reload());
        });
    });

    document.querySelectorAll('[data-notif-delete]').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('Delete this notification?')) return;
            post('/notifications/delete', { id: this.dataset.notifDelete })
                .then(() => location.reload());
        });
    });

    document.querySelectorAll('[data-notif-open]').forEach(a => {
        a.addEventListener('click', function () {
            post('/notifications/mark-read', { id: this.dataset.notifOpen });
            // Navigation proceeds naturally
        });
    });

    const markAll = document.getElementById('markAllBtn');
    if (markAll) {
        markAll.addEventListener('click', function () {
            post('/notifications/mark-all-read').then(() => location.reload());
        });
    }

    const clearAll = document.getElementById('clearAllBtn');
    if (clearAll) {
        clearAll.addEventListener('click', function () {
            if (!confirm('Delete all notifications? This cannot be undone.')) return;
            post('/notifications/clear').then(() => location.reload());
        });
    }
})();
</script>