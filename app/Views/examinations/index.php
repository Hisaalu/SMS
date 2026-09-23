<!-- File: /app/Views/examinations/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Examinations</h4>
            <small class="text-muted">Manage school examinations</small>
        </div>
        <a href="<?= BASE_URL ?>/examinations/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Examination
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
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Academic Year</th>
                            <th>Term</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($examinations) || count($examinations) === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No examinations found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($examinations as $exam): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($exam['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($exam['code'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($exam['assessment_type_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($exam['academic_year_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($exam['term_name'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($exam['status'] === 'published'): ?>
                                            <span class="badge bg-success">Published</span>
                                        <?php elseif ($exam['status'] === 'completed'): ?>
                                            <span class="badge bg-info">Completed</span>
                                        <?php elseif ($exam['status'] === 'archived'): ?>
                                            <span class="badge bg-secondary">Archived</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/examinations/<?= $exam['id'] ?>/edit" class="btn btn-sm btn-outline-primary" title="Edit Examination">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/results/examination/<?= $exam['id'] ?>" class="btn btn-sm btn-outline-info" title="View Results">
                                            <i class="fas fa-chart-bar"></i>
                                        </a>
                                        <button onclick="deleteExam(<?= $exam['id'] ?>)" class="btn btn-sm btn-outline-danger" title="Delete Examination">
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
function deleteExam(id) {
    if (confirm('Are you sure you want to delete this examination?')) {
        fetch('<?= BASE_URL ?>/examinations/' + id, {
            method: 'DELETE',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Failed to delete examination');
            }
        })
        .catch(() => alert('An error occurred'));
    }
}
</script>