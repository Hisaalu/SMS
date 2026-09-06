<!-- File: /app/Views/examinations/results/examination.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Results</h4>
            <small class="text-muted"><?= htmlspecialchars($examination['name']) ?> - <?= htmlspecialchars($examination['assessment_type_name'] ?? '') ?></small>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/examinations" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <?php if ($examination['status'] !== 'published'): ?>
                <button onclick="publishResults(<?= $examination['id'] ?>)" class="btn btn-sm btn-success">
                    <i class="fas fa-check-circle me-1"></i> Publish Results
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6 class="text-muted">Total Students</h6>
                <h3><?= $totalStudents ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6 class="text-muted">Average Marks</h6>
                <h3><?= $average ?>%</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6 class="text-muted">Passed</h6>
                <h3 class="text-success"><?= $passed ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6 class="text-muted">Failed</h6>
                <h3 class="text-danger"><?= $failed ?></h3>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Admission No.</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Marks</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($results) || count($results) === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No results found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($results as $index => $row): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($row['admission_number']) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/results/student/<?= $row['student_id'] ?>">
                                            <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($row['class_name'] ?? '-') ?></td>
                                    <td><strong><?= $row['marks_obtained'] ?></strong></td>
                                    <td>
                                        <?php if ($row['marks_obtained'] >= 50): ?>
                                            <span class="badge bg-success">Pass</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Fail</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['remarks'] ?? '-') ?></td>
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
function publishResults(id) {
    if (confirm('Are you sure you want to publish these results?')) {
        fetch('<?= BASE_URL ?>/results/publish/' + id, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Failed to publish results');
            }
        })
        .catch(error => {
            alert('An error occurred');
        });
    }
}
</script>