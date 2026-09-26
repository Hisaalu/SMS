<!-- File: /app/Views/examinations/grading/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Create Grading System</h4>
            <small class="text-muted">Define a grading system and the classes it applies to</small>
        </div>
        <a href="<?= BASE_URL ?>/grading/systems" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/grading/systems">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name"
                               placeholder="e.g., Primary 2 Grading" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Academic Year (Optional)</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">All Years</option>
                            <?php foreach ($years as $y): ?>
                                <option value="<?= (int)$y['id'] ?>"><?= htmlspecialchars($y['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Default</label>
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="is_default" id="isDefault" value="1">
                            <label class="form-check-label" for="isDefault">
                                School-wide default grading system
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Applies to Classes
                            <span class="text-muted fw-normal">(optional)</span>
                        </label>

                        <?php if (empty($classes)): ?>
                            <div class="alert alert-light border small mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                No classes have been created yet.
                                <a href="<?= BASE_URL ?>/academic/classes/create" class="ms-1">Add a class</a>
                                to assign this grading system to it.
                            </div>
                        <?php else: ?>
                            <div class="border rounded p-2" style="max-height: 220px; overflow-y: auto;">
                                <?php foreach ($classes as $c): ?>
                                    <?php $cId = (int)$c['id']; ?>
                                    <div class="form-check">
                                        <input type="checkbox"
                                               name="class_ids[]"
                                               value="<?= $cId ?>"
                                               id="cls_<?= $cId ?>"
                                               class="form-check-input">
                                        <label class="form-check-label" for="cls_<?= $cId ?>">
                                            <?= htmlspecialchars($c['name']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="form-text">
                                Leave all unchecked to keep this grading system school-wide.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create | Add Rules
                    </button>
                    <a href="<?= BASE_URL ?>/grading/systems" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>