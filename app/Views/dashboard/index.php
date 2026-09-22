<!-- File: /app/Views/dashboard/index.php -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
$termName = 'Term 1';
if (isset($currentTerm)) {
    if (is_array($currentTerm)) {
        $termName = $currentTerm['term'] ?? $currentTerm['name'] ?? 'Term 1';
    } else {
        $termName = (string)$currentTerm;
    }
}

$yearName = date('Y');
if (isset($currentYear) && !is_array($currentYear)) {
    $yearName = (string)$currentYear;
} elseif (isset($currentTerm) && is_array($currentTerm)) {
    $yearName = (string)($currentTerm['year'] ?? $currentTerm['year_name'] ?? date('Y'));
}

$yearLabel     = htmlspecialchars($yearName . ' - ' . $termName, ENT_QUOTES, 'UTF-8');
$todayRate     = (int)($todayAttendance ?? 0);
$activityList  = is_array($recentActivities ?? null) ? $recentActivities : [];
$trendLabels   = $attendanceTrend['labels'] ?? [];
$trendData     = $attendanceTrend['data']   ?? [];
$genderData    = is_array($genderData ?? null) ? $genderData : ['male' => 0, 'female' => 0, 'other' => 0];
$classDist     = $classDistribution ?? ['labels' => [], 'data' => []];
$staffDist     = $staffCategories   ?? ['labels' => [], 'data' => []];

$totalEnrolled = (int)($genderData['male'] ?? 0) + (int)($genderData['female'] ?? 0) + (int)($genderData['other'] ?? 0);
$maleCount     = (int)($genderData['male']   ?? 0);
$femaleCount   = (int)($genderData['female'] ?? 0);
$malePct       = $totalEnrolled > 0 ? round(($maleCount   / $totalEnrolled) * 100) : 0;
$femalePct     = $totalEnrolled > 0 ? round(($femaleCount / $totalEnrolled) * 100) : 0;

$attendanceState  = $todayRate >= 75 ? 'success' : ($todayRate >= 50 ? 'warning' : 'danger');
$attendanceLabel  = $todayRate >= 75 ? 'Optimal' : ($todayRate >= 50 ? 'Fair' : 'Critical');
$attendanceIcon   = $todayRate >= 75 ? 'fa-check-circle' : ($todayRate >= 50 ? 'fa-exclamation-circle' : 'fa-times-circle');

$avgTrend  = !empty($trendData) ? round(array_sum($trendData) / count($trendData)) : 0;
$bestTrend = !empty($trendData) ? max($trendData) : 0;
$worstTrend = !empty($trendData) ? min(array_filter($trendData, fn($v) => $v > 0)) : 0;

$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$firstName = htmlspecialchars($user->first_name ?? $user->username ?? 'Administrator', ENT_QUOTES, 'UTF-8');
?>

