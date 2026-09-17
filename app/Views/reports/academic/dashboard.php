<!-- File: /app/Views/reports/academic/dashboard.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Academic Reports Dashboard</h4>
            <small class="text-muted">Overview of academic performance</small>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/reports/academic" class="row g-2">
                <div class="col-md-3">
                    <select name="academic_year_id" class="form-select form-select-sm">
                        <option value="">All Academic Years</option>
                        <?php foreach ($filters['academic_years'] as $year): ?>
                            <option value="<?= $year['id'] ?>" <?= ($selectedFilters['academic_year_id'] ?? '') == $year['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="class_id" class="form-select form-select-sm">
                        <option value="">All Classes</option>
                        <?php foreach ($filters['classes'] as $class): ?>
                            <option value="<?= $class['id'] ?>" <?= ($selectedFilters['class_id'] ?? '') == $class['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark btn-sm flex-fill">Apply</button>
                    <a href="<?= BASE_URL ?>/reports/academic" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Total Students</small>
                <h3 class="mb-0"><?= $stats['total_students'] ?? 0 ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Assessed</small>
                <h3 class="mb-0"><?= $stats['assessed_students'] ?? 0 ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Overall Average</small>
                <h3 class="mb-0"><?= $stats['overall_average'] ?? 0 ?>%</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Missing Marks</small>
                <h3 class="mb-0"><?= $stats['missing_marks'] ?? 0 ?></h3>
            </div>
        </div>
    </div>

    <!-- Grade Distribution -->
    <?php if (!empty($stats['grade_distribution'])): ?>
        <div class="card">
            <div class="card-header fw-bold">Grade Distribution</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th>Range</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['grade_distribution'] as $g): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($g['grade']) ?></strong></td>
                                <td><?= htmlspecialchars($g['range']) ?></td>
                                <td><?= $g['count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="card"><div class="card-body text-center text-muted py-4">
            No academic results available for the selected criteria.
        </div></div>
    <?php endif; ?>
</div>