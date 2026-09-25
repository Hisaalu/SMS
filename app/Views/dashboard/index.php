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

$yearLabel             = htmlspecialchars($yearName . ' - ' . $termName, ENT_QUOTES, 'UTF-8');
$todayRate             = (int)($todayAttendance ?? 0);
$activityList          = is_array($recentActivities ?? null) ? $recentActivities : [];
$recentStudentList     = is_array($recentlyAddedStudents ?? null) ? $recentlyAddedStudents : [];
$trendLabels           = $attendanceTrend['labels'] ?? [];
$trendData             = $attendanceTrend['data']   ?? [];
$genderData            = is_array($genderData ?? null) ? $genderData : ['male' => 0, 'female' => 0, 'other' => 0];
$classDist             = $classDistribution ?? ['labels' => [], 'male' => [], 'female' => []];
$staffDist             = $staffCategories   ?? ['labels' => [], 'data' => []];
$academicTermsList     = is_array($academicTermsList ?? null) ? $academicTermsList : [];

$totalEnrolled = (int)($genderData['male'] ?? 0) + (int)($genderData['female'] ?? 0) + (int)($genderData['other'] ?? 0);
$maleCount     = (int)($genderData['male']   ?? 0);
$femaleCount   = (int)($genderData['female'] ?? 0);
$malePct       = $totalEnrolled > 0 ? round(($maleCount   / $totalEnrolled) * 100) : 0;
$femalePct     = $totalEnrolled > 0 ? round(($femaleCount / $totalEnrolled) * 100) : 0;

$attendanceState  = $todayRate >= 75 ? 'success' : ($todayRate >= 50 ? 'warning' : 'danger');
$attendanceLabel  = $todayRate >= 75 ? 'Optimal' : ($todayRate >= 50 ? 'Fair' : 'Critical');
$attendanceIcon   = $todayRate >= 75 ? 'fa-check-circle' : ($todayRate >= 50 ? 'fa-exclamation-circle' : 'fa-times-circle');

$avgTrend   = !empty($trendData) ? round(array_sum($trendData) / count($trendData)) : 0;
$bestTrend  = !empty($trendData) ? max($trendData) : 0;
$worstTrend = !empty($trendData) ? min(array_filter($trendData, fn($v) => $v > 0)) : 0;

$hour      = (int)date('H');
$greeting  = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$firstName = htmlspecialchars($user->first_name ?? $user->username ?? 'Administrator', ENT_QUOTES, 'UTF-8');
?>

<style>
    .metric-card {
        position: relative;
        overflow: hidden;
        background: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: 0.875rem;
        box-shadow: 0 1px 3px 0 rgba(0,0,0,0.03), 0 1px 2px -1px rgba(0,0,0,0.03);
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08), 0 8px 10px -6px rgba(0,0,0,0.04);
    }
    .metric-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3.5px;
        background: var(--metric-accent, var(--accent-color));
    }
    .metric-icon {
        width: 48px; height: 48px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 14px;
    }
    .metric-trend {
        font-size: 0.7rem;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 600;
    }

    .dash-card {
        background: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: 0.875rem;
        box-shadow: 0 1px 3px 0 rgba(0,0,0,0.02);
        transition: box-shadow 0.2s ease;
    }
    .dash-card:hover {
        box-shadow: 0 4px 12px 0 rgba(0,0,0,0.04);
    }
    .dash-card .card-header {
        background: var(--surface-color);
        border-bottom: 1px solid var(--border-color);
        color: var(--text-color);
        border-top-left-radius: 0.875rem !important;
        border-top-right-radius: 0.875rem !important;
    }
    .dash-card .card-body {
        color: var(--text-color);
    }
    .dash-card h6 {
        color: var(--text-color);
    }
    .dash-card small,
    .dash-card .text-muted {
        color: var(--text-muted) !important;
    }

    .activity-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
        background: var(--accent-color);
    }

    .stat-mini { padding: 0.5rem 0; }
    .stat-mini .value {
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .stat-mini .label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: var(--text-muted);
    }

    .hero-strip {
        background: var(--accent-color);
        border-radius: 1rem;
        color: #fff;
        padding: 1.5rem 2rem;
        position: relative;
        box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.25);
    }
    .hero-bg-shapes {
        position: absolute;
        inset: 0;
        overflow: hidden;
        border-radius: 1rem;
        pointer-events: none;
    }
    .hero-bg-shapes::after {
        content: '';
        position: absolute;
        right: -30px; top: -30px;
        width: 150px; height: 150px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }
    .hero-bg-shapes::before {
        content: '';
        position: absolute;
        right: 80px; bottom: -40px;
        width: 120px; height: 120px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    .hero-strip .badge {
        background: rgba(255,255,255,0.2);
        color: #fff;
        font-weight: 500;
        backdrop-filter: blur(4px);
    }

    .activity-table {
        color: var(--text-color);
        margin-bottom: 0;
    }
    .activity-table thead th {
        background: var(--background-color);
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-color);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        padding: 0.75rem 1rem;
    }
    .activity-table tbody tr {
        border-color: var(--border-color);
    }
    .activity-table tbody td {
        color: var(--text-color);
        border-color: var(--border-color);
        vertical-align: middle;
        padding: 0.85rem 1rem;
    }
    .activity-table tbody tr:hover {
        background: rgba(37, 99, 235, 0.03);
    }

    .chart-container {
        position: relative;
        width: 100%;
    }

    .chart-empty-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: var(--surface-color);
        color: var(--text-muted);
        font-size: 0.85rem;
        z-index: 5;
        border-radius: 0.5rem;
    }

    @media (max-width: 767px) {
        .hero-strip { padding: 1.25rem 1rem; }
        .metric-icon { width: 40px; height: 40px; }
    }
