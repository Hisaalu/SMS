<!-- File: /app/Views/examinations/grading/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Grading Systems</h4>
            <small class="text-muted">Configure grading systems per class or school-wide</small>
        </div>
        <a href="<?= BASE_URL ?>/grading/systems/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Grading System
        </a>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/grading/divisions" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-layer-group me-1"></i> Divisions
        </a>
        <a href="<?= BASE_URL ?>/grading/systems/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Grading System
        </a>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Applies To</th>
                        <th>Academic Year</th>
                        <th>Rules</th>
                        <th>Status</th>
                        <th>Default</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($systems)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No grading systems found.
                            </td>
                        </tr>
                    <?php else: foreach ($systems as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['name']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($s['description'] ?? '') ?></small>
                            </td>
                            <td>
                                <?php if (!empty($s['class_name'])): ?>
                                    <span class="badge bg-primary"><?= htmlspecialchars($s['class_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">All Classes</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($s['academic_year_name'] ?? 'All Years') ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/grading/systems/<?= $s['id'] ?>/rules" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-list"></i> <?= (int)($s['rule_count'] ?? 0) ?> rules
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-<?= ($s['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>">
                                    <?= htmlspecialchars($s['status'] ?? 'active') ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($s['is_default'])): ?>
                                    <span class="badge bg-primary">Default</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/grading/systems/<?= $s['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteSystem(<?= $s['id'] ?>)" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function deleteSystem(id) {
    if (!confirm('Delete this grading system?')) return;
    fetch('<?= BASE_URL ?>/grading/systems/' + id, { method: 'DELETE', headers: {'X-Requested-With':'XMLHttpRequest'} })
        .then(r => r.json())
        .then(d => d.success ? location.reload() : alert(d.error || 'Failed'))
        .catch(() => alert('Error'));
}
</script>