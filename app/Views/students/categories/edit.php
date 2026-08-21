<div class="container-fluid px-4 py-3">
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <?php unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Edit Student Category</h4>
        <a href="<?= BASE_URL ?>/student-categories" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="card shadow-sm border-0 col-md-8 mx-auto">
        <div class="card-body p-4">
            <form action="<?= BASE_URL ?>/student-categories/update" method="POST">
                <input type="hidden" name="id" value="<?= $category['id'] ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Code *</label>
                    <input type="text" name="code" class="form-control text-uppercase" value="<?= htmlspecialchars($category['code']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="active" <?= $category['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $category['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-4">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>