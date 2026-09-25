<!-- File: /app/Views/staff/show.php -->
<?php
$formatDate = static function ($value, string $format = 'M d, Y'): string {
    if (empty($value) || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
        return '-';
    }
    $ts = strtotime((string) $value);
    return $ts !== false ? date($format, $ts) : '-';
};

$hasPhoto = !empty($staff['photo_path']) && file_exists(ROOT_PATH . '/public/' . $staff['photo_path']);
$initials = strtoupper(substr($staff['first_name'] ?? '', 0, 1) . substr($staff['last_name'] ?? '', 0, 1));
$isActive = !empty($staff['is_active_status']);
?>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">Staff Profile</h4>
            <small class="text-muted">View and manage staff member details</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/staff/edit?id=<?= (int)$staff['id'] ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-edit me-1"></i> Edit Profile
            </a>
            <a href="<?= BASE_URL ?>/staff" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Directory
            </a>
        </div>
    </div>

    <div class="row g-3">

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <?php if ($hasPhoto): ?>
                        <img src="<?= BASE_URL . '/' . htmlspecialchars($staff['photo_path']) ?>"
                             class="rounded-circle mb-3 shadow-sm"
                             width="120" height="120" style="object-fit:cover;" alt="Staff Photo">
                    <?php else: ?>
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm text-white"
                             style="width:120px; height:120px; font-size:42px; font-weight:600; background: var(--accent-color);">
                            <?= $initials !== '' ? $initials : '<i class="fas fa-user"></i>' ?>
                        </div>
                    <?php endif; ?>

                    <h5 class="fw-bold mb-1">
                        <?= htmlspecialchars(trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? ''))) ?>
                    </h5>
                    <p class="text-muted mb-2">
                        <?= htmlspecialchars($staff['position'] ?? 'No Position Assigned') ?>
                    </p>

                    <span class="badge <?= $isActive ? 'bg-success-subtle' : 'bg-secondary-subtle' ?> px-3 py-2">
                        <i class="fas fa-circle me-1" style="font-size:8px;"></i>
                        <?= htmlspecialchars($staff['status_name'] ?? 'Unknown') ?>
                    </span>

                    <hr class="my-4">

                    <div class="text-start">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted small" style="width:45%;">Staff Number</td>
                                <td class="fw-semibold text-end"><?= htmlspecialchars($staff['staff_number'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted small">Category</td>
                                <td class="fw-semibold text-end"><?= htmlspecialchars($staff['category_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted small">Department</td>
                                <td class="fw-semibold text-end"><?= htmlspecialchars($staff['department_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted small">Employment Type</td>
                                <td class="fw-semibold text-end">
                                    <?= ucfirst(str_replace('_', ' ', $staff['employment_type'] ?? 'full_time')) ?>
                                </td>
                            </tr>
                            <?php if (!empty($staff['employment_date']) && $staff['employment_date'] !== '0000-00-00'): ?>
                            <tr>
                                <td class="text-muted small">Employed Since</td>
                                <td class="fw-semibold text-end"><?= $formatDate($staff['employment_date']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($staff['phone'])): ?>
                            <tr>
                                <td class="text-muted small">Phone</td>
                                <td class="fw-semibold text-end"><?= htmlspecialchars($staff['phone']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($staff['email'])): ?>
                            <tr>
                                <td class="text-muted small">Email</td>
                                <td class="fw-semibold text-end text-truncate small" style="max-width:150px;">
                                    <?= htmlspecialchars($staff['email']) ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-user-shield me-2" style="color: var(--accent-color);"></i>System Login Account
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($staff['user_id']) && !empty($staff['user_username'])): ?>
                        <div class="alert alert-success d-flex justify-content-between align-items-center mb-0 flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fas fa-link fa-lg"></i>
                                <div>
                                    <div class="fw-bold">Linked Account Active</div>
                                    <div class="small">
                                        <strong>Username:</strong> <?= htmlspecialchars($staff['user_username']) ?>
                                        &nbsp;|&nbsp;
                                        <strong>Email:</strong> <?= htmlspecialchars($staff['user_account_email'] ?? '-') ?>
                                        &nbsp;|&nbsp;
                                        <strong>Roles:</strong> <?= htmlspecialchars($staff['user_role_names'] ?? '-') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="<?= BASE_URL ?>/users/<?= (int)$staff['user_id'] ?>/edit" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit me-1"></i> Edit Account
                                </a>
                                <form method="POST" action="<?= BASE_URL ?>/staff/unlink-account" class="d-inline"
                                      onsubmit="return confirm('Unlink the login account from this staff member?');">
                                    <input type="hidden" name="staff_id" value="<?= (int)$staff['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-unlink me-1"></i> Unlink
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light d-flex justify-content-between align-items-center mb-0 flex-wrap gap-3">
                            <div>
                                <i class="fas fa-user-slash me-2"></i>
                                No login account is linked to this staff member.
                            </div>
                            <?php if (!empty($unlinkedUsers)): ?>
                                <form method="POST" action="<?= BASE_URL ?>/staff/link-account" class="d-flex gap-2 flex-wrap">
                                    <input type="hidden" name="staff_id" value="<?= (int)$staff['id'] ?>">
                                    <select name="user_id" class="form-select form-select-sm" required style="min-width:240px;">
                                        <option value="">Select user to link...</option>
                                        <?php foreach ($unlinkedUsers as $u): ?>
                                            <option value="<?= (int)$u['id'] ?>">
                                                <?= htmlspecialchars($u['username'] . ' (' . $u['email'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-link me-1"></i> Link
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle">No unlinked users available</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-3" id="status-management">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-user-cog me-2" style="color: var(--accent-color);"></i>Update Staff Status
                    </h6>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/staff/change-status" method="POST">
                        <input type="hidden" name="staff_id" value="<?= (int)$staff['id'] ?>">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">New Status</label>
                                <select name="staff_status_id" class="form-select form-select-sm" required>
                                    <?php foreach ($statuses as $st): ?>
                                        <option value="<?= (int)$st['id'] ?>"
                                                <?= (($staff['staff_status_id'] ?? 0) == $st['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($st['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Effective Date</label>
                                <input type="date" name="effective_date" class="form-control form-control-sm"
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Reason</label>
                                <input type="text" name="reason" class="form-control form-control-sm"
                                       placeholder="Optional notes...">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <i class="fas fa-save me-1"></i> Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-chalkboard-teacher me-2" style="color: var(--accent-color);"></i>Teaching Assignments
                    </h6>
                    <a href="<?= BASE_URL ?>/staff/edit?id=<?= (int)$staff['id'] ?>#assignments"
                       class="btn btn-sm btn-secondary">
                        <i class="fas fa-edit me-1"></i> Manage
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($teacherAssignments)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Class</th>
                                        <th>Stream</th>
                                        <th>Subject</th>
                                        <th>Type</th>
                                        <th class="pe-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teacherAssignments as $a): ?>
                                        <tr>
                                            <td class="ps-3"><?= htmlspecialchars($a['class_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($a['stream_name'] ?? 'All') ?></td>
                                            <td><?= htmlspecialchars($a['subject_name'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge bg-info-subtle">
                                                    <?= htmlspecialchars($a['assignment_type_name'] ?? '-') ?>
                                                </span>
                                            </td>
                                            <td class="pe-3">
                                                <span class="badge <?= ($a['status'] ?? '') === 'active' ? 'bg-success-subtle' : 'bg-secondary-subtle' ?>">
                                                    <?= htmlspecialchars($a['status'] ?? '-') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-chalkboard fa-2x mb-2 d-block opacity-50"></i>
                            <div class="small">No teaching assignments yet.</div>
                            <a href="<?= BASE_URL ?>/staff/edit?id=<?= (int)$staff['id'] ?>#assignments"
                               class="btn btn-sm btn-secondary mt-2">
                                <i class="fas fa-plus me-1"></i> Add First Assignment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-history me-2" style="color: var(--accent-color);"></i>Status History Log
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($statusHistory)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-clock fa-2x mb-2 d-block opacity-50"></i>
                            <div class="small">No status changes recorded.</div>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Date</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Reason</th>
                                        <th class="pe-3">By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($statusHistory as $sh): ?>
                                        <tr>
                                            <td class="ps-3"><?= $formatDate($sh['effective_date']) ?></td>
                                            <td>
                                                <span class="badge bg-secondary-subtle">
                                                    <?= htmlspecialchars($sh['old_status_name'] ?? 'Initial') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle">
                                                    <?= htmlspecialchars($sh['new_status_name']) ?>
                                                </span>
                                            </td>
                                            <td class="small"><?= htmlspecialchars($sh['reason'] ?? '-') ?></td>
                                            <td class="pe-3 small"><?= htmlspecialchars($sh['authorizer'] ?? 'System') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>