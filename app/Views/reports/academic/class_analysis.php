<!-- File: /app/Views/reports/academic/class_analysis.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Class Performance Analysis</h4>
            <small class="text-muted">View marks matrix for the whole class</small>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/reports/academic/class-analysis" class="row g-2">
                <div class="col-md-3">
                    <select name="academic_year_id" class="form-select form-select-sm" required>
                        <option value="">Select Academic Year</option>
                        <?php foreach ($filters['academic_years'] as $year): ?>
                            <option value="<?= $year['id'] ?>" <?= ($selectedFilters['academic_year_id'] ?? '') == $year['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="class_id" class="form-select form-select-sm">
                        <option value="">All Classes</option>
                        <?php foreach ($filters['classes'] as $class): ?>
                            <option value="<?= $class['id'] ?>" <?= ($selectedFilters['class_id'] ?? '') == $class['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="stream_id" class="form-select form-select-sm">
                        <option value="">All Streams</option>
                        <?php foreach ($filters['streams'] as $stream): ?>
                            <option value="<?= $stream['id'] ?>" <?= ($selectedFilters['stream_id'] ?? '') == $stream['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($stream['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark btn-sm flex-fill">Generate</button>
                    <a href="<?= BASE_URL ?>/reports/academic/class-analysis" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Matrix -->
    <?php if (!empty($matrix['students']) && !empty($matrix['subjects'])): ?>
        <div class="card">
            <div class="card-header fw-bold">Marks Matrix</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <?php foreach ($matrix['subjects'] as $subject): ?>
                                    <th><?= htmlspecialchars($subject['code'] ?: $subject['name']) ?></th>
                                <?php endforeach; ?>
                                <th>Total</th>
                                <th>Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matrix['students'] as $student): 
                                $total = 0; $count = 0;
                                foreach ($matrix['subjects'] as $subject) {
                                    $mark = $matrix['marks'][$student['id']][$subject['id']] ?? null;
                                    if ($mark !== null) { $total += $mark; $count++; }
                                }
                                $avg = $count > 0 ? round($total / $count, 2) : 0;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['last_name'] . ' ' . $student['first_name']) ?></td>
                                    <?php foreach ($matrix['subjects'] as $subject): ?>
                                        <td><?= htmlspecialchars($matrix['marks'][$student['id']][$subject['id']] ?? '-') ?></td>
                                    <?php endforeach; ?>
                                    <td><strong><?= $total ?></strong></td>
                                    <td><strong><?= $avg ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card"><div class="card-body text-center text-muted py-4">
            No academic results available for the selected criteria.
        </div></div>
    <?php endif; ?>
</div>