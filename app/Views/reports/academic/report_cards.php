<!-- File: /app/Views/reports/academic/report_cards.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Report Cards</h4>
            <small class="text-muted">Configure and generate student report cards</small>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <form method="GET" action="<?= BASE_URL ?>/reports/academic/report-card/view" target="_blank">
        <input type="hidden" name="student_id" id="selected_student_id">

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
                                   value="End of Term Report" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Report Color</label>
                            <select name="report_color" class="form-select">
                                <option value="color">Color</option>
                                <option value="bw" selected>Black & White</option>
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
                            <label class="form-label">Generate PDF(s)</label>
                            <select name="generate_pdf" class="form-select">
                                <option value="no" selected>No</option>
                                <option value="yes">Yes</option>
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
                                    <option value="no" selected>No</option>
                                    <option value="yes">Yes</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Photo</label>
                                <select name="show_photo" class="form-select form-select-sm">
                                    <option value="no">No</option>
                                    <option value="yes" selected>Yes</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Division</label>
                                <select name="show_division" class="form-select form-select-sm">
                                    <option value="no" selected>No</option>
                                    <option value="yes">Yes</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Grades</label>
                                <select name="show_grades" class="form-select form-select-sm">
                                    <option value="no">No</option>
                                    <option value="yes" selected>Yes</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Grades Per Exam</label>
                                <select name="grades_per_exam" class="form-select form-select-sm">
                                    <option value="no">No</option>
                                    <option value="yes" selected>Yes</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Grade Format</label>
                                <select name="grade_format" class="form-select form-select-sm">
                                    <option value="default" selected>Default</option>
                                    <option value="points">Points Only</option>
                                    <option value="both">Points & Grade</option>
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
                                    <option value="no">No</option>
                                    <option value="yes" selected>Yes</option>
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
                                    <option value="no" selected>No</option>
                                    <option value="yes">Yes</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Auto Signatures</label>
                                <select name="auto_signatures" class="form-select form-select-sm">
                                    <option value="no" selected>No</option>
                                    <option value="yes">Yes</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">School Fees</label>
                                <select name="show_fees" class="form-select form-select-sm">
                                    <option value="no" selected>No</option>
                                    <option value="yes">Yes</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Next Term Begins/Ends</label>
                                <select name="show_next_term" class="form-select form-select-sm">
                                    <option value="no" selected>No</option>
                                    <option value="yes">Yes</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Remarks/Comments</label>
                                <select name="show_remarks" class="form-select form-select-sm">
                                    <option value="no" selected>No</option>
                                    <option value="yes">Yes</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Performance</label>
                                <select name="show_performance" class="form-select form-select-sm">
                                    <option value="no" selected>No</option>
                                    <option value="yes">Yes</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Mark Sheet</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="mark_sheet" value="list" id="ms_list">
                                        <label class="form-check-label" for="ms_list">List Only</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="mark_sheet" value="summary" id="ms_summary">
                                        <label class="form-check-label" for="ms_summary">Summary Report</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================= -->
        <!-- Student Selection (essential for a single report card) -->
        <!-- ============================================= -->
        <div class="card mt-3">
            <div class="card-header bg-light fw-bold">
                <i class="fas fa-user-graduate me-1"></i> Select Student and Period
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                        <select name="academic_year_id" class="form-select" required>
                            <option value="">Select Year</option>
                            <?php foreach ($filters['academic_years'] as $year): ?>
                                <option value="<?= $year['id'] ?>"><?= htmlspecialchars($year['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Term <span class="text-danger">*</span></label>
                        <select name="term_id" class="form-select" required>
                            <option value="">Select Term</option>
                            <?php foreach ($filters['terms'] as $term): ?>
                                <option value="<?= $term['id'] ?>"><?= htmlspecialchars($term['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Student <span class="text-danger">*</span></label>
                        <select name="student_id" id="student_id" class="form-select" required>
                            <option value="">Select Student</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student['id'] ?>">
                                    <?= htmlspecialchars($student['admission_number'] . ' - ' . $student['last_name'] . ' ' . $student['first_name']) ?>
                                    <?= $student['class_name'] ? ' (' . htmlspecialchars($student['class_name']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-eye me-1"></i> Generate
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// "All" checkbox toggles all exam checkboxes
document.getElementById('exam_all').addEventListener('change', function () {
    document.querySelectorAll('.exam-checkbox').forEach(cb => {
        cb.checked = this.checked;
        cb.disabled = this.checked;
    });
});

// Sync student select to hidden field
document.getElementById('student_id').addEventListener('change', function () {
    document.getElementById('selected_student_id').value = this.value;
});
</script>