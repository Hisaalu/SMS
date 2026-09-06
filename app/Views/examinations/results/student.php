<!-- File: /app/Views/examinations/results/student.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Student Results</h4>
            <small class="text-muted"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></small>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/students/show?id=<?= $student['id'] ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Profile
            </a>
        </div>
    </div>

    <!-- Student Info -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Name:</strong> <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></p>
                    <p><strong>Admission:</strong> <?= htmlspecialchars($student['admission_number']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Category:</strong> <?= htmlspecialchars($student['category_name'] ?? '-') ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($student['status_name'] ?? '-') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card text-center p-3">
                <h6 class="text-muted">Total Examinations</h6>
                <h3><?= $totalExams ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center p-3">
                <h6 class="text-muted">Average Marks</h6>
                <h3><?= $average ?>%</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center p-3">
                <h6 class="text-muted">Examinations Taken</h6>
                <h3><?= $totalExams ?></h3>
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
                            <th>Examination</th>
                            <th>Type</th>
                            <th>Academic Year</th>
                            <th>Term</th>
                            <th>Marks</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($results) || count($results) === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No results found for this student.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['examination_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['assessment_type'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['academic_year'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['term'] ?? '-') ?></td>
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