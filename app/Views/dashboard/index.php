<!-- File: /app/Views/dashboard/index.php -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Dashboard Overview</h4>
            <span class="text-muted small">
                <i class="fas fa-calendar-alt me-1"></i><?= htmlspecialchars($currentYear . ' - ' . $currentTerm, ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
        <div>
            <a href="<?= BASE_URL . '/attendance' ?>" class="btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-check-double me-1"></i>Mark Attendance
            </a>
        </div>
    </div>

    <!-- Metrics Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Students</span>
                        <h3 class="fw-bold my-1"><?= number_format($totalStudents ?? 0) ?></h3>
                        <small class="text-success"><i class="fas fa-user-check me-1"></i>Active Students</small>
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
                        <small class="text-muted"><i class="fas fa-users me-1"></i>Total Directory</small>
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
                        <small class="text-muted"><i class="fas fa-school me-1"></i>Configured Streams</small>
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
                        <h3 class="fw-bold my-1"><?= $todayAttendance ?>%</h3>
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

    <!-- Analytical Visualizations Row 1 -->
    <div class="row g-3 mb-4">
        <!-- Weekly Attendance Trend -->
        <div class="col-lg-8 col-12">
            <div class="card border-0 shadow-sm p-3 h-100">
                <h6 class="fw-bold mb-3"><i class="fas fa-chart-line text-primary me-2"></i>Weekly Attendance Trend (%)</h6>
                <div style="height: 250px;">
                    <canvas id="attendanceTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Male vs Female Demographics -->
        <div class="col-lg-4 col-12">
            <div class="card border-0 shadow-sm p-3 h-100">
                <h6 class="fw-bold mb-3"><i class="fas fa-venus-mars text-danger me-2"></i>Student Gender Ratio</h6>
                <div style="height: 250px;" class="d-flex justify-content-center align-items-center">
                    <canvas id="genderRatioChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytical Visualizations Row 2 -->
    <div class="row g-3 mb-4">
        <!-- Class Distribution -->
        <div class="col-lg-6 col-12">
            <div class="card border-0 shadow-sm p-3 h-100">
                <h6 class="fw-bold mb-3"><i class="fas fa-chart-bar text-success me-2"></i>Student Enrollment by Class</h6>
                <div style="height: 230px;">
                    <canvas id="classDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Staff Category Breakdown -->
        <div class="col-lg-6 col-12">
            <div class="card border-0 shadow-sm p-3 h-100">
                <h6 class="fw-bold mb-3"><i class="fas fa-user-tag text-warning me-2"></i>Staff Distribution by Category</h6>
                <div style="height: 230px;" class="d-flex justify-content-center align-items-center">
                    <canvas id="staffCategoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick System Shortcuts & Audit Logs -->
    <div class="row g-3">
        <div class="col-lg-4 col-12">
            <div class="card border-0 shadow-sm p-3 h-100">
                <h6 class="fw-bold mb-3"><i class="fas fa-bolt text-warning me-2"></i>Management Shortcuts</h6>
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL . '/student/categories' ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-tags me-2"></i>Student Categories</span>
                        <i class="fas fa-chevron-right small"></i>
                    </a>
                    <a href="<?= BASE_URL . '/staff/categories' ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-users-cog me-2"></i>Staff Categories</span>
                        <i class="fas fa-chevron-right small"></i>
                    </a>
                    <a href="<?= BASE_URL . '/staff/statuses' ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-toggle-on me-2"></i>Staff Statuses</span>
                        <i class="fas fa-chevron-right small"></i>
                    </a>
                    <a href="<?= BASE_URL . '/attendance/statuses' ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-list-check me-2"></i>Attendance Statuses</span>
                        <i class="fas fa-chevron-right small"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8 col-12">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-history text-primary me-2"></i>Recent System Activity</h6>
                    <a href="<?= BASE_URL . '/audit' ?>" class="text-decoration-none small text-primary">View All</a>
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

<script>
document.addEventListener("DOMContentLoaded", function () {
    // 1. Attendance Trend Line Chart
    new Chart(document.getElementById('attendanceTrendChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($attendanceTrend['labels']) ?>,
            datasets: [{
                label: 'Attendance %',
                data: <?= json_encode($attendanceTrend['data']) ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { min: 0, max: 100 } }
        }
    });

    // 2. Gender Ratio Donut Chart
    new Chart(document.getElementById('genderRatioChart'), {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female', 'Other'],
            datasets: [{
                data: [
                    <?= $genderData['male'] ?? 0 ?>,
                    <?= $genderData['female'] ?? 0 ?>,
                    <?= $genderData['other'] ?? 0 ?>
                ],
                backgroundColor: ['#0d6efd', '#dc3545', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // 3. Class Enrollment Bar Chart
    new Chart(document.getElementById('classDistributionChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($classDistribution['labels']) ?>,
            datasets: [{
                label: 'Students',
                data: <?= json_encode($classDistribution['data']) ?>,
                backgroundColor: '#198754'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    // 4. Staff Category Pie Chart
    new Chart(document.getElementById('staffCategoryChart'), {
        type: 'pie',
        data: {
            labels: <?= json_encode($staffCategories['labels']) ?>,
            datasets: [{
                data: <?= json_encode($staffCategories['data']) ?>,
                backgroundColor: ['#ffc107', '#0dcaf0', '#6f42c1', '#fd7e14', '#20c997']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
});
</script>