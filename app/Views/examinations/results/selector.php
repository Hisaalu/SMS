<!-- File: /app/Views/examinations/results/selector.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Class Results</h4>
            <p class="text-muted mb-0">Select academic period and class to view or generate assessment results.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="<?= BASE_URL ?>/results/class" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="academic_year_id" class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
                    <select name="academic_year_id" id="academic_year_id" class="form-select" required>
                        <option value="">-- Select Year --</option>
                        <?php foreach ($academicYears as $year): ?>
                            <option value="<?= $year['id'] ?>" <?= (isset($_GET['academic_year_id']) && $_GET['academic_year_id'] == $year['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="term_id" class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                    <select name="term_id" id="term_id" class="form-select" required>
                        <option value="">-- Select Term --</option>
                        <?php foreach ($terms as $term): ?>
                            <option value="<?= $term['id'] ?>" <?= (isset($_GET['term_id']) && $_GET['term_id'] == $term['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($term['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="class_id" class="form-label fw-semibold">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="class_id" class="form-select" required>
                        <option value="">-- Select Class --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['id'] ?>" <?= (isset($_GET['class_id']) && $_GET['class_id'] == $class['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="examination_id" class="form-label fw-semibold">Examination <span class="text-danger">*</span></label>
                    <select name="examination_id" id="examination_id" class="form-select" required>
                        <option value="">-- Select Examination --</option>
                        <?php foreach ($examinations as $exam): ?>
                            <option value="<?= $exam['id'] ?>" <?= (isset($_GET['examination_id']) && $_GET['examination_id'] == $exam['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($exam['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 text-end mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-search me-1"></i> View Results
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($examination) && $examination): ?>
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Results for <?= htmlspecialchars($examination['name']) ?></h5>
            <span class="badge bg-primary"><?= count($results) ?> Record(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Admission No.</th>
                            <th>Student Name</th>
                            <th>Subject</th>
                            <th>Marks Obtained</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($results)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No marks recorded for this selection.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($results as $index => $row): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($row['admission_number']) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/results/student/<?= $row['student_id'] ?>" class="text-decoration-none fw-semibold">
                                            <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($row['subject_name']) ?></td>
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
<?php endif; ?>