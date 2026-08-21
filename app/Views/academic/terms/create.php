<!-- File: /app/Views/academic/terms/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Create Academic Term</h4>
        <a href="<?= BASE_URL ?>/academic/terms" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Terms
        </a>
    </div>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger mb-2"><?= $flash ?></div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST" action="<?= BASE_URL ?>/academic/terms">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="academic_year_id" class="form-label fw-bold">Academic Year <span class="text-danger">*</span></label>
                    <select name="academic_year_id" id="academic_year_id" class="form-select" required>
                        <option value="">-- Select Academic Year --</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?= $year->id ?>" <?= ($selectedYearId == $year->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="name" class="form-label fw-bold">Term Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="e.g., Term 1 or First Term" required>
                </div>

                <div class="col-md-4">
                    <label for="term_number" class="form-label fw-bold">Term Sequence/Number</label>
                    <input type="number" name="term_number" id="term_number" class="form-control" value="1" min="1" max="4">
                </div>

                <div class="col-md-4">
                    <label for="start_date" class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label for="end_date" class="form-label fw-bold">End Date <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="is_current" id="is_current" class="form-check-input" value="1">
                        <label for="is_current" class="form-check-label">Set as Current Term</label>
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Save Term</button>
                    <a href="<?= BASE_URL ?>/academic/terms" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>