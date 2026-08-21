<!-- File: /app/Views/academic/years/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Academic Years</h4>
        <a href="<?= BASE_URL ?>/academic/years/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Academic Year
        </a>
    </div>

    <?php if ($flash = $this->getFlash('success')): ?>
        <div class="alert alert-success mb-2"><?= $flash ?></div>
    <?php endif; ?>
    
    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger mb-2"><?= $flash ?></div>
    <?php endif; ?>

    <div class="card p-3">
        <?php if (count($years) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Current</th>
                            <th>Terms</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($years as $year): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($year->name) ?></strong></td>
                                <td><?= date('M d, Y', strtotime($year->start_date)) ?></td>
                                <td><?= date('M d, Y', strtotime($year->end_date)) ?></td>
                                <td>
                                    <?php if ($year->status === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= $year->status ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($year->is_current): ?>
                                        <span class="badge bg-primary">Current</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= count($year->terms()) ?></span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/academic/years/<?= $year->id ?>/edit" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="deleteYear(<?= $year->id ?>)" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <a href="<?= BASE_URL ?>/academic/terms?year=<?= $year->id ?>" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-calendar-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-calendar-alt fa-2x mb-2 d-block"></i>
                <p>No academic years found. Create your first academic year.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteYear(id) {
    if (confirm('Are you sure you want to delete this academic year?')) {
        fetch('<?= BASE_URL ?>/academic/years/' + id, {
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
                alert(data.error || 'Failed to delete academic year');
            }
        })
        .catch(error => {
            alert('An error occurred');
        });
    }
}
</script>