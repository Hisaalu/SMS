<!-- File: /app/Views/attendance/index.php -->
<div class="container-fluid px-0">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Attendance Overview</h4>
            <small class="text-muted">Manage daily registers, view session summaries, and track student attendance</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/attendance/take" class="btn btn-sm btn-primary">
                <i class="fas fa-clipboard-check me-1"></i> Take Attendance
            </a>
            <a href="<?= BASE_URL ?>/attendance/statuses" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-cog me-1"></i> Manage Statuses
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary p-3 rounded">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small text-uppercase">Today's Date</h6>
                            <h5 class="mb-0 fw-bold"><?= date('D, d M Y') ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-success bg-opacity-10 text-success p-3 rounded">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small text-uppercase">Registers Taken</h6>
                            <h5 class="mb-0 fw-bold"><?= htmlspecialchars($submittedCount ?? 0) ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-warning bg-opacity-10 text-warning p-3 rounded">
                            <i class="fas fa-edit fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small text-uppercase">Draft Registers</h6>
                            <h5 class="mb-0 fw-bold"><?= htmlspecialchars($draftCount ?? 0) ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-info bg-opacity-10 text-info p-3 rounded">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small text-uppercase">Avg. Attendance</h6>
                            <h5 class="mb-0 fw-bold"><?= htmlspecialchars($avgAttendance ?? '0%') ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Register Table Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <form method="GET" action="<?= BASE_URL ?>/attendance" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="date" name="attendance_date" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['attendance_date'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-md-3">
                    <select name="class_id" class="form-select form-select-sm">
                        <option value="">All Classes</option>
                        <?php if (!empty($classes)): ?>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['id'] ?>" <?= (($_GET['class_id'] ?? '') == $class['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($class['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Register Statuses</option>
                        <option value="submitted" <?= (($_GET['status'] ?? '') === 'submitted') ? 'selected' : '' ?>>Submitted</option>
                        <option value="draft" <?= (($_GET['status'] ?? '') === 'draft') ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-secondary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="<?= BASE_URL ?>/attendance" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Class / Stream</th>
                            <th>Session</th>
                            <th>Recorded By</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registers)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No attendance registers found for the selected criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registers as $register): ?>
                                <tr>
                                    <td><strong><?= date('M d, Y', strtotime($register['attendance_date'])) ?></strong></td>
                                    <td>
                                        <?= htmlspecialchars($register['class_name'] ?? 'N/A') ?> 
                                        <?= !empty($register['stream_name']) ? ' (' . htmlspecialchars($register['stream_name']) . ')' : '' ?>
                                    </td>
                                    <td><?= htmlspecialchars($register['session_name'] ?? 'Daily') ?></td>
                                    <td><?= htmlspecialchars($register['recorder_name'] ?? 'System') ?></td>
                                    <td>
                                        <?php if (($register['status'] ?? 'draft') === 'submitted'): ?>
                                            <span class="badge bg-success">Submitted</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/attendance/view/<?= $register['id'] ?>" class="btn btn-sm btn-outline-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/attendance/edit/<?= $register['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit Register">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>