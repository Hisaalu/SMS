<!-- File: /app/Views/users/edit.php -->
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Edit User: <?= htmlspecialchars($user->username) ?></h4>
            <span class="text-muted small">Update profile settings, security access, and roles</span>
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
        <form method="POST" action="<?= BASE_URL ?>/users/<?= $user->id ?>">
            <input type="hidden" name="_method" value="PUT">
            
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark small">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg fs-6" name="first_name" value="<?= htmlspecialchars($user->first_name) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark small">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg fs-6" name="last_name" value="<?= htmlspecialchars($user->last_name) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark small">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted">@</span>
                        <input type="text" class="form-control form-control-lg fs-6 bg-light text-muted" value="<?= htmlspecialchars($user->username) ?>" disabled>
                    </div>
                    <input type="hidden" name="username" value="<?= htmlspecialchars($user->username) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark small">Email Address</label>
                    <input type="email" class="form-control form-control-lg fs-6 bg-light text-muted" value="<?= htmlspecialchars($user->email) ?>" disabled>
                    <input type="hidden" name="email" value="<?= htmlspecialchars($user->email) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark small">New Password</label>
                    <input type="password" class="form-control form-control-lg fs-6" name="password" minlength="8" placeholder="Leave blank to keep current password">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark small">Account Status</label>
                    <select class="form-select form-select-lg fs-6" name="status">
                        <option value="active" <?= $user->status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $user->status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="suspended" <?= $user->status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold text-dark small">Assigned Roles</label>
                    <select class="form-select form-select-lg fs-6" name="roles[]" multiple style="min-height: 120px;">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role->id ?>" <?= in_array($role->id, $userRoles) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i> Hold <kbd>Ctrl</kbd> or <kbd>Cmd</kbd> to select multiple roles.</small>
                </div>
            </div>
            
            <hr class="my-4 text-secondary opacity-25">

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= BASE_URL ?>/users" class="btn btn-light px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                    <i class="fas fa-save me-2"></i> Update User
                </button>
            </div>
        </form>
    </div>
</div>