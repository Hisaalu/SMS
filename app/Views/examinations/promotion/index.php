<!-- File: /app/Views/examinations/promotion/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Promotion Rules</h4>
            <small class="text-muted">Configure promotion rules for students</small>
        </div>
        <a href="<?= BASE_URL ?>/promotion/rules/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Promotion Rule
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
                            <th>Pass Mark</th>
                            <th>Min Subjects Passed</th>
                            <th>Require All Subjects</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rules) || count($rules) === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-arrow-up fa-2x mb-2 d-block"></i>
                                    No promotion rules found.
                                    <br>
                                    <a href="<?= BASE_URL ?>/promotion/rules/create" class="btn btn-sm btn-primary mt-2">Create Your First Promotion Rule</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rules as $rule): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($rule['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($rule['description'] ?? '-') ?></td>
                                    <td><?= $rule['pass_mark'] ?>%</td>
                                    <td><?= $rule['min_subjects_passed'] > 0 ? $rule['min_subjects_passed'] : 'Not Required' ?></td>
                                    <td>
                                        <?php if ($rule['require_all_subjects']): ?>
                                            <span class="badge bg-info">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($rule['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/promotion/rules/<?= $rule['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteRule(<?= $rule['id'] ?>)" class="btn btn-sm btn-outline-danger">
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
function deleteRule(id) {
    if (confirm('Are you sure you want to delete this promotion rule?')) {
        fetch('<?= BASE_URL ?>/promotion/rules/' + id, {
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
                alert(data.error || 'Failed to delete promotion rule');
            }
        })
        .catch(error => {
            alert('An error occurred');
        });
    }
}
</script>