<!-- File: /app/Views/dashboard/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Dashboard Overview</h4>
            <span class="text-muted small">
                <i class="fas fa-calendar-alt me-1"></i><?= htmlspecialchars($currentYear . ' - ' . $currentTerm, ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
        <div>
            <a href="<?= BASE_URL . '/attendance' ?>" class="btn btn-sm btn-primary shadow-sm"><i class="fas fa-check-double me-1"></i>Mark Attendance</a>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Students</span>
                        <h3 class="fw-bold my-1"><?= number_format($totalStudents ?? 0) ?></h3>
                        <small class="text-success"><i class="fas fa-arrow-up me-1"></i>Active enrollment</small>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="fas fa-user-graduate fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Teachers & Staff</span>
                        <h3 class="fw-bold my-1"><?= number_format($totalTeachers ?? 0) ?></h3>
                        <small class="text-muted">Assigned staff</small>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="fas fa-chalkboard-teacher fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Classes / Streams</span>
                        <h3 class="fw-bold my-1"><?= number_format($totalClasses ?? 0) ?></h3>
                        <small class="text-muted">Configured classes</small>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                        <i class="fas fa-school fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Today's Attendance</span>
                        <h3 class="fw-bold my-1"><?= $todayAttendance ?? 0 ?>%</h3>
                        <small class="<?= ($todayAttendance >= 75) ? 'text-success' : 'text-danger' ?>">
                            <i class="fas <?= ($todayAttendance >= 75) ? 'fa-check' : 'fa-exclamation-triangle' ?> me-1"></i>
                            <?= ($todayAttendance >= 75) ? 'Optimal rate' : 'Needs attention' ?>
                        </small>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                        <i class="fas fa-calendar-check fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflow Cards -->
    <div class="row g-3">
        <div class="col-lg-4 col-12">
            <div class="card border-0 shadow-sm p-3 h-100">
                <h6 class="fw-bold mb-3"><i class="fas fa-bolt me-2 text-warning"></i>Quick Shortcuts</h6>
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL . '/students/create' ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-user-plus me-2"></i>Register New Student</span>
                        <i class="fas fa-chevron-right small"></i>
                    </a>
                    <a href="<?= BASE_URL . '/attendance' ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-clipboard-check me-2"></i>Record Daily Attendance</span>
                        <i class="fas fa-chevron-right small"></i>
                    </a>
                    <a href="<?= BASE_URL . '/examinations/marks' ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-pen-nib me-2"></i>Enter Exam Results</span>
                        <i class="fas fa-chevron-right small"></i>
                    </a>
                    <a href="<?= BASE_URL . '/payments/collect' ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-money-bill-wave me-2"></i>Record Fee Payment</span>
                        <i class="fas fa-chevron-right small"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8 col-12">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-history me-2 text-primary"></i>Recent System Activity</h6>
                    <a href="#" class="text-decoration-none small text-primary">View All</a>
                </div>

                <?php if (!empty($recentActivities)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentActivities as $activity): ?>
                                    <tr>
                                        <td class="fw-semibold">
                                            <?= htmlspecialchars(($activity['first_name'] ?? 'System') . ' ' . ($activity['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($activity['action'] ?? 'LOG', ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td class="text-muted small"><?= htmlspecialchars($activity['description'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-muted small"><?= date('H:i, d M', strtotime($activity['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-muted text-center py-4 my-auto">
                        <i class="fas fa-inbox fa-2x mb-2 text-secondary opacity-50 d-block"></i>
                        <small>No recent activity logs recorded yet.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>