<!-- File: /app/Views/attendance/sessions/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Attendance Sessions</h4>
            <small class="text-muted">Manage daily attendance sessions (e.g., Morning, Afternoon, Full Day)</small>
        </div>
        <a href="<?= BASE_URL ?>/attendance/sessions/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Session
        </a>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Time Window</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sessions)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No attendance sessions configured. Click <strong>Add Session</strong> to create one.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sessions as $session): 
                                $s = (object)$session;
                            ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($s->display_order ?? 0) ?></span></td>
                                    <td><strong><?= htmlspecialchars($s->name) ?></strong></td>
                                    <td><span class="badge bg-outline-primary border border-primary text-primary"><?= htmlspecialchars($s->code) ?></span></td>
                                    <td>
                                        <?php if (!empty($s->start_time) || !empty($s->end_time)): ?>
                                            <small class="text-muted">
                                                <i class="far fa-clock me-1"></i>
                                                <?= $s->start_time ? date('h:i A', strtotime($s->start_time)) : 'N/A' ?> - 
                                                <?= $s->end_time ? date('h:i A', strtotime($s->end_time)) : 'N/A' ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted small">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($s->description ?? '-') ?></td>
                                    <td>
                                        <?php if (($s->status ?? 'active') === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/attendance/sessions/<?= $s->id ?>/edit" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-session-btn" data-id="<?= $s->id ?>" data-name="<?= htmlspecialchars($s->name) ?>" title="Delete">
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
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-session-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            if (confirm(`Are you sure you want to delete the "${name}" session?`)) {
                fetch(`<?= BASE_URL ?>/attendance/sessions/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.error || 'Failed to delete session.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('An error occurred while deleting.');
                });
            }
        });
    });
});
</script>