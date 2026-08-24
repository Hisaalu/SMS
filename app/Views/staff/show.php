<!-- File: /app/Views/staff/show.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Staff Profile</h4>
            <small class="text-muted">View and manage staff member details</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/staff/edit?id=<?= $staff['id'] ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-edit me-1"></i> Edit Profile
            </a>
            <a href="<?= BASE_URL ?>/staff" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Directory
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <!-- Staff Info Card -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <?php if (!empty($staff['photo_path'])): ?>
                        <img src="<?= BASE_URL . '/' . htmlspecialchars($staff['photo_path']) ?>" class="rounded-circle mb-3 shadow-sm" width="120" height="120" style="object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:120px; height:120px; font-size:48px;">
                            <?= strtoupper(substr($staff['first_name'] ?? '', 0, 1) . substr($staff['last_name'] ?? '', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? '')) ?></h5>
                    <p class="text-muted mb-2"><?= htmlspecialchars($staff['position'] ?? 'No Position Assigned') ?></p>
                    <span class="badge <?= ($staff['is_active_status'] ?? 1) ? 'bg-success' : 'bg-secondary' ?>">
                        <?= htmlspecialchars($staff['status_name'] ?? 'Unknown') ?>
                    </span>
                    <hr class="my-3">
                    <div class="text-start small">
                        <p class="mb-2"><strong>Staff Number:</strong> <span class="float-end text-muted"><?= htmlspecialchars($staff['staff_number'] ?? '-') ?></span></p>
                        <p class="mb-2"><strong>Category:</strong> <span class="float-end text-muted"><?= htmlspecialchars($staff['category_name'] ?? '-') ?></span></p>
                        <p class="mb-2"><strong>Department:</strong> <span class="float-end text-muted"><?= htmlspecialchars($staff['department_name'] ?? '-') ?></span></p>
                        <p class="mb-2"><strong>Employment Type:</strong> <span class="float-end text-muted"><?= ucfirst(str_replace('_', ' ', $staff['employment_type'] ?? 'full_time')) ?></span></p>
                        <?php if (!empty($staff['employment_date'])): ?>
                            <p class="mb-0"><strong>Employment Date:</strong> <span class="float-end text-muted"><?= date('M d, Y', strtotime($staff['employment_date'])) ?></span></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side Details Section -->
        <div class="col-md-8">
            <!-- Status Update Form -->
            <div class="card mb-3" id="status-management">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-user-cog me-2"></i>Update Staff Status</h6>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/staff/change-status" method="POST">
                        <input type="hidden" name="staff_id" value="<?= $staff['id'] ?>">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">New Status</label>
                                <select name="staff_status_id" class="form-select form-select-sm" required>
                                    <?php foreach ($statuses as $st): ?>
                                        <option value="<?= $st['id'] ?>" <?= (($staff['staff_status_id'] ?? 0) == $st['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($st['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Effective Date</label>
                                <input type="date" name="effective_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Reason</label>
                                <input type="text" name="reason" class="form-control form-control-sm" placeholder="Optional notes...">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <i class="fas fa-save me-1"></i> Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Linking -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-key me-2"></i>System User Account</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($staff['user_username']) || !empty($staff['user_account_email'])): ?>
                        <div class="alert alert-success d-flex justify-content-between align-items-center mb-0">
                            <div>
                                <i class="fas fa-link me-2"></i>
                                <strong>Linked Account:</strong> 
                                <?= htmlspecialchars($staff['user_username'] ?? 'User') ?> 
                                (<?= htmlspecialchars($staff['user_account_email'] ?? $staff['user_email'] ?? 'No Email') ?>)
                            </div>
                            <form method="POST" action="<?= BASE_URL ?>/staff/unlink-account" onsubmit="return confirm('Unlink account from staff member?');">
                                <input type="hidden" name="staff_id" value="<?= $staff['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Unlink Account</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small mb-2">No system user account is linked to this staff record.</p>
                        <?php if (!empty($unlinkedUsers)): ?>
                            <form method="POST" action="<?= BASE_URL ?>/staff/link-account" class="row g-2 align-items-center">
                                <input type="hidden" name="staff_id" value="<?= $staff['id'] ?>">
                                <div class="col-md-8">
                                    <select name="user_id" class="form-select form-select-sm" required>
                                        <option value="">Select an existing user account to link...</option>
                                        <?php foreach ($unlinkedUsers as $u): ?>
                                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username'] . ' (' . $u['email'] . ')') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">
                                        <i class="fas fa-link me-1"></i> Link Account
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <span class="badge bg-light text-muted border">No Unlinked Users Available</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status History -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Status History Log</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Old Status</th>
                                    <th>New Status</th>
                                    <th>Reason</th>
                                    <th>Authorizer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($statusHistory)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">No status changes recorded.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($statusHistory as $sh): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($sh['effective_date'])) ?></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($sh['old_status_name'] ?? 'Initial') ?></span></td>
                                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($sh['new_status_name']) ?></span></td>
                                            <td><?= htmlspecialchars($sh['reason'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($sh['authorizer'] ?? 'System') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>