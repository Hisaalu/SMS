<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Department</h4>
        <a href="<?= BASE_URL ?>/academic/departments" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card p-4">
        <form method="POST" action="<?= BASE_URL ?>/academic/departments/<?= $department->id ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label fw-bold">Department Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($department->name) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="code" class="form-label fw-bold">Department Code</label>
                    <input type="text" name="code" id="code" class="form-control" value="<?= htmlspecialchars($department->code ?? '') ?>">
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Update Department</button>
                    <a href="<?= BASE_URL ?>/academic/departments" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>