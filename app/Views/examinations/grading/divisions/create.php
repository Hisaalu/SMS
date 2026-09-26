<!-- File: /app/Views/examinations/grading/divisions/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">New Division Scheme</h4>
            <small class="text-muted">A named grouping of your Ranges</small>
        </div>
        <a href="<?= BASE_URL ?>/grading/divisions" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/grading/divisions">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Scheme Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. O-Level Division" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Grading System <span class="text-danger">*</span></label>
                        <select name="grading_system_id" class="form-select" required>
                            <option value="">— Choose a grading system —</option>
                            <?php foreach ($systems as $sys): ?>
                                <option value="<?= (int)$sys['id'] ?>">
                                    <?= htmlspecialchars($sys['name']) ?>
                                    <?= !empty($sys['class_name']) ? ' — ' . htmlspecialchars($sys['class_name']) : '' ?>
                                    <?= !empty($sys['is_default']) ? ' (default)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2"
                                  placeholder="e.g. Division ranges for O-Level candidates"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Display Order</label>
                        <input type="number" name="display_order" class="form-control" value="0">
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create | + Ranges
                    </button>
                    <a href="<?= BASE_URL ?>/grading/divisions" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>