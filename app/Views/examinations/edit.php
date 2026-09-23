<!-- File: /app/Views/examinations/edit.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Edit Examination</h4>
            <small class="text-muted">Update examination details</small>
        </div>
        <a href="<?= BASE_URL ?>/examinations" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/examinations/<?= $examination['id'] ?>/update">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Examination Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($examination['name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" name="code" value="<?= htmlspecialchars($examination['code'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($examination['description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                        <select class="form-select" name="academic_year_id" required>
                            <option value="">Select Year</option>
                            <?php foreach ($academicYears as $year): ?>
                                <option value="<?= $year['id'] ?>" <?= $year['id'] == $examination['academic_year_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($year['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Term <span class="text-danger">*</span></label>
                        <select class="form-select" name="academic_period_id" required>
                            <option value="">Select Term</option>
                            <?php foreach ($terms as $term): ?>
                                <option value="<?= $term['id'] ?>" <?= $term['id'] == $examination['academic_period_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($term['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Assessment Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="assessment_type_id" required>
                            <option value="">Select Type</option>
                            <?php foreach ($assessmentTypes as $type): ?>
                                <option value="<?= $type['id'] ?>" <?= $type['id'] == $examination['assessment_type_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="draft" <?= $examination['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= $examination['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="completed" <?= $examination['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="archived" <?= $examination['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="<?= htmlspecialchars($examination['start_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($examination['end_date'] ?? '') ?>">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Examination
                    </button>
                    <a href="<?= BASE_URL ?>/examinations" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>