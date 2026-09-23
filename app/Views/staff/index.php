<!-- File: /app/Views/staff/index.php -->
<?php
$hasFilters = !empty($filters);
?>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Staff Directory</h4>
            <small class="text-muted">Manage school staff members</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/staff/categories" class="btn btn-sm btn-secondary">
                <i class="fas fa-tags me-1"></i> Categories
            </a>
            <a href="<?= BASE_URL ?>/staff/statuses" class="btn btn-sm btn-secondary">
                <i class="fas fa-toggle-on me-1"></i> Statuses
            </a>
            <a href="<?= BASE_URL ?>/staff/create" class="btn btn-sm btn-primary">
                <i class="fas fa-user-plus me-1"></i> Add Staff
            </a>
        </div>
    </div>

    <?php if ($flash = $this->getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/staff" class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Search by name, staff #, username, email..."
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"
                                    <?= (($filters['category_id'] ?? 0) == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status_id" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses as $st): ?>
                            <option value="<?= (int)$st['id'] ?>"
                                    <?= (($filters['status_id'] ?? 0) == $st['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($st['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= (int)$dept['id'] ?>"
                                    <?= (($filters['department_id'] ?? 0) == $dept['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <?php if ($hasFilters): ?>
                        <a href="<?= BASE_URL ?>/staff" class="btn btn-secondary btn-sm">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Staff #</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Category</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Account</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($staffMembers)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                    <?php if ($hasFilters): ?>
                                        No staff records found matching your filters.
                                        <br>
                                        <a href="<?= BASE_URL ?>/staff" class="btn btn-sm btn-secondary mt-2">Clear Filters</a>
                                    <?php else: ?>
                                        No staff records found.
                                        <br>
                                        <a href="<?= BASE_URL ?>/staff/create" class="btn btn-sm btn-primary mt-2">Add Staff Member</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($staffMembers as $row): ?>
                                <tr>
                                    <td><span class="fw-bold"><?= htmlspecialchars($row['staff_number'] ?? '-') ?></span></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['phone'] ?? $row['email'] ?? '') ?></small>
                                    </td>
                                    <td><span class="text-muted"><?= htmlspecialchars($row['username'] ?? '-') ?></span></td>
                                    <td><?= htmlspecialchars($row['category_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['department_name'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge <?= !empty($row['is_active_status']) ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= htmlspecialchars($row['status_name'] ?? 'Unknown') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['user_account_email'])): ?>
                                            <span class="badge bg-info-subtle" title="<?= htmlspecialchars($row['user_account_email']) ?>">Linked</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle">No Account</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <a class="dropdown-item" href="<?= BASE_URL ?>/staff/show?id=<?= (int)$row['id'] ?>">
                                                        <i class="fas fa-eye me-2"></i> View Profile
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="<?= BASE_URL ?>/staff/edit?id=<?= (int)$row['id'] ?>">
                                                        <i class="fas fa-edit me-2"></i> Edit Details
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item" href="<?= BASE_URL ?>/staff/show?id=<?= (int)$row['id'] ?>#status-management">
                                                        <i class="fas fa-user-cog me-2"></i> Change Status
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (!empty($staffMembers)): ?>
        <div class="d-flex justify-content-between align-items-center mt-2 text-muted small">
            <div>
                Showing <strong><?= count($staffMembers) ?></strong> staff member<?= count($staffMembers) === 1 ? '' : 's' ?>
                <?php if (!empty($filters['search'])): ?>
                    matching "<strong><?= htmlspecialchars($filters['search']) ?></strong>"
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>