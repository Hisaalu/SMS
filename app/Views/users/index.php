<!-- File: /app/Views/users/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1">User Management</h4>
            <span class="text-muted small">
                <i class="fas fa-school me-1" style="color: var(--accent-color);"></i>
                School: <strong><?= htmlspecialchars($schoolName ?? 'My School') ?></strong>
            </span>
        </div>
        <a href="<?= BASE_URL ?>/users/create" class="btn btn-primary px-3">
            <i class="fas fa-user-plus me-2"></i> New User
        </a>
    </div>

    <?php if ($flash = $this->getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $flash ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= $flash ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <?php if (count($users) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4 py-3">User Details</th>
                            <th class="py-3">Username</th>
                            <th class="py-3">Email</th>
                            <th class="py-3">Assigned Roles</th>
                            <th class="py-3">Status</th>
                            <th class="text-end pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3 fw-bold d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px;">
                                            <?= strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?></div>
                                            <small class="text-muted d-sm-none"><?= htmlspecialchars($user->email) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><code>@<?= htmlspecialchars($user->username) ?></code></td>
                                <td class="text-muted"><?= htmlspecialchars($user->email) ?></td>
                                <td>
                                    <?php
                                    $roles = $user->roles();
                                    if (!empty($roles)):
                                        foreach ($roles as $role): ?>
                                            <span class="badge bg-primary-subtle rounded-pill me-1 px-2 py-1">
                                                <?= htmlspecialchars($role['name']) ?>
                                            </span>
                                        <?php endforeach;
                                    else: ?>
                                        <span class="badge bg-secondary-subtle rounded-pill px-2 py-1">No Role</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user->status === 'active'): ?>
                                        <span class="badge bg-success-subtle rounded-pill px-2 py-1">Active</span>
                                    <?php elseif ($user->status === 'suspended'): ?>
                                        <span class="badge bg-danger-subtle rounded-pill px-2 py-1">Suspended</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle rounded-pill px-2 py-1"><?= ucfirst(htmlspecialchars($user->status)) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 py-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/users/<?= $user->id ?>/edit" class="btn btn-secondary" title="Edit User">
                                            <i class="fas fa-pen" style="color: var(--accent-color);"></i>
                                        </a>
                                        <?php if ($user->id != $currentUserId): ?>
                                            <button onclick="deleteUser(<?= $user->id ?>)" class="btn btn-secondary" title="Delete User">
                                                <i class="fas fa-trash-alt" style="color: var(--danger-color);"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-users-slash fa-3x mb-3 opacity-50"></i>
                <h5>No Users Found</h5>
                <p class="mb-3">There are no registered accounts in your school organization.</p>
                <a href="<?= BASE_URL ?>/users/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Create First User
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteUser(id) {
    if (confirm('Are you sure you want to permanently remove this user account?')) {
        fetch('<?= BASE_URL ?>/users/' + id, {
            method: 'DELETE',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Failed to delete user');
            }
        })
        .catch(() => alert('An unexpected network error occurred.'));
    }
}
</script>