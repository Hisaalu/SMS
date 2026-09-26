<!-- File: /app/Views/examinations/grading/edit.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Edit Grading System</h4>
            <small class="text-muted">Update your grading system and its classes</small>
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
            <form method="POST" action="<?= BASE_URL ?>/grading/systems/<?= (int)$system['id'] ?>">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name"
                               value="<?= htmlspecialchars($system['name']) ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Description</label>
                        <input type="text" class="form-control" name="description"
                               value="<?= htmlspecialchars($system['description'] ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Academic Year</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">All Years</option>
                            <?php foreach ($years as $y): ?>
                                <option value="<?= (int)$y['id'] ?>"
                                        <?= ((int)($system['academic_year_id'] ?? 0) === (int)$y['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($y['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active"   <?= ($system['status'] ?? 'active') === 'active'   ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($system['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Default</label>
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="is_default" id="isDefault"
                                   value="1" <?= !empty($system['is_default']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isDefault">
                                School-wide default
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
                            </div>
                        <?php else: ?>
                            <div class="border rounded p-2" style="max-height: 220px; overflow-y: auto;">
                                <?php foreach ($classes as $c): ?>
                                    <?php
                                        $cId = (int)$c['id'];
                                        $checked = in_array($cId, $selectedClasses ?? [], true);
                                    ?>
                                    <div class="form-check">
                                        <input type="checkbox"
                                               name="class_ids[]"
                                               value="<?= $cId ?>"
                                               id="cls_<?= $cId ?>"
                                               class="form-check-input"
                                               <?= $checked ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="cls_<?= $cId ?>">
                                            <?= htmlspecialchars($c['name']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-3 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update
                    </button>
                    <a href="<?= BASE_URL ?>/grading/systems/<?= (int)$system['id'] ?>/rules" class="btn btn-outline-info">
                        <i class="fas fa-list me-1"></i> Rules
                    </a>
                    <a href="<?= BASE_URL ?>/grading/systems" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>