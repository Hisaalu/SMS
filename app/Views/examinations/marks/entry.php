<!-- File: /app/Views/examinations/marks/entry.php -->
<div class="container-fluid px-0">
    <form id="marksForm" onsubmit="return false;">
        <!-- Hidden input for foreign key requirement -->
        <input type="hidden" name="examination_subject_id" value="<?= (int)($examinationSubjectId ?? 0) ?>">

        <!-- Top Sticky Header Action Bar -->
        <div class="card mb-3 sticky-top shadow-sm" style="top: 10px; z-index: 1020; background-color: #ffffff;">
            <div class="card-body py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0 fw-bold">Enter Marks</h5>
                    <small class="text-muted">
                        <?= htmlspecialchars($academicYear['name'] ?? '') ?> | 
                        <?= htmlspecialchars($term['name'] ?? '') ?> | 
                        <span class="badge bg-secondary"><?= htmlspecialchars($examination['name'] ?? 'Exam') ?></span> |
                        <strong><?= htmlspecialchars($class['name'] ?? '') ?></strong>
                        <?= !empty($stream) ? ' (' . htmlspecialchars($stream['name']) . ')' : '' ?> | 
                        <span class="badge bg-primary"><?= htmlspecialchars($subject['name'] ?? '') ?></span>
                    </small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="autoSaveStatus" class="small text-muted me-2">
                        <i class="fas fa-clock me-1"></i> Auto-save active
                    </span>
                    <button type="button" class="btn btn-success btn-save-marks" onclick="saveMarks(false)">
                        <i class="fas fa-save me-1"></i> Save Marks
                    </button>
                    <a href="<?= BASE_URL ?>/marks/entry" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <?php if (empty($examinationSubjectId)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-1"></i> 
                This subject is not assigned to an active examination schedule for this academic year. Please schedule the examination paper first.
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-users fa-2x mb-2 d-block"></i>
                        <p>No students found for this class and stream selection.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Admission No.</th>
                                    <th>Student Name</th>
                                    <th>Stream</th>
                                    <th>Marks (Max: <?= $subject['max_marks'] ?? 100 ?>)</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $index => $student): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($student['admission_number']) ?></td>
                                        <td><?= htmlspecialchars(($student['last_name'] ?? '') . ' ' . ($student['first_name'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars($student['stream_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <input type="number" 
                                                   name="marks[<?= $student['id'] ?>][marks_obtained]" 
                                                   class="form-control form-control-sm mark-input" 
                                                   style="width: 120px;"
                                                   min="0" 
                                                   max="<?= $subject['max_marks'] ?? 100 ?>"
                                                   value="<?= htmlspecialchars($existingMarks[$student['id']]['marks_obtained'] ?? '') ?>"
                                                   step="0.01">
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   name="marks[<?= $student['id'] ?>][remarks]" 
                                                   class="form-control form-control-sm"
                                                   placeholder="Optional remarks"
                                                   value="<?= htmlspecialchars($existingMarks[$student['id']]['remarks'] ?? '') ?>"
                                                   style="width: 220px;">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 d-flex justify-content-end">
                        <button type="button" class="btn btn-success btn-save-marks" onclick="saveMarks(false)">
                            <i class="fas fa-save me-1"></i> Save All Marks
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<script>
function saveMarks(isAutoSave = false) {
    const form = document.getElementById('marksForm');
    const statusEl = document.getElementById('autoSaveStatus');
    if (!form) return;
    
    const formData = new FormData(form);
    const marks = {};
    let hasData = false;
    const maxMarks = <?= (float)($subject['max_marks'] ?? 100) ?>;
    const examSubjId = parseInt(formData.get('examination_subject_id') || 0);

    document.querySelectorAll('.mark-input').forEach(input => {
        const val = input.value.trim();
        if (val !== '') {
            const numVal = parseFloat(val);
            if (numVal >= 0 && numVal <= maxMarks) {
                hasData = true;
            }
        }
    });

    if (!hasData) {
        if (!isAutoSave) alert('Please enter at least one mark before saving.');
        return;
    }

    formData.forEach((value, key) => {
        const match = key.match(/marks\[(\d+)\]\[(marks_obtained|remarks)\]/);
        if (match) {
            const studentId = match[1];
            const field = match[2];
            if (!marks[studentId]) marks[studentId] = {};
            marks[studentId][field] = value;
        }
    });

    const saveButtons = document.querySelectorAll('.btn-save-marks');
    if (!isAutoSave) {
        saveButtons.forEach(btn => {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
        });
    } else if (statusEl) {
        statusEl.innerHTML = '<i class="fas fa-sync fa-spin me-1"></i> Auto-saving...';
    }

    fetch('<?= BASE_URL ?>/marks/bulk-save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ 
            marks: marks,
            examination_subject_id: examSubjId,
            academic_year_id: <?= (int)($academicYear['id'] ?? 0) ?>,
            term_id: <?= (int)($term['id'] ?? 0) ?>,
            subject_id: <?= (int)($subject['id'] ?? 0) ?>
        })
    })
    .then(async res => {
        const text = await res.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error('Server returned HTML instead of JSON. Check backend controller.');
        }
    })
    .then(data => {
        if (data.success) {
            if (statusEl) {
                const now = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                statusEl.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i> Saved at ${now}`;
            }
            if (!isAutoSave) alert('Marks saved successfully!');
        } else if (!isAutoSave) {
            alert('Error: ' + (data.error || 'Save failed'));
        }
    })
    .catch(err => {
        if (statusEl) statusEl.innerHTML = '<i class="fas fa-exclamation-triangle text-danger me-1"></i> Save failed';
        if (!isAutoSave) alert('Failed to save: ' + err.message);
    })
    .finally(() => {
        saveButtons.forEach(btn => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Marks';
        });
    });
}

// Auto-save every 30 seconds
setInterval(() => {
    saveMarks(true);
}, 30000);
</script>