<!-- File: /app/Views/users/create.php -->
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Create New User</h4>
            <span class="text-muted small">Add a new team member or system access account</span>
        </div>
        <a href="<?= BASE_URL ?>/users" class="btn btn-outline-secondary btn-sm px-3 shadow-sm rounded-2">
            <i class="fas fa-arrow-left me-1"></i> Back to Users
        </a>
    </div>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= $flash ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="card card-shadow bg-white rounded-3 border-0 p-4">
        <form method="POST" action="<?= BASE_URL ?>/users">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark small">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg fs-6" name="first_name" placeholder="John" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark small">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg fs-6" name="last_name" placeholder="Doe" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark small">Username <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted">@</span>
                        <input type="text" class="form-control form-control-lg fs-6" name="username" placeholder="johndoe" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark small">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control form-control-lg fs-6" name="email" placeholder="john@school.com" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark small">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control form-control-lg fs-6" name="password" minlength="8" placeholder="Minimum 8 characters" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark small">Assign Roles</label>
                    <select class="form-select form-select-lg fs-6" name="roles[]" multiple style="min-height: 110px;">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i> Hold <kbd>Ctrl</kbd> or <kbd>Cmd</kbd> to select multiple roles.</small>
                </div>
            </div>
            
            <hr class="my-4 text-secondary opacity-25">

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= BASE_URL ?>/users" class="btn btn-light px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                    <i class="fas fa-save me-2"></i> Save User
                </button>
            </div>
        </form>
    </div>
</div>