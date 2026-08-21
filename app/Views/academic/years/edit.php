<!-- File: /app/Views/academic/years/edit.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Academic Year: <?= htmlspecialchars($year->name) ?></h4>
        <a href="<?= BASE_URL ?>/academic/years" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger mb-2"><?= $flash ?></div>
    <?php endif; ?>

    <div class="card p-3">
        <form method="POST" action="<?= BASE_URL ?>/academic/years/<?= $year->id ?>">
            <input type="hidden" name="_method" value="PUT">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Year Name</label>
                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($year->name) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="active" <?= $year->status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $year->status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="archived" <?= $year->status === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="<?= $year->start_date ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="<?= $year->end_date ?>" required>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_current" id="isCurrent" value="1" <?= $year->is_current ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isCurrent">Set as Current Academic Year</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">
                <i class="fas fa-save me-1"></i> Update Academic Year
            </button>
        </form>
    </div>
</div>