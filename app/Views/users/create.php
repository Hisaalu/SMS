<!-- File: /app/Views/users/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Create New User</h4>
            <span class="text-muted small">Add a new team member or system access account</span>
        </div>
        <a href="<?= BASE_URL ?>/users" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Users
        </a>
    </div>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= $flash ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST" action="<?= BASE_URL ?>/users">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="first_name" placeholder="John" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="last_name" placeholder="Doe" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Username <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">@</span>
                        <input type="text" class="form-control" name="username" placeholder="johndoe" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" placeholder="john@school.com" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" name="password" minlength="8" placeholder="Minimum 8 characters" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Assign Roles</label>
                    <select class="form-select" name="roles[]" multiple style="min-height: 110px;">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted mt-1 d-block">
                        <i class="fas fa-info-circle me-1"></i> Hold <kbd>Ctrl</kbd> or <kbd>Cmd</kbd> to select multiple roles.
                    </small>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="<?= BASE_URL ?>/users" class="btn btn-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i> Save User
                </button>
            </div>
        </form>
    </div>
</div>