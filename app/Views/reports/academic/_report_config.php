<!-- File: /app/Views/reports/academic/_report_config.php -->
<?php
/**
 * Shared Report Configuration partial.
 */
$defaults = $defaults ?? [];
$opt = function ($key, $default) use ($defaults) {
    return $defaults[$key] ?? $default;
};
?>

<div class="row g-3">

    <!-- ============================================= -->
    <!-- LEFT COLUMN: Report Definition -->
    <!-- ============================================= -->
    <div class="col-lg-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="mb-0 fw-bold text-primary">
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
                            <option value="color" <?= $opt('report_color','bw')==='color'?'selected':'' ?>>Color</option>
                            <option value="bw"    <?= $opt('report_color','bw')==='bw'   ?'selected':'' ?>>Black & White</option>
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

                <!-- FINAL GRADE SOURCE -->
                <h6 class="fw-bold text-primary small text-uppercase mb-2">
                    <i class="fas fa-calculator me-1"></i> Final Grade Source
                </h6>
                <div class="alert alert-light border small py-2 px-3 mb-3">
                    Determines which exam set (or combination) produces the final
                    <strong>Grade</strong>, <strong>Total Score</strong>, <strong>Aggregate</strong>,
                    <strong>Division</strong>, <strong>Position</strong> and <strong>Comment</strong>.
                </div>
                <div class="mb-3">
                    <select name="final_grade_method" id="final_grade_method" class="form-select form-select-sm">
                        <option value="average"
                            <?= $opt('final_grade_method','average')==='average'?'selected':'' ?>>
                            Average of all selected exams
                        </option>
                        <option value="best_set"
                            <?= $opt('final_grade_method','average')==='best_set'?'selected':'' ?>>
                            Best exam set (highest average across all subjects)
                        </option>
                        <option value="worst_set"
                            <?= $opt('final_grade_method','average')==='worst_set'?'selected':'' ?>>
                            Worst exam set (lowest average across all subjects)
                        </option>
                        <?php if (!empty($filters['examinations'])): ?>
                            <option disabled>──────────</option>
                            <?php foreach ($filters['examinations'] as $exam): ?>
                                <option value="exam:<?= $exam['id'] ?>"
                                    <?= $opt('final_grade_method','') === 'exam:' . $exam['id'] ? 'selected' : '' ?>>
                                    Use <?= htmlspecialchars($exam['name']) ?> exam only
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="text-muted">
                        Choose how the final Grade, Total Score, Aggregate, Division and Position are computed.
                    </small>
                </div>

                <hr class="my-3">

                <!-- POSITION RANKING -->
                <h6 class="fw-bold text-primary small text-uppercase mb-2">
                    <i class="fas fa-sort-numeric-down me-1"></i> Position Ranking
                </h6>
                <div class="alert alert-light border small py-2 px-3 mb-3">
                    Determines how student positions are ordered within the class.
                </div>
                <div class="mb-3">
                    <select name="position_ranking" class="form-select form-select-sm">
                        <option value="aggregate" <?= $opt('position_ranking','aggregate')==='aggregate'?'selected':'' ?>>
                            Highest Aggregate (recommended)
                        </option>
                        <option value="total"     <?= $opt('position_ranking','aggregate')==='total'?'selected':'' ?>>
                            Highest Total Marks
                        </option>
                        <option value="average"   <?= $opt('position_ranking','aggregate')==='average'?'selected':'' ?>>
                            Highest Average Marks
                        </option>
                    </select>
                </div>

                <hr class="my-3">

                <!-- EXAMS -->
                <div class="mb-0">
                    <label class="form-label small fw-semibold">
                        Examinations <span class="text-danger">*</span>
                    </label>
                    <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="examinations[]" value="all" id="exam_all">
                            <label class="form-check-label fw-bold" for="exam_all">All</label>
                        </div>
                        <hr class="my-1">
                        <?php foreach ($filters['examinations'] as $exam): ?>
                            <div class="form-check">
                                <input class="form-check-input exam-checkbox" type="checkbox"
                                       name="examinations[]" value="<?= $exam['id'] ?>"
                                       id="exam_<?= $exam['id'] ?>">
                                <label class="form-check-label" for="exam_<?= $exam['id'] ?>">
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

    <!-- ============================================= -->
    <!-- RIGHT COLUMN: Report Includes -->
    <!-- ============================================= -->
    <div class="col-lg-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-list-check me-2"></i>Report Includes
                </h6>
            </div>
            <div class="card-body">

                <!-- STUDENT INFO SECTION -->
                <h6 class="fw-bold text-secondary small text-uppercase mb-2">Student Information</h6>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Show Photo</label>
                        <select name="show_photo" class="form-select form-select-sm">
                            <option value="no"  <?= $opt('show_photo','yes')==='no' ?'selected':'' ?>>No</option>
                            <option value="yes" <?= $opt('show_photo','yes')==='yes'?'selected':'' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Show Positions</label>
                        <select name="show_positions" class="form-select form-select-sm">
                            <option value="no"  <?= $opt('show_positions','no')==='no' ?'selected':'' ?>>No</option>
                            <option value="yes" <?= $opt('show_positions','no')==='yes'?'selected':'' ?>>Yes</option>
                        </select>
                    </div>
                </div>

                <hr class="my-3">

                <!-- MARKS SECTION -->
                <h6 class="fw-bold text-secondary small text-uppercase mb-2">Marks Display</h6>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Show Grades (Scores)</label>
                        <select name="show_grades" class="form-select form-select-sm">
                            <option value="no"  <?= $opt('show_grades','yes')==='no' ?'selected':'' ?>>No</option>
                            <option value="yes" <?= $opt('show_grades','yes')==='yes'?'selected':'' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Scores Per Exam</label>
                        <select name="grades_per_exam" class="form-select form-select-sm">
                            <option value="no"  <?= $opt('grades_per_exam','yes')==='no' ?'selected':'' ?>>No</option>
                            <option value="yes" <?= $opt('grades_per_exam','yes')==='yes'?'selected':'' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Show Division</label>
                        <select name="show_division" class="form-select form-select-sm">
                            <option value="no"  <?= $opt('show_division','no')==='no' ?'selected':'' ?>>No</option>
                            <option value="yes" <?= $opt('show_division','no')==='yes'?'selected':'' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Show Teacher Initials</label>
                        <select name="show_initials" class="form-select form-select-sm">
                            <option value="yes" <?= $opt('show_initials','yes')==='yes'?'selected':'' ?>>Yes</option>
                            <option value="no"  <?= $opt('show_initials','yes')==='no' ?'selected':'' ?>>No</option>
                        </select>
                    </div>
                </div>

                <hr class="my-3">

                <!-- COMMENTS SECTION -->
                <h6 class="fw-bold text-secondary small text-uppercase mb-2">Comments & Remarks</h6>
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
                            <option value="no"  selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Auto Signatures</label>
                        <select name="auto_signatures" class="form-select form-select-sm">
                            <option value="no"  selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                </div>

                <hr class="my-3">

                <!-- EXTRAS SECTION -->
                <h6 class="fw-bold text-secondary small text-uppercase mb-2">Additional Sections</h6>
                <div class="row g-2 mb-0">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Other Activities (Skills)</label>
                        <select name="show_skills" class="form-select form-select-sm">
                            <option value="no"  <?= $opt('show_skills','no')==='no' ?'selected':'' ?>>No</option>
                            <option value="yes" <?= $opt('show_skills','no')==='yes'?'selected':'' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">School Fees Balance</label>
                        <select name="show_fees" class="form-select form-select-sm">
                            <option value="no"  selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Next Term Dates</label>
                        <select name="show_next_term" class="form-select form-select-sm">
                            <option value="no"  <?= $opt('show_next_term','no')==='no' ?'selected':'' ?>>No</option>
                            <option value="yes" <?= $opt('show_next_term','no')==='yes'?'selected':'' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Performance Graph</label>
                        <select name="show_performance" class="form-select form-select-sm">
                            <option value="no"  selected>No</option>
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
    if (examAll) {
        examAll.addEventListener('change', function () {
            document.querySelectorAll('.exam-checkbox').forEach(cb => {
                cb.checked = this.checked;
                cb.disabled = this.checked;
            });
        });
    }
});
</script>