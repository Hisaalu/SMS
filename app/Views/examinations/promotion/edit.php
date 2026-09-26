<!-- File: /app/Views/examinations/promotion/edit.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Edit Promotion Rule</h4>
            <small class="text-muted">Update promotion rule details</small>
        </div>
        <a href="<?= BASE_URL ?>/promotion/rules" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/promotion/rules/<?= $rule['id'] ?>">
                <input type="hidden" name="_method" value="PUT">
                
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Rule Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($rule['name']) ?>" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($rule['description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pass Mark (%)</label>
                        <input type="number" class="form-control" name="pass_mark" value="<?= $rule['pass_mark'] ?? 50 ?>" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Minimum Subjects Passed</label>
                        <input type="number" class="form-control" name="min_subjects_passed" value="<?= $rule['min_subjects_passed'] ?? 0 ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?= ($rule['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($rule['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="require_all_subjects" id="requireAll" value="1" <?= ($rule['require_all_subjects'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="requireAll">Require All Subjects Passed</label>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Rule
                    </button>
                    <a href="<?= BASE_URL ?>/promotion/rules" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>