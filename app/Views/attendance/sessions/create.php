<!-- File: /app/Views/attendance/sessions/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Create Attendance Session</h4>
            <small class="text-muted">Define a new session (e.g., Morning, Afternoon, Evening)</small>
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
            <form method="POST" action="<?= BASE_URL ?>/attendance/sessions/store">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Session Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g., Morning" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="code" placeholder="e.g., AM, PM" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start Time</label>
                        <input type="time" class="form-control" name="start_time">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Time</label>
                        <input type="time" class="form-control" name="end_time">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Display Order</label>
                        <input type="number" class="form-control" name="display_order" value="0">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Brief description of when this session applies"></textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Session
                    </button>
                    <a href="<?= BASE_URL ?>/attendance/sessions" class="btn btn-secondary">Cancel</a>
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