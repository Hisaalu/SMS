<!-- File: /app/Views/users/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-nowrap mb-4 gap-2">
        <div class="flex-grow-1">
            <h4 class="fw-bold mb-1">User Management</h4>
            <span class="text-muted small">
                Manage your Users
            </span>
        </div>
        <a href="<?= BASE_URL ?>/users/create" class="btn btn-primary px-3 text-nowrap">
            <i class="fas fa-user-plus me-2"></i> User
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
                            <th class="ps-4 py-3">User</th>
                            <th class="py-3">Username</th>
                            <th class="py-3">Email</th>
                            <th class="py-3">Roles</th>
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
                                            <button type="button"
                                                    class="btn btn-secondary"
                                                    title="Delete User"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteUserModal"
                                                    data-id="<?= (int)$user->id ?>"
                                                    data-name="<?= htmlspecialchars(trim($user->first_name . ' ' . $user->last_name), ENT_QUOTES, 'UTF-8') ?>">
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

<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete User</h5>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">Are you sure you want to delete <strong id="deleteUserName">this user</strong>?</p>
                <p class="small text-muted mb-0">
                    This action cannot be undone. The account and its role assignments will be removed permanently.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteUserBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete User
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl   = document.getElementById('deleteUserModal');
    const nameEl    = document.getElementById('deleteUserName');
    const confirmEl = document.getElementById('confirmDeleteUserBtn');

    let currentId = 0;

    modalEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        currentId = parseInt(button.getAttribute('data-id'), 10) || 0;
        const name = button.getAttribute('data-name') || 'this user';
        nameEl.textContent = name;
    });

    confirmEl.addEventListener('click', function () {
        if (!currentId) return;

        confirmEl.disabled = true;
        confirmEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        fetch('<?= BASE_URL ?>/users/' + currentId, {
            method: 'DELETE',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) {
            return r.text().then(function (text) {
                try { return { ok: r.ok, status: r.status, json: JSON.parse(text) }; }
                catch (e) { return { ok: r.ok, status: r.status, json: null, raw: text }; }
            });
        })
        .then(function (res) {
            if (res.json && res.json.success) {
                window.location.reload();
                return;
            }

            const message = (res.json && res.json.error)
                ? res.json.error
                : 'Could not delete this user (HTTP ' + res.status + ').';

            showErrorToast(message);

            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete User';
        })
        .catch(function () {
            showErrorToast('Network error. Please check your connection and try again.');

            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete User';
        });
    });

    function showErrorToast(message) {
        if (window.NexaToast && typeof window.NexaToast.error === 'function') {
            window.NexaToast.error(message);
            return;
        }
        alert(message);
    }
})();
</script>