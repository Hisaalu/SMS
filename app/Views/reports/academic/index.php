<!-- File: app/Views/reports/academic/index.php -->

<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Academic Reports</h4>
            <small class="text-muted">School-wide performance overview</small>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <label class="form-label small">Academic Year</label>
                    <select name="academic_year_id" class="form-select form-select-sm">
                        <option value="">All Years</option>
                        <?php foreach ($filters['academic_years'] as $y): ?>
                            <option value="<?= (int)$y['id'] ?>"
                                <?= ($selectedFilters['academic_year_id'] ?? '') == $y['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($y['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Term</label>
                    <select name="term_id" class="form-select form-select-sm">
                        <option value="">All Terms</option>
                        <?php foreach ($filters['terms'] as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"
                                <?= ($selectedFilters['term_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Class</label>
                    <select name="class_id" class="form-select form-select-sm">
                        <option value="">All Classes</option>
                        <?php foreach ($filters['classes'] as $c): ?>
                            <option value="<?= (int)$c['id'] ?>"
                                <?= ($selectedFilters['class_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm me-2">Apply</button>
                    <a href="<?= BASE_URL ?>/reports/academic" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Total Students</div>
                    <div class="h4 mb-0"><?= (int)$stats['total_students'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Assessed Students</div>
                    <div class="h4 mb-0"><?= (int)$stats['assessed_students'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Missing Marks</div>
                    <div class="h4 mb-0"><?= (int)$stats['missing_marks'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Overall Average</div>
                    <div class="h4 mb-0"><?= number_format((float)$stats['overall_average'], 2) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Highest Mark</div>
                    <div class="h5 mb-0"><?= number_format((float)$stats['highest_mark'], 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Lowest Mark</div>
                    <div class="h5 mb-0"><?= number_format((float)$stats['lowest_mark'], 2) ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($stats['grade_distribution'])): ?>
        <div class="card mb-3">
            <div class="card-header">
                <strong>Grade Distribution</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th>Range</th>
                            <th class="text-end">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['grade_distribution'] as $g): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($g['grade']) ?></strong></td>
                                <td><?= htmlspecialchars($g['range']) ?></td>
                                <td class="text-end"><?= (int)$g['count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <strong>Quick Links</strong>
        </div>
        <div class="card-body">
            <a href="<?= BASE_URL ?>/reports/academic/report-cards" class="btn btn-outline-primary btn-sm me-2">Report Cards</a>
            <a href="<?= BASE_URL ?>/reports/academic/subject-analysis" class="btn btn-outline-primary btn-sm me-2">Subject Analysis</a>
            <a href="<?= BASE_URL ?>/reports/academic/class-analysis" class="btn btn-outline-primary btn-sm me-2">Class Analysis</a>
            <a href="<?= BASE_URL ?>/reports/academic/batch-report-cards" class="btn btn-outline-primary btn-sm">Batch Report Cards</a>
        </div>
    </div>

</div>