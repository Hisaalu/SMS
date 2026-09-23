<!-- File: /app/Views/users/edit.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Edit User: <?= htmlspecialchars($user->username) ?></h4>
            <span class="text-muted small">Update profile settings, security access, and roles</span>
        </div>
        <a href="<?= BASE_URL ?>/users" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Users
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST" action="<?= BASE_URL ?>/users/<?= $user->id ?>">
            <input type="hidden" name="_method" value="PUT">

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="first_name"
                           value="<?= htmlspecialchars($user->first_name) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="last_name"
                           value="<?= htmlspecialchars($user->last_name) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Username <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">@</span>
                        <input type="text" class="form-control" name="username"
                               value="<?= htmlspecialchars($user->username) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email"
                           value="<?= htmlspecialchars($user->email) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">New Password</label>
                    <input type="password" class="form-control" name="password" minlength="8"
                           placeholder="Leave blank to keep current password">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Account Status</label>
                    <select class="form-select" name="status">
                        <option value="active" <?= $user->status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $user->status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="suspended" <?= $user->status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold small">Assigned Roles</label>
                    <select class="form-select" name="roles[]" multiple style="min-height: 120px;">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>" <?= in_array($role['id'], $userRoles) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['name']) ?>
                            </option>
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
                    <i class="fas fa-save me-2"></i> Update User
                </button>
            </div>
        </form>
    </div>
</div>