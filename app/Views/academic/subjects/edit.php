<!-- File: /app/Views/academic/subjects/edit.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Subject</h4>
        <a href="<?= BASE_URL ?>/academic/subjects" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger mb-2"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <?php $currentType = strtolower((string)($subject->type ?? 'core')); ?>

    <div class="card p-4">
        <form method="POST" action="<?= BASE_URL ?>/academic/subjects/<?= (int)$subject->id ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label fw-bold">Subject Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($subject->name) ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="code" class="form-label fw-bold">Subject Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" id="code" class="form-control" value="<?= htmlspecialchars($subject->code) ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="department_id" class="form-label fw-bold">Department</label>
                    <select name="department_id" id="department_id" class="form-select">
                        <option value="">-- Optional Department --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= (int)$dept->id ?>" <?= ((int)$subject->department_id === (int)$dept->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="type" class="form-label fw-bold">Subject Type</label>
                    <select name="type" id="type" class="form-select">
                        <option value="core"     <?= $currentType === 'core'     ? 'selected' : '' ?>>Core</option>
                        <option value="elective" <?= $currentType === 'elective' ? 'selected' : '' ?>>Elective</option>
                        <option value="optional" <?= $currentType === 'optional' ? 'selected' : '' ?>>Optional</option>
                        <option value="other"    <?= $currentType === 'other'    ? 'selected' : '' ?>>Other (not graded)</option>
                    </select>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Update Subject</button>
                    <a href="<?= BASE_URL ?>/academic/subjects" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>