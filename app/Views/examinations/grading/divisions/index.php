<!-- File: /app/Views/examinations/grading/divisions/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Division Schemes</h4>
            <small class="text-muted">
                <i class="fas fa-layer-group me-1"></i> Groupings of aggregate ranges — one scheme per grading system
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/grading/systems" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Grading Systems
            </a>
            <a href="<?= BASE_URL ?>/grading/divisions/create" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Division Scheme
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($schemes)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-layer-group fa-2x mb-2 d-block opacity-50"></i>
                    <div>No division schemes yet.</div>
                    <div class="small">Create one to group ranges under a grading system.</div>
                    <a href="<?= BASE_URL ?>/grading/divisions/create" class="btn btn-sm btn-primary mt-3">
                        <i class="fas fa-plus me-1"></i> Create Scheme
                    </a>
                </div>
            <?php else: ?>
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Scheme</th>
                            <th>Grading System</th>
                            <th>Applies To</th>
                            <th class="text-center">Ranges</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-3" width="180">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schemes as $s): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold"><?= htmlspecialchars($s['name']) ?></div>
                                    <?php if (!empty($s['description'])): ?>
                                        <small class="text-muted"><?= htmlspecialchars($s['description']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle"><?= htmlspecialchars($s['system_name']) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($s['class_name'])): ?>
                                        <span class="badge bg-secondary-subtle"><?= htmlspecialchars($s['class_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">All classes</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle"><?= (int)$s['range_count'] ?> ranges</span>
                                </td>
                                <td class="text-center">
                                    <?php if ($s['status'] === 'active'): ?>
                                        <span class="badge bg-success-subtle">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/grading/divisions/<?= (int)$s['id'] ?>/ranges"
                                       class="btn btn-sm btn-primary" title="Manage ranges">
                                        <i class="fas fa-list"></i> Ranges
                                    </a>
                                    <a href="<?= BASE_URL ?>/grading/divisions/<?= (int)$s['id'] ?>/edit"
                                       class="btn btn-sm btn-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-secondary"
                                            onclick="deleteScheme(<?= (int)$s['id'] ?>)" title="Delete">
                                        <i class="fas fa-trash" style="color: var(--danger-color);"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteScheme(id) {
    if (!confirm('Delete this division scheme and all its ranges?')) return;
    fetch('<?= BASE_URL ?>/grading/divisions/' + id, {
        method: 'DELETE',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => d.success ? location.reload() : alert(d.error || 'Failed'))
    .catch(() => alert('An error occurred'));
}
</script>