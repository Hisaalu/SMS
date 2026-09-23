<!-- File: /app/Views/academic/years/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Create Academic Year</h4>
        <a href="<?= BASE_URL ?>/academic/years" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger mb-2"><?= $flash ?></div>
    <?php endif; ?>

    <div class="card p-3">
        <form method="POST" action="<?= BASE_URL ?>/academic/years">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Year Name</label>
                    <input type="text" class="form-control" name="name" placeholder="e.g., 2025 Academic Year" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="start_date" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date" required>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_current" id="isCurrent" value="1">
                        <label class="form-check-label" for="isCurrent">Set as Current Academic Year</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">
                <i class="fas fa-save me-1"></i> Create Academic Year
            </button>
        </form>
    </div>
</div>