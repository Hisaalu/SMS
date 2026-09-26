<!-- File: /app/Views/academic/streams/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Create Stream</h4>
            <small class="text-muted">Add a new stream to a Class</small>
        </div>
        <a href="<?= BASE_URL ?>/academic/streams" class="btn btn-sm btn-outline-secondary">
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
        <form method="POST" action="<?= BASE_URL ?>/academic/streams">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="class_id" class="form-label fw-semibold">
                        Class <span class="text-danger">*</span>
                    </label>
                    <select name="class_id" id="class_id" class="form-select" required>
                        <option value="">— Select Class —</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= (int)$c['id'] ?>">
                                <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label for="name" class="form-label fw-semibold">
                        Stream Name <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control"
                           placeholder="e.g. North, Blue, or A"
                           required>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Stream
                    </button>
                    <a href="<?= BASE_URL ?>/academic/streams" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>