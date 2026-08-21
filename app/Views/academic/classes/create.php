<!-- File: /app/Views/academic/classes/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Create New Class</h4>
        <a href="<?= BASE_URL ?>/academic/classes" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Classes
        </a>
    </div>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger mb-2"><?= $flash ?></div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST" action="<?= BASE_URL ?>/academic/classes">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label fw-bold">Class Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="e.g., Primary 1 or Senior 1" required>
                </div>

                <div class="col-md-6">
                    <label for="code" class="form-label fw-bold">Class Code</label>
                    <input type="text" name="code" id="code" class="form-control" placeholder="e.g., P1 or S1">
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Save Class</button>
                    <a href="<?= BASE_URL ?>/academic/classes" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>