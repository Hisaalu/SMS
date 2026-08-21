<!-- File: /app/Views/roles/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Roles & Permissions</h4>
        <a href="/roles/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Role
        </a>
    </div>

    <?php if ($flash = $this->getFlash('success')): ?>
        <div class="alert alert-success mb-2"><?= $flash ?></div>
    <?php endif; ?>
    
    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger mb-2"><?= $flash ?></div>
    <?php endif; ?>

    <div class="card p-3">
        <?php if (count($roles) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): ?>
                            <tr>
                                <td><strong><?= $role->name ?></strong></td>
                                <td><code><?= $role->slug ?></code></td>
                                <td><?= $role->description ?? '-' ?></td>
                                <td>
                                    <?php if ($role->status === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= count($role->permissions()) ?></span>
                                </td>
                                <td>
                                    <?php if ($role->slug !== 'super_admin'): ?>
                                        <a href="/roles/<?= $role->id ?>/edit" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteRole(<?= $role->id ?>)" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small">Protected</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-user-shield fa-2x mb-2 d-block"></i>
                <p>No roles found. Create your first role.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteRole(id) {
    if (confirm('Are you sure you want to delete this role?')) {
        fetch('<?= BASE_URL ?>/roles/' + id, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Failed to delete role');
            }
        })
        .catch(error => {
            alert('An error occurred');
        });
    }
}
</script>