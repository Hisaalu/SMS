<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Create Stream</h4>
        <a href="<?= BASE_URL ?>/academic/streams" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card p-4">
        <form method="POST" action="<?= BASE_URL ?>/academic/streams">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="class_id" class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="class_id" class="form-select" required>
                        <option value="">-- Select Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c->id ?>"><?= htmlspecialchars($c->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="name" class="form-label fw-bold">Stream Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="e.g., North, Blue, or A" required>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Save Stream</button>
                </div>
            </div>
        </form>
    </div>
</div>