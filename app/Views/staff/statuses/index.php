<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Staff Statuses</h4>
        <small class="text-muted">Manage active, suspended, and employment states</small>
    </div>
    <div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStatusModal">
            <i class="fas fa-plus me-1"></i> Add Status
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
                        <th>Status Name</th>
                        <th>Code</th>
                        <th>Is Active</th>
                        <th>Allows Login</th>
                        <th>Staff Count</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($statuses)): ?>
                        <?php foreach ($statuses as $st): ?>
                        <tr>
                            <td><?= $st['display_order'] ?></td>
                            <td><strong><?= htmlspecialchars($st['name']) ?></strong></td>
                            <td><code><?= htmlspecialchars($st['code']) ?></code></td>
                            <td>
                                <span class="badge bg-<?= !empty($st['is_active_status']) ? 'success' : 'danger' ?>">
                                    <?= !empty($st['is_active_status']) ? 'Yes' : 'No' ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= !empty($st['allows_login']) ? 'info' : 'warning' ?>">
                                    <?= !empty($st['allows_login']) ? 'Yes' : 'No' ?>
                                </span>
                            </td>
                            <td><span class="badge bg-secondary"><?= $st['usage_count'] ?? 0 ?></span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary me-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editStatusModal<?= $st['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if (($st['usage_count'] ?? 0) == 0): ?>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteStatusModal<?= $st['id'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary" 
                                            disabled 
                                            title="Cannot delete status while assigned to staff members">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Edit Status Modal -->
                        <div class="modal fade" id="editStatusModal<?= $st['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="<?= BASE_URL ?>/staff/statuses/update" method="POST">
                                        <input type="hidden" name="id" value="<?= $st['id'] ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Staff Status</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="mb-3">
                                                <label class="form-label required">Status Name</label>
                                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($st['name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label required">Code</label>
                                                <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($st['code']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($st['description'] ?? '') ?></textarea>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="is_active_status" value="1" id="activeEdit<?= $st['id'] ?>" <?= !empty($st['is_active_status']) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="activeEdit<?= $st['id'] ?>">Is Active Status</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="allows_login" value="1" id="loginEdit<?= $st['id'] ?>" <?= !empty($st['allows_login']) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="loginEdit<?= $st['id'] ?>">Allows Login</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Display Order</label>
                                                <input type="number" name="display_order" class="form-control" value="<?= $st['display_order'] ?>">
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

                        <!-- Delete Status Modal -->
                        <div class="modal fade" id="deleteStatusModal<?= $st['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="<?= BASE_URL ?>/staff/statuses/delete" method="POST">
                                        <input type="hidden" name="id" value="<?= $st['id'] ?>">
                                        <div class="modal-header text-bg-danger">
                                            <h5 class="modal-title">Delete Staff Status</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-start py-4">
                                            <p class="mb-0">Are you sure you want to delete status <strong><?= htmlspecialchars($st['name']) ?></strong>?</p>
                                            <small class="text-muted">This action cannot be undone.</small>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Delete Status</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No staff statuses found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Status Modal -->
<div class="modal fade" id="addStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= BASE_URL ?>/staff/statuses/store" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add Staff Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Status Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Active, Suspended" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. ACTIVE, SUSPENDED" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description..."></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active_status" value="1" id="addActive" checked>
                                <label class="form-check-label" for="addActive">Is Active Status</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="allows_login" value="1" id="addLogin" checked>
                                <label class="form-check-label" for="addLogin">Allows Login</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="display_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Status</button>
                </div>
            </form>
        </div>
    </div>
</div>