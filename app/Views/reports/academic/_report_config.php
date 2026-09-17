<!-- File: /app/Views/reports/academic/_report_config.php -->
<?php
/**
 * Shared Report Configuration partial.
 * Used by both single report-card form and batch report-card form.
 *
 * Expected variables:
 *   $filters        – from $reportService->getReportFilters()
 *   $formAction     – URL the form should submit to
 *   $includeStudent – true for single report card, false for batch
 *   $extrasHtml     – HTML string inserted into the student selector (batch inserts class/stream here)
 *   $defaults       – array of default option values (optional)
 */
$defaults = $defaults ?? [];
$opt = function ($key, $default) use ($defaults) {
    return $defaults[$key] ?? $default;
};
?>

<!-- ============================================= -->
<!-- Left Column: Report Definition -->
<!-- ============================================= -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fas fa-file-alt me-1"></i> Report Definition
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Report Name <span class="text-danger">*</span></label>
                    <input type="text" name="report_name" class="form-control"
                           value="<?= htmlspecialchars($opt('report_name', 'End of Term Report')) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Report Color</label>
                    <select name="report_color" class="form-select">
                        <option value="color" <?= $opt('report_color','bw')==='color'?'selected':'' ?>>Color</option>
                        <option value="bw"    <?= $opt('report_color','bw')==='bw'   ?'selected':'' ?>>Black &amp; White</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Report Header</label>
                    <select name="report_header" class="form-select">
                        <option value="default" selected>Default Report Header</option>
                        <option value="custom">Custom Header</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Report Format</label>
                    <select name="report_format" class="form-select">
                        <option value="progression" selected>Progression Report Card</option>
                        <option value="standard">Standard Report Card</option>
                        <option value="summary">Summary Report Card</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Report Card Schedule</label>
                    <select name="report_schedule" class="form-select">
                        <option value="no" selected>No</option>
                        <option value="yes">Yes</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">
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
                    <small class="text-muted">Select which exams to include on the report card</small>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- Right Column: Report Includes -->
    <!-- ============================================= -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fas fa-list me-1"></i> Report Includes
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Positions</label>
                        <select name="show_positions" class="form-select form-select-sm">
                            <option value="no"  <?= $opt('show_positions','no')==='no' ?'selected':'' ?>>No</option>
                            <option value="yes" <?= $opt('show_positions','no')==='yes'?'selected':'' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Photo</label>
                        <select name="show_photo" class="form-select form-select-sm">
                            <option value="no"  <?= $opt('show_photo','yes')==='no' ?'selected':'' ?>>No</option>
                            <option value="yes" <?= $opt('show_photo','yes')==='yes'?'selected':'' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Division</label>
                        <select name="show_division" class="form-select form-select-sm">
                            <option value="no"  selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Grades</label>
                        <select name="show_grades" class="form-select form-select-sm">
                            <option value="no"  <?= $opt('show_grades','yes')==='no' ?'selected':'' ?>>No</option>
                            <option value="yes" <?= $opt('show_grades','yes')==='yes'?'selected':'' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Grades Per Exam</label>
                        <select name="grades_per_exam" class="form-select form-select-sm">
                            <option value="no"  <?= $opt('grades_per_exam','yes')==='no' ?'selected':'' ?>>No</option>
                            <option value="yes" <?= $opt('grades_per_exam','yes')==='yes'?'selected':'' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Grade Format</label>
                        <select name="grade_format" class="form-select form-select-sm">
                            <option value="default" selected>Default (Grade)</option>
                            <option value="points">Points Only</option>
                            <option value="both">Points &amp; Grade</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Opt Comments</label>
                        <select name="opt_comments" class="form-select form-select-sm">
                            <option value="no">No</option>
                            <option value="yes" selected>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Other Activities (Skills)</label>
                        <select name="show_skills" class="form-select form-select-sm">
                            <option value="no"  <?= $opt('show_skills','yes')==='no' ?'selected':'' ?>>No</option>
                            <option value="yes" <?= $opt('show_skills','yes')==='yes'?'selected':'' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">HM Comment</label>
                        <select name="hm_comment" class="form-select form-select-sm">
                            <option value="no">No</option>
                            <option value="auto" selected>Auto</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">CT Comment</label>
                        <select name="ct_comment" class="form-select form-select-sm">
                            <option value="no">No</option>
                            <option value="auto" selected>Auto</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">HM/CT Names</label>
                        <select name="show_names" class="form-select form-select-sm">
                            <option value="no"  selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Auto Signatures</label>
                        <select name="auto_signatures" class="form-select form-select-sm">
                            <option value="no"  selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">School Fees</label>
                        <select name="show_fees" class="form-select form-select-sm">
                            <option value="no"  selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Next Term Begins/Ends</label>
                        <select name="show_next_term" class="form-select form-select-sm">
                            <option value="no"  selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Remarks/Comments</label>
                        <select name="show_remarks" class="form-select form-select-sm">
                            <option value="no"  selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Performance</label>
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