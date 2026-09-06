<!-- File: /app/Views/examinations/grading/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Grading Systems</h4>
            <small class="text-muted">Configure grading systems and rules</small>
        </div>
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
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Rules</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($systems) || count($systems) === 0): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-percent fa-2x mb-2 d-block"></i>
                                    No grading systems found.
                                    <br>
                                    <a href="<?= BASE_URL ?>/grading/systems/create" class="btn btn-sm btn-primary mt-2">Create Your First Grading System</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($systems as $system): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($system['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($system['description'] ?? '-') ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/grading/systems/<?= $system['id'] ?>/rules" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-list me-1"></i> Manage Rules
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($system['status'] ?? 'active' === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/grading/systems/<?= $system['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteSystem(<?= $system['id'] ?>)" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function deleteSystem(id) {
    if (confirm('Are you sure you want to delete this grading system?')) {
        fetch('<?= BASE_URL ?>/grading/systems/' + id, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Failed to delete grading system');
            }
        })
        .catch(error => {
            alert('An error occurred');
        });
    }
}
</script>