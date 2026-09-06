<!-- File: /app/Views/examinations/assessment/types.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Assessment Types</h4>
            <small class="text-muted">Configure assessment types for examinations</small>
        </div>
        <a href="<?= BASE_URL ?>/assessment/types/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Assessment Type
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
                            <th>Code</th>
                            <th>Description</th>
                            <th>Max Marks</th>
                            <th>Weight (%)</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($types) || count($types) === 0): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-tasks fa-2x mb-2 d-block"></i>
                                    No assessment types found.
                                    <br>
                                    <a href="<?= BASE_URL ?>/assessment/types/create" class="btn btn-sm btn-primary mt-2">Create Your First Assessment Type</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($types as $type): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($type['name']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($type['code']) ?></code></td>
                                    <td><?= htmlspecialchars($type['description'] ?? '-') ?></td>
                                    <td><?= $type['max_marks'] ?? 100 ?></td>
                                    <td><?= $type['weight'] ?? 0 ?></td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/assessment/types/<?= $type['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteType(<?= $type['id'] ?>)" class="btn btn-sm btn-outline-danger">
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
function deleteType(id) {
    if (confirm('Are you sure you want to delete this assessment type?')) {
        fetch('<?= BASE_URL ?>/assessment/types/' + id, {
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
                alert(data.error || 'Failed to delete assessment type');
            }
        })
        .catch(error => {
            alert('An error occurred');
        });
    }
}
</script>