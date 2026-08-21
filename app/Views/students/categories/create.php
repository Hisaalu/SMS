<div class="container-fluid px-4 py-3">
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <?php unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Create Student Category</h4>
        <a href="<?= BASE_URL ?>/student-categories" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="card shadow-sm border-0 col-md-8 mx-auto">
        <div class="card-body p-4">
            <form action="<?= BASE_URL ?>/student-categories/store" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Day Scholar, Boarder, Orphan" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Code *</label>
                    <input type="text" name="code" class="form-control text-uppercase" placeholder="e.g. DAY, BDR, ORP" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Optional description..."></textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-4">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>