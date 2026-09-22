<!-- File: /app/Views/reports/academic/report_cards.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Report Cards</h4>
            <small class="text-muted">Configure and generate student report cards</small>
        </div>
        <a href="<?= BASE_URL ?>/reports/academic/batch-report-cards" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-layer-group me-1"></i> Batch Mode
        </a>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <?php unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="GET" action="<?= BASE_URL ?>/reports/academic/report-card/view" target="_blank">

        <?php
            $formAction     = BASE_URL . '/reports/academic/report-card/view';
            $includeStudent = true;
            $defaults       = $defaults ?? [];
            include __DIR__ . '/_report_config.php';
        ?>

        <div class="card mt-3 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-user-graduate me-2"></i>Select Student and Period
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Academic Year <span class="text-danger">*</span></label>
                        <select name="academic_year_id" class="form-select form-select-sm" required>
                            <option value="">Select Year</option>
                            <?php foreach ($filters['academic_years'] as $year): ?>
                                <option value="<?= (int)$year['id'] ?>"><?= htmlspecialchars($year['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Term <span class="text-danger">*</span></label>
                        <select name="term_id" class="form-select form-select-sm" required>
                            <option value="">Select Term</option>
                            <?php foreach ($filters['terms'] as $term): ?>
                                <option value="<?= (int)$term['id'] ?>"><?= htmlspecialchars($term['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Student <span class="text-danger">*</span></label>
                        <select name="student_id" class="form-select form-select-sm" required>
                            <option value="">Select Student</option>
                            <?php foreach ($students as $student): ?>
                                <?php
                                    $label = $student['admission_number'] . ' - ' . $student['last_name'] . ' ' . $student['first_name'];
                                    if (!empty($student['class_name'])) {
                                        $label .= ' (' . $student['class_name'] . ')';
                                    }
                                ?>
                                <option value="<?= (int)$student['id'] ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-eye me-1"></i> Generate
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>