<!-- File: /app/Views/examinations/grading/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Create Grading System</h4>
            <small class="text-muted">Define a Grading System</small>
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
            <form method="POST" action="<?= BASE_URL ?>/grading/systems">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g., Primary 2 Grading" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Applies To Class</label>
                        <select name="class_id" class="form-select">
                            <option value="">All Classes (School-wide)</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Academic Year (Optional)</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">All Years</option>
                            <?php foreach ($years as $y): ?>
                                <option value="<?= $y['id'] ?>"><?= htmlspecialchars($y['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create | Add Rules
                    </button>
                    <a href="<?= BASE_URL ?>/grading/systems" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>