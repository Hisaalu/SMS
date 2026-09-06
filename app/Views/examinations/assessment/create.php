<!-- File: /app/Views/examinations/assessment/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Create Assessment Type</h4>
            <small class="text-muted">Define a new assessment type</small>
        </div>
        <a href="<?= BASE_URL ?>/assessment/types" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/assessment/types">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g., Coursework" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="code" placeholder="e.g., CW" required>
                        <small class="text-muted">Short code for the assessment type</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Brief description"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max Marks</label>
                        <input type="number" class="form-control" name="max_marks" value="100" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Weight (%)</label>
                        <input type="number" class="form-control" name="weight" value="0" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Display Order</label>
                        <input type="number" class="form-control" name="display_order" value="0">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Assessment Type
                    </button>
                    <a href="<?= BASE_URL ?>/assessment/types" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>