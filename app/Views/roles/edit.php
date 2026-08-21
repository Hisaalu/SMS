<!-- File: /app/Views/roles/edit.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Role: <?= $role->name ?></h4>
        <a href="/roles" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger mb-2"><?= $flash ?></div>
    <?php endif; ?>

    <div class="card p-3">
        <form method="POST" action="<?= BASE_URL ?>/roles/<?= $role->id ?>">
            <input type="hidden" name="_method" value="PUT">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Role Name</label>
                    <input type="text" class="form-control" name="name" value="<?= $role->name ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="active" <?= $role->status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $role->status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="2"><?= $role->description ?></textarea>
                </div>
            </div>

            <hr class="my-3">

            <h6 class="mb-2">Permissions</h6>
            <div class="row">
                <?php foreach ($permissions as $module => $modulePermissions): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card p-2">
                            <h6 class="text-primary"><?= ucfirst($module) ?></h6>
                            <?php foreach ($modulePermissions as $permission): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $permission->id ?>" id="perm_<?= $permission->id ?>"
                                        <?= in_array($permission->id, $rolePermissions) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="perm_<?= $permission->id ?>">
                                        <?= $permission->name ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Update Role
            </button>
        </form>
    </div>
</div>