<style>
    .metric-card {
        position: relative;
        overflow: hidden;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08) !important;
    }
    .metric-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: var(--accent, #0d6efd);
    }
    .metric-icon {
        width: 48px; height: 48px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 12px;
    }
    .metric-trend {
        font-size: 0.7rem;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 600;
    }
    .shortcut-btn {
        transition: all 0.15s ease;
        border-left: 3px solid transparent;
    }
    .shortcut-btn:hover {
        border-left-color: #0d6efd;
        background-color: rgba(13,110,253,0.04);
        transform: translateX(2px);
    }
    .activity-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .stat-mini {
        padding: 0.5rem 0;
    }
    .stat-mini .value {
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .stat-mini .label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #6c757d;
    }
    .hero-strip {
        background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
        border-radius: 0.75rem;
        color: #fff;
        padding: 1.25rem 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .hero-strip::after {
        content: '';
        position: absolute;
        right: -40px; top: -40px;
        width: 160px; height: 160px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }
    .hero-strip::before {
        content: '';
        position: absolute;
        right: 60px; bottom: -30px;
        width: 100px; height: 100px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
    }
    .hero-strip .badge {
        background: rgba(255,255,255,0.2);
        color: #fff;
        font-weight: 500;
    }
    @media (max-width: 767px) {
        .hero-strip { padding: 1rem; }
        .metric-icon { width: 40px; height: 40px; }
    }
</style>

<div class="container-fluid px-0">

    <div class="hero-strip mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 position-relative" style="z-index: 1;">
            <div>
                <div class="small opacity-75"><?= $greeting ?>,</div>
                <h4 class="fw-bold mb-1"><?= $firstName ?></h4>
                <div class="small opacity-75">
                    <i class="fas fa-calendar-alt me-1"></i><?= $yearLabel ?>
                    <span class="mx-2">•</span>
                    <i class="fas fa-clock me-1"></i><?= date('l, d M Y') ?>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL . '/attendance/take' ?>" class="btn btn-light btn-sm shadow-sm">
                    <i class="fas fa-check-double me-1"></i>Mark Attendance
                </a>
                <a href="<?= BASE_URL . '/student/create' ?>" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-user-plus me-1"></i>Admit Student
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3 h-100 metric-card" style="--accent: #0d6efd;">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="text-muted small fw-semibold text-uppercase">Total Students</div>
                        <h3 class="fw-bold my-2 mb-1"><?= number_format((int)($totalStudents ?? 0)) ?></h3>
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="metric-trend bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-user-check me-1"></i>Active
                            </span>
                            <span class="text-muted">
                                <i class="fas fa-venus me-1 text-danger"></i><?= $femaleCount ?>
                                <i class="fas fa-mars ms-2 me-1 text-primary"></i><?= $maleCount ?>
                            </span>
                        </div>
                    </div>
                    <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-user-graduate fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3 h-100 metric-card" style="--accent: #198754;">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="text-muted small fw-semibold text-uppercase">Staff & Teachers</div>
                        <h3 class="fw-bold my-2 mb-1"><?= number_format((int)($totalTeachers ?? 0)) ?></h3>
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="metric-trend bg-success bg-opacity-10 text-success">
                                <i class="fas fa-users me-1"></i>On payroll
                            </span>
                            <span class="text-muted">Staff directory</span>
                        </div>
                    </div>
                    <div class="metric-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-chalkboard-teacher fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3 h-100 metric-card" style="--accent: #ffc107;">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="text-muted small fw-semibold text-uppercase">Classes</div>
                        <h3 class="fw-bold my-2 mb-1"><?= number_format((int)($totalClasses ?? 0)) ?></h3>
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="metric-trend bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-school me-1"></i>Configured
                            </span>
                            <?php if (!empty($classDist['data'])): ?>
                                <span class="text-muted">
                                    <?= count($classDist['data']) ?> with students
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-school fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3 h-100 metric-card" style="--accent: <?= $attendanceState === 'success' ? '#0dcaf0' : ($attendanceState === 'warning' ? '#ffc107' : '#dc3545') ?>;">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="text-muted small fw-semibold text-uppercase">Today's Attendance</div>
                        <h3 class="fw-bold my-2 mb-1"><?= $todayRate ?>%</h3>
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="metric-trend bg-<?= $attendanceState ?> bg-opacity-10 text-<?= $attendanceState ?>">
                                <i class="fas <?= $attendanceIcon ?> me-1"></i><?= $attendanceLabel ?>
                            </span>
                            <span class="text-muted">7-day avg <?= $avgTrend ?>%</span>
                        </div>
                    </div>
                    <div class="metric-icon bg-<?= $attendanceState ?> bg-opacity-10 text-<?= $attendanceState ?>">
                        <i class="fas fa-calendar-check fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h6 class="fw-bold mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Weekly Attendance Trend</h6>
                        <small class="text-muted">Last 7 days performance</small>
                    </div>
                    <div class="d-flex gap-3 small">
                        <div class="stat-mini text-end">
                            <div class="value text-success"><?= $bestTrend ?>%</div>
                            <div class="label">Best</div>
                        </div>
                        <div class="stat-mini text-end">
                            <div class="value text-danger"><?= $worstTrend ?>%</div>
                            <div class="label">Lowest</div>
                        </div>
                        <div class="stat-mini text-end">
                            <div class="value text-primary"><?= $avgTrend ?>%</div>
                            <div class="label">Average</div>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div style="height: 260px;">
                        <canvas id="attendanceTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-venus-mars text-danger me-2"></i>Gender Distribution</h6>
                    <small class="text-muted"><?= number_format($totalEnrolled) ?> active students</small>
                </div>
                <div class="card-body pt-0 d-flex flex-column">
                    <div style="height: 200px;">
                        <canvas id="genderRatioChart"></canvas>
                    </div>
                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <div class="text-center p-2 rounded bg-primary bg-opacity-10">
                                <div class="fw-bold text-primary"><?= $malePct ?>%</div>
                                <div class="small text-muted"><i class="fas fa-mars me-1"></i>Male</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 rounded bg-danger bg-opacity-10">
                                <div class="fw-bold text-danger"><?= $femalePct ?>%</div>
                                <div class="small text-muted"><i class="fas fa-venus me-1"></i>Female</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-bar text-success me-2"></i>Enrollment by Class</h6>
                    <small class="text-muted">Student count per class</small>
                </div>
                <div class="card-body pt-0">
                    <div style="height: 240px;">
                        <canvas id="classDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-user-tag text-warning me-2"></i>Staff by Category</h6>
                    <small class="text-muted">Workforce breakdown</small>
                </div>
                <div class="card-body pt-0">
                    <div style="height: 240px;">
                        <canvas id="staffCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL . '/students/create' ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center shortcut-btn">
                            <span><i class="fas fa-user-plus me-2"></i>Admit New Student</span>
                            <i class="fas fa-chevron-right small"></i>
                        </a>
                        <a href="<?= BASE_URL . '/staff/create' ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center shortcut-btn">
                            <span><i class="fas fa-user-tie me-2"></i>Register Staff</span>
                            <i class="fas fa-chevron-right small"></i>
                        </a>
                        <a href="<?= BASE_URL . '/marks/entry' ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center shortcut-btn">
                            <span><i class="fas fa-pen-to-square me-2"></i>Enter Marks</span>
                            <i class="fas fa-chevron-right small"></i>
                        </a>
                        <a href="<?= BASE_URL . '/academic/years' ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center shortcut-btn">
                            <span><i class="fas fa-calendar-plus me-2"></i>Manage Academic Years</span>
                            <i class="fas fa-chevron-right small"></i>
                        </a>
                        <a href="<?= BASE_URL . '/grading/systems' ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center shortcut-btn">
                            <span><i class="fas fa-award me-2"></i>Grading Systems</span>
                            <i class="fas fa-chevron-right small"></i>
                        </a>
                    </div>

                    <hr class="my-3">

                    <div class="text-muted small fw-semibold text-uppercase mb-2">Configuration</div>
                    <div class="d-flex flex-wrap gap-1">
                        <a href="<?= BASE_URL . '/student/categories' ?>" class="btn btn-sm btn-light border" title="Student Categories">
                            <i class="fas fa-tags me-1"></i>Categories
                        </a>
                        <a href="<?= BASE_URL . '/staff/categories' ?>" class="btn btn-sm btn-light border" title="Staff Categories">
                            <i class="fas fa-users-cog me-1"></i>Staff Cat.
                        </a>
                        <a href="<?= BASE_URL . '/attendance/statuses' ?>" class="btn btn-sm btn-light border" title="Attendance Statuses">
                            <i class="fas fa-list-check me-1"></i>Att. Status
                        </a>
                        <a href="<?= BASE_URL . '/attendance/sessions' ?>" class="btn btn-sm btn-light border" title="Attendance Sessions">
                            <i class="fas fa-clock me-1"></i>Sessions
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0"><i class="fas fa-history text-primary me-2"></i>Recent Activity</h6>
                        <small class="text-muted">Latest system changes</small>
                    </div>
                    <a href="<?= BASE_URL . '/audit' ?>" class="text-decoration-none small">View All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                <div class="card-body pt-0">
                    <?php if (!empty($activityList)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 32%;">User</th>
                                        <th style="width: 20%;">Action</th>
                                        <th style="width: 33%;">Details</th>
                                        <th style="width: 15%;" class="text-end">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activityList as $activity): ?>
                                        <?php
                                            $actionName = (string)($activity['action'] ?? 'LOG');
                                            $badgeClass = 'bg-secondary';
                                            if (stripos($actionName, 'creat') !== false)  $badgeClass = 'bg-success';
                                            elseif (stripos($actionName, 'updat') !== false) $badgeClass = 'bg-primary';
                                            elseif (stripos($actionName, 'delet') !== false) $badgeClass = 'bg-danger';
                                            elseif (stripos($actionName, 'publish') !== false) $badgeClass = 'bg-info';
                                            elseif (stripos($actionName, 'login') !== false) $badgeClass = 'bg-secondary';
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="activity-dot bg-primary"></span>
                                                    <span class="fw-semibold small">
                                                        <?= htmlspecialchars(trim(($activity['first_name'] ?? 'System') . ' ' . ($activity['last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($actionName, ENT_QUOTES, 'UTF-8') ?></span></td>
                                            <td class="text-muted small"><?= htmlspecialchars($activity['description'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="text-muted small text-end">
                                                <?= !empty($activity['created_at']) ? date('H:i, d M', strtotime($activity['created_at'])) : '-' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-muted text-center py-5">
                            <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-25 d-block"></i>
                            <div class="small">No recent activity recorded yet.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
    Chart.defaults.color = '#6c757d';

    new Chart(document.getElementById('attendanceTrendChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($trendLabels) ?>,
            datasets: [{
                label: 'Attendance %',
                data: <?= json_encode($trendData) ?>,
                borderColor: '#0d6efd',
                backgroundColor: (ctx) => {
                    const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 250);
                    g.addColorStop(0, 'rgba(13, 110, 253, 0.25)');
                    g.addColorStop(1, 'rgba(13, 110, 253, 0)');
                    return g;
                },
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#0d6efd',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0, max: 100, ticks: { callback: v => v + '%' }, grid: { color: '#f1f3f5' } },
                x: { grid: { display: false } }
            }
        }
    });

    new Chart(document.getElementById('genderRatioChart'), {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female', 'Other'],
            datasets: [{
                data: [
                    <?= (int)($genderData['male']   ?? 0) ?>,
                    <?= (int)($genderData['female'] ?? 0) ?>,
                    <?= (int)($genderData['other']  ?? 0) ?>
                ],
                backgroundColor: ['#0d6efd', '#dc3545', '#adb5bd'],
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            }
        }
    });

    new Chart(document.getElementById('classDistributionChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($classDist['labels'] ?? []) ?>,
            datasets: [{
                label: 'Students',
                data: <?= json_encode($classDist['data'] ?? []) ?>,
                backgroundColor: '#198754',
                borderRadius: 6,
                maxBarThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f3f5' } },
                x: { grid: { display: false } }
            }
        }
    });

    new Chart(document.getElementById('staffCategoryChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($staffDist['labels'] ?? []) ?>,
            datasets: [{
                data: <?= json_encode($staffDist['data'] ?? []) ?>,
                backgroundColor: ['#ffc107', '#0dcaf0', '#6f42c1', '#fd7e14', '#20c997', '#d63384'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 10, padding: 10, font: { size: 11 } } }
            }
        }
    });
});
</script>