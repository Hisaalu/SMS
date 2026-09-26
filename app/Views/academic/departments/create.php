<!-- File: /app/Views/academic/departments/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Create Department</h4>
            <small class="text-muted">Add a new department to your school</small>
        </div>
        <a href="<?= BASE_URL ?>/academic/departments" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-3 p-md-4">
        <form method="POST" action="<?= BASE_URL ?>/academic/departments">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="name" class="form-label fw-semibold">
                        Department Name <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control"
                           placeholder="e.g. Science, Languages, Mathematics"
                           required>
                    <div class="form-text">The full name shown across the system.</div>
                </div>

                <div class="col-12 col-md-6">
                    <label for="code" class="form-label fw-semibold">Department Code</label>
                    <input type="text"
                           name="code"
                           id="code"
                           class="form-control"
                           placeholder="e.g. SCI, LANG, MATH">
                    <div class="form-text">Optional shorthand for quick identification.</div>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Department
                    </button>
                    <a href="<?= BASE_URL ?>/academic/departments" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>