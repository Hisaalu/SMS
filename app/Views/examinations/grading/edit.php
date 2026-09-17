<!-- File: /app/Views/examinations/grading/edit.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Edit Grading System</h4>
            <small class="text-muted">Update grading system and its class assignment</small>
        </div>
        <a href="<?= BASE_URL ?>/grading/systems" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/grading/systems/<?= $system['id'] ?>">
                <input type="hidden" name="_method" value="PUT">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($system['name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" value="<?= htmlspecialchars($system['description'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Applies To Class</label>
                        <select name="class_id" class="form-select">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($system['class_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">All Years</option>
                            <?php foreach ($years as $y): ?>
                                <option value="<?= $y['id'] ?>" <?= ($system['academic_year_id'] ?? '') == $y['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($y['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($system['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($system['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_default" id="isDefault" value="1" <?= !empty($system['is_default']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isDefault">
                                School-wide default (fallback grading system)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update
                    </button>
                    <a href="<?= BASE_URL ?>/grading/systems/<?= $system['id'] ?>/rules" class="btn btn-outline-info">
                        <i class="fas fa-list me-1"></i> Manage Rules
                    </a>
                    <a href="<?= BASE_URL ?>/grading/systems" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>