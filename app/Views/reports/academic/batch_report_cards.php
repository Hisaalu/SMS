<!-- File: /app/Views/reports/academic/batch_report_cards.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Batch Report Cards</h4>
            <small class="text-muted">Generate report cards for an entire class or stream</small>
        </div>
        <a href="<?= BASE_URL ?>/reports/academic/report-cards" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <?php unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="GET" action="<?= BASE_URL ?>/reports/academic/batch-report-cards/view" target="_blank">

        <?php
            $formAction     = BASE_URL . '/reports/academic/batch-report-cards/view';
            $includeStudent = false;
            $defaults       = $defaults ?? [];
            include __DIR__ . '/_report_config.php';
        ?>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0 fw-bold" style="color: var(--accent-color);">
                    <i class="fas fa-users me-2"></i>Select Class, Stream and Period
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
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Class <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-select form-select-sm" required>
                            <option value="">Select Class</option>
                            <?php foreach ($filters['classes'] as $class): ?>
                                <option value="<?= (int)$class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Stream (Optional)</label>
                        <select name="stream_id" class="form-select form-select-sm">
                            <option value="">All Streams</option>
                            <?php foreach ($filters['streams'] as $stream): ?>
                                <option value="<?= (int)$stream['id'] ?>"><?= htmlspecialchars($stream['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-print me-1"></i> Generate Batch Report Cards
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="alert alert-info mt-3">
        <i class="fas fa-info-circle me-1"></i>
        Batch report cards open in a new tab. Use <strong>Print All</strong> to print them.
    </div>
</div>