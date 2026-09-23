<!-- File: /app/Views/reports/academic/_report_config.php -->
<?php
$defaults = $defaults ?? [];
$opt = static fn(string $key, $fallback) => $defaults[$key] ?? $fallback;
$sel = static fn($current, $value) => (string)$current === (string)$value ? ' selected' : '';
?>

<div class="row g-3">

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0 fw-bold" style="color: var(--accent-color);">
                    <i class="fas fa-file-alt me-2"></i>Report Definition
                </h6>
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Report Name <span class="text-danger">*</span></label>
                    <input type="text" name="report_name" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($opt('report_name', 'End of Term Report')) ?>" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Report Color</label>
                        <select name="report_color" class="form-select form-select-sm">
                            <option value="color"<?= $sel($opt('report_color', 'bw'), 'color') ?>>Color</option>
                            <option value="bw"<?= $sel($opt('report_color', 'bw'), 'bw') ?>>Black &amp; White</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Report Header</label>
                        <select name="report_header" class="form-select form-select-sm">
                            <option value="default" selected>Default Header</option>
                            <option value="custom">Custom Header</option>
                        </select>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Report Format</label>
                        <select name="report_format" class="form-select form-select-sm">
                            <option value="progression" selected>Progression</option>
                            <option value="standard">Standard</option>
                            <option value="summary">Summary</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Report Schedule</label>
                        <select name="report_schedule" class="form-select form-select-sm">
                            <option value="no" selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                </div>

                <hr class="my-3">

                <h6 class="fw-bold small text-uppercase mb-2" style="color: var(--accent-color);">
                    <i class="fas fa-calculator me-1"></i> Final Grade Source
                </h6>
                <div class="alert alert-light small py-2 px-3 mb-3">
                    Determines which exam set (or combination) produces the final
                    <strong>Grade</strong>, <strong>Total Score</strong>, <strong>Aggregate</strong>,
                    <strong>Division</strong>, <strong>Position</strong> and <strong>Comment</strong>.
                </div>
                <?php $currentMethod = $opt('final_grade_method', 'average'); ?>
                <div class="mb-3">
                    <select name="final_grade_method" class="form-select form-select-sm">
                        <option value="average"<?= $sel($currentMethod, 'average') ?>>Average of all selected exams</option>
                        <option value="best_set"<?= $sel($currentMethod, 'best_set') ?>>Best exam set (highest average)</option>
                        <option value="worst_set"<?= $sel($currentMethod, 'worst_set') ?>>Worst exam set (lowest average)</option>
                        <?php if (!empty($filters['examinations'])): ?>
                            <option disabled>──────────</option>
                            <?php foreach ($filters['examinations'] as $exam): ?>
                                <option value="exam:<?= (int)$exam['id'] ?>"<?= $sel($currentMethod, 'exam:' . $exam['id']) ?>>
                                    Use <?= htmlspecialchars($exam['name']) ?> exam only
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="text-muted">Choose how the final Grade, Total Score, Aggregate, Division and Position are computed.</small>
                </div>

                <hr class="my-3">

                <h6 class="fw-bold small text-uppercase mb-2" style="color: var(--accent-color);">
                    <i class="fas fa-sort-numeric-down me-1"></i> Position Ranking
                </h6>
                <div class="alert alert-light small py-2 px-3 mb-3">
                    Determines how student positions are ordered within the class.
                </div>
                <?php $currentRanking = $opt('position_ranking', 'aggregate'); ?>
                <div class="mb-3">
                    <select name="position_ranking" class="form-select form-select-sm">
                        <option value="aggregate"<?= $sel($currentRanking, 'aggregate') ?>>Highest Aggregate (recommended)</option>
                        <option value="total"<?= $sel($currentRanking, 'total') ?>>Highest Total Marks</option>
                        <option value="average"<?= $sel($currentRanking, 'average') ?>>Highest Average Marks</option>
                    </select>
                </div>

                <hr class="my-3">

                <div class="mb-0">
                    <label class="form-label small fw-semibold">Examinations <span class="text-danger">*</span></label>
                    <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="examinations[]" value="all" id="exam_all">
                            <label class="form-check-label fw-bold" for="exam_all">All</label>
                        </div>
                        <hr class="my-1">
                        <?php foreach ($filters['examinations'] as $exam): ?>
                            <div class="form-check">
                                <input class="form-check-input exam-checkbox" type="checkbox"
                                       name="examinations[]" value="<?= (int)$exam['id'] ?>"
                                       id="exam_<?= (int)$exam['id'] ?>">
                                <label class="form-check-label" for="exam_<?= (int)$exam['id'] ?>">
                                    <?= htmlspecialchars($exam['name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-muted">Select exams to include on the report card.</small>
                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0 fw-bold" style="color: var(--accent-color);">
                    <i class="fas fa-list-check me-2"></i>Report Includes
                </h6>
            </div>
            <div class="card-body">

                <h6 class="fw-bold text-muted small text-uppercase mb-2">Student Information</h6>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Show Photo</label>
                        <?php $v = $opt('show_photo', 'yes'); ?>
                        <select name="show_photo" class="form-select form-select-sm">
                            <option value="no"<?= $sel($v, 'no') ?>>No</option>
                            <option value="yes"<?= $sel($v, 'yes') ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Show Positions</label>
                        <?php $v = $opt('show_positions', 'no'); ?>
                        <select name="show_positions" class="form-select form-select-sm">
                            <option value="no"<?= $sel($v, 'no') ?>>No</option>
                            <option value="yes"<?= $sel($v, 'yes') ?>>Yes</option>
                        </select>
                    </div>
                </div>

                <hr class="my-3">

                <h6 class="fw-bold text-muted small text-uppercase mb-2">Marks Display</h6>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Show Grades (Scores)</label>
                        <?php $v = $opt('show_grades', 'yes'); ?>
                        <select name="show_grades" class="form-select form-select-sm">
                            <option value="no"<?= $sel($v, 'no') ?>>No</option>
                            <option value="yes"<?= $sel($v, 'yes') ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Scores Per Exam</label>
                        <?php $v = $opt('grades_per_exam', 'yes'); ?>
                        <select name="grades_per_exam" class="form-select form-select-sm">
                            <option value="no"<?= $sel($v, 'no') ?>>No</option>
                            <option value="yes"<?= $sel($v, 'yes') ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Show Division</label>
                        <?php $v = $opt('show_division', 'no'); ?>
                        <select name="show_division" class="form-select form-select-sm">
                            <option value="no"<?= $sel($v, 'no') ?>>No</option>
                            <option value="yes"<?= $sel($v, 'yes') ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Show Teacher Initials</label>
                        <?php $v = $opt('show_initials', 'yes'); ?>
                        <select name="show_initials" class="form-select form-select-sm">
                            <option value="yes"<?= $sel($v, 'yes') ?>>Yes</option>
                            <option value="no"<?= $sel($v, 'no') ?>>No</option>
                        </select>
                    </div>
                </div>

                <hr class="my-3">

                <h6 class="fw-bold text-muted small text-uppercase mb-2">Comments &amp; Remarks</h6>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Class Teacher Comment</label>
                        <select name="ct_comment" class="form-select form-select-sm">
                            <option value="auto" selected>Auto (based on grade method)</option>
                            <option value="custom">Custom</option>
                            <option value="no">None</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Head Teacher Comment</label>
                        <select name="hm_comment" class="form-select form-select-sm">
                            <option value="auto" selected>Auto</option>
                            <option value="custom">Custom</option>
                            <option value="no">None</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Show HM/CT Names</label>
                        <select name="show_names" class="form-select form-select-sm">
                            <option value="no" selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Auto Signatures</label>
                        <select name="auto_signatures" class="form-select form-select-sm">
                            <option value="no" selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                </div>

                <hr class="my-3">

                <h6 class="fw-bold text-muted small text-uppercase mb-2">Additional Sections</h6>
                <div class="row g-2 mb-0">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Other Activities (Skills)</label>
                        <?php $v = $opt('show_skills', 'no'); ?>
                        <select name="show_skills" class="form-select form-select-sm">
                            <option value="no"<?= $sel($v, 'no') ?>>No</option>
                            <option value="yes"<?= $sel($v, 'yes') ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">School Fees Balance</label>
                        <select name="show_fees" class="form-select form-select-sm">
                            <option value="no" selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Next Term Dates</label>
                        <?php $v = $opt('show_next_term', 'no'); ?>
                        <select name="show_next_term" class="form-select form-select-sm">
                            <option value="no"<?= $sel($v, 'no') ?>>No</option>
                            <option value="yes"<?= $sel($v, 'yes') ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Performance Graph</label>
                        <select name="show_performance" class="form-select form-select-sm">
                            <option value="no" selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const examAll = document.getElementById('exam_all');
    if (!examAll) return;

    examAll.addEventListener('change', function () {
        document.querySelectorAll('.exam-checkbox').forEach(function (cb) {
            cb.checked = this.checked;
            cb.disabled = this.checked;
        }.bind(this));
    });
});
</script>