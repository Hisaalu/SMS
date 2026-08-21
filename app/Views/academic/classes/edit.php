<!-- File: /app/Views/academic/classes/edit.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Class</h4>
        <a href="<?= BASE_URL ?>/academic/classes" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Classes
        </a>
    </div>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger mb-2"><?= $flash ?></div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST" action="<?= BASE_URL ?>/academic/classes/<?= $class->id ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label fw-bold">Class Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($class->name ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="code" class="form-label fw-bold">Class Code</label>
                    <input type="text" name="code" id="code" class="form-control" value="<?= htmlspecialchars($class->code ?? '') ?>">
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Update Class</button>
                    <a href="<?= BASE_URL ?>/academic/classes" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>