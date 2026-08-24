<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Attendance Statuses</h4>
            <small class="text-muted">Configure attendance statuses for the school</small>
        </div>
        <a href="<?= BASE_URL ?>/attendance/statuses/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Status
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
                            <th>Counts As</th>
                            <th>Requires Reason</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($statuses) || count($statuses) === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No attendance statuses found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($statuses as $status): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($status['name']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($status['code']) ?></code></td>
                                    <td><?= htmlspecialchars($status['description'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($status['counts_as_present']): ?>
                                            <span class="badge bg-success">Present</span>
                                        <?php endif; ?>
                                        <?php if ($status['counts_as_absent']): ?>
                                            <span class="badge bg-danger">Absent</span>
                                        <?php endif; ?>
                                        <?php if ($status['counts_as_late']): ?>
                                            <span class="badge bg-warning text-dark">Late</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($status['requires_reason']): ?>
                                            <span class="badge bg-info text-dark">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($status['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/attendance/statuses/<?= $status['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteStatus(<?= $status['id'] ?>)" class="btn btn-sm btn-outline-danger">
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
function deleteStatus(id) {
    if (confirm('Are you sure you want to delete this attendance status?')) {
        fetch('<?= BASE_URL ?>/attendance/statuses/' + id, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Failed to delete status');
            }
        })
        .catch(error => {
            alert('An error occurred while deleting.');
        });
    }
}
</script>