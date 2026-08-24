<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Staff Categories</h4>
        <small class="text-muted">Manage staff groupings and structural categories</small>
    </div>
    <div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus me-1"></i> Add Category
        </button>
        <a href="<?= BASE_URL ?>/staff" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Directory
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Staff Count</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= $cat['display_order'] ?></td>
                            <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                            <td><code><?= htmlspecialchars($cat['code']) ?></code></td>
                            <td><?= htmlspecialchars($cat['description'] ?? '-') ?></td>
                            <td><span class="badge bg-secondary"><?= $cat['usage_count'] ?? 0 ?></span></td>
                            <td>
                                <span class="badge bg-<?= ($cat['status'] ?? 'active') === 'active' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($cat['status'] ?? 'active') ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editCategoryModal<?= $cat['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Edit Category Modal -->
                        <div class="modal fade" id="editCategoryModal<?= $cat['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="<?= BASE_URL ?>/staff/categories/update" method="POST">
                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Staff Category</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="mb-3">
                                                <label class="form-label required">Category Name</label>
                                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($cat['name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label required">Code</label>
                                                <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($cat['code']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($cat['description'] ?? '') ?></textarea>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Display Order</label>
                                                    <input type="number" name="display_order" class="form-control" value="<?= $cat['display_order'] ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="active" <?= ($cat['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                                        <option value="inactive" <?= ($cat['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No staff categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= BASE_URL ?>/staff/categories/store" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add Staff Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Teaching Staff" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. TEACHING" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>