</style>

<div class="container-fluid px-0">

    <div class="hero-strip mb-4">
        <div class="hero-bg-shapes"></div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 position-relative" style="z-index: 2;">
            <div>
                <div class="small opacity-75 fw-medium"><?= $greeting ?>,</div>
                <h3 class="fw-bold mb-1 text-white"><?= $firstName ?></h3>
                <div class="small opacity-75 d-flex align-items-center flex-wrap gap-2">
                    <span><i class="fas fa-calendar-alt me-1"></i><?= $yearLabel ?></span>
                    <span>•</span>
                    <span><i class="fas fa-clock me-1"></i><?= date('l, d M Y') ?></span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle shadow-sm text-dark bg-white border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-filter text-dark me-1"></i> <span class="text-dark"><?= $yearLabel ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="font-size: 0.85rem; max-height: 280px; overflow-y: auto; z-index: 1050;">
                        <li><h6 class="dropdown-header text-uppercase fs-xs">Academic Filter</h6></li>
                        <?php if (!empty($academicTermsList)): ?>
                            <?php foreach ($academicTermsList as $termItem): ?>
                                <?php 
                                    $itemYear = $termItem['year_name'] ?? '';
                                    $itemTerm = $termItem['term_name'] ?? '';
                                    $itemLabel = $itemYear . ' - ' . $itemTerm;
                                    $isActive = ($itemYear === $yearName && $itemTerm === $termName) || (!empty($termItem['is_current']) && empty($yearName));
                                    $filterUrl = BASE_URL . '/dashboard?year=' . urlencode($itemYear) . '&term=' . urlencode($itemTerm);
                                ?>
                                <li>
                                    <a class="dropdown-item <?= $isActive ? 'active' : '' ?>" href="<?= $filterUrl ?>">
                                        <?= htmlspecialchars($itemLabel, ENT_QUOTES, 'UTF-8') ?>
                                        <?php if (!empty($termItem['is_current'])): ?>
                                            <span class="badge bg-primary ms-1" style="font-size: 0.65rem;">Current</span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><a class="dropdown-item active" href="#"><?= $yearLabel ?> (Current)</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL . '/academic/years' ?>"><i class="fas fa-cog me-2 text-muted"></i>Manage Terms & Years</a></li>
                    </ul>
                </div>
                <a href="<?= BASE_URL . '/attendance/take' ?>" class="btn btn-light btn-sm shadow-sm fw-semibold text-dark">
                    <i class="fas fa-check-double text-dark me-1"></i>Mark Attendance
                </a>
                <a href="<?= BASE_URL . '/student/create' ?>" class="btn btn-outline-light btn-sm fw-semibold">
                    <i class="fas fa-user-plus me-1"></i>Admit Student
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="metric-card p-3 h-100" style="--metric-accent: var(--accent-color);">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="text-muted small fw-bold text-uppercase tracking-wide">Total Students</div>
                        <h3 class="fw-bold my-2 mb-1"><?= number_format((int)($totalStudents ?? 0)) ?></h3>
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="metric-trend bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-user-check me-1"></i>Active
                            </span>
                            <span class="text-muted">
                                <i class="fas fa-venus me-1 text-danger"></i><?= $femaleCount ?>
                                <i class="fas fa-mars ms-1 me-1 text-primary"></i><?= $maleCount ?>
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
            <div class="metric-card p-3 h-100" style="--metric-accent: #10b981;">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="text-muted small fw-bold text-uppercase tracking-wide">Staff & Teachers</div>
                        <h3 class="fw-bold my-2 mb-1"><?= number_format((int)($totalTeachers ?? 0)) ?></h3>
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="metric-trend bg-success bg-opacity-10 text-success">
                                <i class="fas fa-users me-1"></i>On payroll
                            </span>
                            <span class="text-muted">Verified staff</span>
                        </div>
                    </div>
                    <div class="metric-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-chalkboard-teacher fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="metric-card p-3 h-100" style="--metric-accent: #f59e0b;">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="text-muted small fw-bold text-uppercase tracking-wide">Classes</div>
                        <h3 class="fw-bold my-2 mb-1"><?= number_format((int)($totalClasses ?? 0)) ?></h3>
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="metric-trend bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-school me-1"></i>Active streams
                            </span>
                            <?php if (!empty($classDist['labels'])): ?>
                                <span class="text-muted"><?= count($classDist['labels']) ?> populated</span>
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
            <div class="metric-card p-3 h-100" style="--metric-accent: <?= $attendanceState === 'success' ? '#10b981' : ($attendanceState === 'warning' ? '#f59e0b' : '#ef4444') ?>;">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="text-muted small fw-bold text-uppercase tracking-wide">Today's Attendance</div>
                        <h3 class="fw-bold my-2 mb-1"><?= $todayRate ?>%</h3>
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="metric-trend bg-<?= $attendanceState ?> bg-opacity-10 text-<?= $attendanceState ?>">
                                <i class="fas <?= $attendanceIcon ?> me-1"></i><?= $attendanceLabel ?>
                            </span>
                            <span class="text-muted">7d avg <?= $avgTrend ?>%</span>
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
            <div class="dash-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h6 class="fw-bold mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Weekly Attendance Trend</h6>
                        <small>Performance over the last 7 recorded days</small>
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
                <div class="card-body pt-3">
                    <div class="chart-container" style="height: 260px;">
                        <?php if (empty($trendData) || array_sum($trendData) === 0): ?>
                            <div class="chart-empty-overlay">
                                <i class="fas fa-chart-line fa-2x mb-2 opacity-50"></i>
                                <span>No attendance trend data recorded for this week.</span>
                            </div>
                        <?php endif; ?>
                        <canvas id="attendanceTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dash-card h-100">
                <div class="card-header py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-venus-mars me-2 text-danger"></i>Gender Distribution</h6>
                    <small><?= number_format($totalEnrolled) ?> active students</small>
                </div>
                <div class="card-body pt-3 d-flex flex-column justify-content-between">
                    <div class="chart-container" style="height: 190px;">
                        <?php if ($totalEnrolled === 0): ?>
                            <div class="chart-empty-overlay">
                                <i class="fas fa-users-slash fa-2x mb-2 opacity-50"></i>
                                <span>No student records found.</span>
                            </div>
                        <?php endif; ?>
                        <canvas id="genderRatioChart"></canvas>
                    </div>
                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <div class="text-center p-2 rounded bg-primary bg-opacity-10 border border-primary border-opacity-10">
                                <div class="fw-bold text-primary"><?= $malePct ?>%</div>
                                <div class="small text-muted"><i class="fas fa-mars me-1"></i>Male (<?= $maleCount ?>)</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 rounded bg-danger bg-opacity-10 border border-danger border-opacity-10">
                                <div class="fw-bold text-danger"><?= $femalePct ?>%</div>
                                <div class="small text-muted"><i class="fas fa-venus me-1"></i>Female (<?= $femaleCount ?>)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="dash-card h-100">
                <div class="card-header py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2 text-success"></i>Enrollment by Class</h6>
                </div>
                <div class="card-body pt-3">
                    <div class="chart-container" style="height: 240px;">
                        <?php if (empty($classDist['labels']) || (array_sum($classDist['male']) + array_sum($classDist['female'])) === 0): ?>
                            <div class="chart-empty-overlay">
                                <i class="fas fa-school fa-2x mb-2 opacity-50"></i>
                                <span>No class allocation data available.</span>
                            </div>
                        <?php endif; ?>
                        <canvas id="classDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="dash-card h-100">
                <div class="card-header py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-user-tag me-2 text-warning"></i>Staff by Category</h6>
                </div>
                <div class="card-body pt-3">
                    <div class="chart-container" style="height: 240px;">
                        <?php if (empty($staffDist['data']) || array_sum($staffDist['data']) === 0): ?>
                            <div class="chart-empty-overlay">
                                <i class="fas fa-id-card fa-2x mb-2 opacity-50"></i>
                                <span>No staff category records found.</span>
                            </div>
                        <?php endif; ?>
                        <canvas id="staffCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Recently Added Students (Moved to Left / First) -->
        <div class="col-lg-6">
            <div class="dash-card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0"><i class="fas fa-user-plus me-2 text-success"></i>Recently Added Learners</h6>
                    </div>
                    <a href="<?= BASE_URL . '/students' ?>" class="text-decoration-none small fw-semibold text-primary">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body pt-0 px-0">
                    <?php if (!empty($recentStudentList)): ?>
                        <div class="table-responsive">
                            <table class="table activity-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 40%;">Learner</th>
                                        <th style="width: 25%;">Adm No.</th>
                                        <th style="width: 15%;">Class</th>
                                        <th style="width: 20%;">Section</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentStudentList as $student): ?>
                                        <?php 
                                            $firstNameVal = trim($student['first_name'] ?? '');
                                            $lastNameVal  = trim($student['last_name'] ?? '');
                                            $fullNameUpper = strtoupper($lastNameVal . ' ' . $firstNameVal);
                                            $initials = strtoupper(substr($firstNameVal, 0, 1) . substr($lastNameVal, 0, 1));
                                            $statusVal = trim($student['section_name'] ?? $student['category_name'] ?? 'UGANDA');
                                            $statusBadge = (stripos($statusVal, 'board') !== false) ? 'bg-success bg-opacity-10 text-success' : 'bg-light text-dark border';
                                            $admNumber = htmlspecialchars($student['registration_number'] ?? $student['admission_number'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center space-x-3">
                                                    <div class="text-truncate" style="max-width: 150px;">
                                                        <a href="<?= BASE_URL . '/students/show?id=' . ($student['id'] ?? '') ?>" class="fw-bold text-dark text-decoration-none uppercase small d-block text-truncate">
                                                            <?= $fullNameUpper ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="font-mono text-muted small">
                                                    <?= $admNumber ?>
                                                </span>
                                            </td>
                                            <td class="small fw-semibold text-dark"><?= htmlspecialchars($student['class_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <span class="badge <?= $statusBadge ?> px-2 py-1 fw-semibold uppercase" style="font-size: 0.68rem;">
                                                    <?= htmlspecialchars($statusVal, ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state py-5 text-center">
                            <i class="fas fa-user-graduate fa-3x mb-2 opacity-25"></i>
                            <div class="small text-muted">No recently added learners found.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activity (Moved to Right / Last) -->
        <div class="col-lg-6">
            <div class="dash-card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0"><i class="fas fa-history me-2 text-primary"></i>Recent Activity</h6>
                    </div>
                    <a href="<?= BASE_URL . '/audit' ?>" class="text-decoration-none small fw-semibold text-primary">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body pt-0 px-0">
                    <?php if (!empty($activityList)): ?>
                        <div class="table-responsive">
                            <table class="table activity-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">User</th>
                                        <th style="width: 20%;">Action</th>
                                        <th style="width: 35%;">Details</th>
                                        <th style="width: 15%;" class="text-end">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activityList as $activity): ?>
                                        <?php
                                            $actionName = (string)($activity['action'] ?? 'LOG');
                                            $badgeClass = 'bg-secondary bg-opacity-10 text-secondary';
                                            if (stripos($actionName, 'creat') !== false)  $badgeClass = 'bg-success bg-opacity-10 text-success';
                                            elseif (stripos($actionName, 'updat') !== false) $badgeClass = 'bg-primary bg-opacity-10 text-primary';
                                            elseif (stripos($actionName, 'delet') !== false) $badgeClass = 'bg-danger bg-opacity-10 text-danger';
                                            elseif (stripos($actionName, 'publish') !== false) $badgeClass = 'bg-info bg-opacity-10 text-info';
                                            elseif (stripos($actionName, 'login') !== false) $badgeClass = 'bg-secondary bg-opacity-10 text-secondary';
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="activity-dot"></span>
                                                    <span class="fw-semibold small text-truncate" style="max-width: 130px;">
                                                        <?= htmlspecialchars(trim(($activity['first_name'] ?? 'System') . ' ' . ($activity['last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td><span class="badge <?= $badgeClass ?> px-2 py-1 fw-semibold"><?= htmlspecialchars($actionName, ENT_QUOTES, 'UTF-8') ?></span></td>
                                            <td class="small text-muted text-truncate" style="max-width: 160px;"><?= htmlspecialchars($activity['description'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="small text-muted text-end text-nowrap">
                                                <?= !empty($activity['created_at']) ? date('H:i, d M', strtotime($activity['created_at'])) : '-' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state py-5 text-center">
                            <i class="fas fa-inbox fa-3x mb-2 opacity-25"></i>
                            <div class="small text-muted">No recent activity recorded yet.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const css = getComputedStyle(document.documentElement);
    const token = (name, fallback) => (css.getPropertyValue(name).trim() || fallback);

    const textColor    = token('--text-muted',   '#64748b');
    const gridColor    = token('--border-color', '#e2e8f0');
    const accentColor  = token('--accent-color', '#2563eb');
    const warningColor = token('--warning-color','#f59e0b');
    const surfaceColor = token('--surface-color','#ffffff');

    const hexToRgb = (hex) => {
        const h = hex.replace('#', '').trim();
        if (h.length !== 6) return '37,99,235';
        return [
            parseInt(h.substring(0, 2), 16),
            parseInt(h.substring(2, 4), 16),
            parseInt(h.substring(4, 6), 16),
        ].join(',');
    };
    const accentRgb = hexToRgb(accentColor);

    Chart.defaults.font.family = token('--font-family-base', "'Inter', system-ui, -apple-system, sans-serif");
    Chart.defaults.font.size   = 11;
    Chart.defaults.color       = textColor;

    new Chart(document.getElementById('attendanceTrendChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($trendLabels) ?>,
            datasets: [{
                label: 'Attendance %',
                data: <?= json_encode($trendData) ?>,
                borderColor: accentColor,
                backgroundColor: (ctx) => {
                    const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 250);
                    g.addColorStop(0, 'rgba(' + accentRgb + ', 0.2)');
                    g.addColorStop(1, 'rgba(' + accentRgb + ', 0)');
                    return g;
                },
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: surfaceColor,
                pointBorderColor: accentColor,
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    min: 0, max: 100,
                    ticks: { callback: v => v + '%', color: textColor },
                    grid: { color: gridColor, drawBorder: false }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: textColor }
                }
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
                backgroundColor: [accentColor, '#ec4899', '#94a3b8'],
                borderWidth: 0,
                cutout: '75%'
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
            datasets: [
                {
                    label: 'Male',
                    data: <?= json_encode($classDist['male'] ?? []) ?>,
                    backgroundColor: accentColor,
                    borderRadius: 0,
                    barPercentage: 0.9,
                    categoryPercentage: 0.65
                },
                {
                    label: 'Female',
                    data: <?= json_encode($classDist['female'] ?? []) ?>,
                    backgroundColor: '#0d9488',
                    borderRadius: 0,
                    barPercentage: 0.9,
                    categoryPercentage: 0.65
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        font: { size: 11, weight: '500' },
                        color: textColor
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: textColor },
                    grid: { color: gridColor, drawBorder: false }
                },
                x: {
                    stacked: false,
                    ticks: { color: textColor },
                    grid: { display: false }
                }
            }
        }
    });

    new Chart(document.getElementById('staffCategoryChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($staffDist['labels'] ?? []) ?>,
            datasets: [{
                data: <?= json_encode($staffDist['data'] ?? []) ?>,
                backgroundColor: [warningColor, accentColor, '#8b5cf6', '#f97316', '#14b8a6', '#db2777'],
                borderWidth: 0,
                cutout: '65%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { boxWidth: 10, padding: 10, font: { size: 11 }, color: textColor }
                }
            }
        }
    });
});
</script>