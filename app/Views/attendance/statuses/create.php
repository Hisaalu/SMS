<!-- File: /app/Views/attendance/statuses/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Create Attendance Status</h4>
            <small class="text-muted">Define a new attendance status</small>
        </div>
        <a href="<?= BASE_URL ?>/attendance/statuses" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/attendance/statuses/store">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="code" placeholder="e.g., P, A, L" required>
                        <small class="text-muted">Short code for the status</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="counts_as_present" id="countsPresent" value="1">
                            <label class="form-check-label" for="countsPresent">Counts as Present</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="counts_as_absent" id="countsAbsent" value="1">
                            <label class="form-check-label" for="countsAbsent">Counts as Absent</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="counts_as_late" id="countsLate" value="1">
                            <label class="form-check-label" for="countsLate">Counts as Late</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="requires_reason" id="requiresReason" value="1">
                            <label class="form-check-label" for="requiresReason">Requires Reason</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Display Order</label>
                        <input type="number" class="form-control" name="display_order" value="0">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Status
                    </button>
                    <a href="<?= BASE_URL ?>/attendance/statuses" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.querySelector('input[name="name"]');
    const codeInput = document.querySelector('input[name="code"]');
    
    nameInput.addEventListener('input', function() {
        if (!codeInput.value || codeInput.value === '') {
            codeInput.value = this.value.substring(0, 3).toUpperCase();
        }
    });
});
</script>