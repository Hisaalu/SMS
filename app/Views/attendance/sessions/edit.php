<!-- File: /app/Views/attendance/sessions/edit.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Edit Attendance Session</h4>
            <small class="text-muted">Update session settings</small>
        </div>
        <a href="<?= BASE_URL ?>/attendance/sessions" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/attendance/sessions/<?= $session->id ?>/update">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Session Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($session->name) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="code" value="<?= htmlspecialchars($session->code) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Start Time</label>
                        <input type="time" class="form-control" name="start_time" value="<?= htmlspecialchars($session->start_time ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Time</label>
                        <input type="time" class="form-control" name="end_time" value="<?= htmlspecialchars($session->end_time ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?= ($session->status ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($session->status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Display Order</label>
                        <input type="number" class="form-control" name="display_order" value="<?= htmlspecialchars($session->display_order ?? 0) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($session->description ?? '') ?></textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Session
                    </button>
                    <a href="<?= BASE_URL ?>/attendance/sessions" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>