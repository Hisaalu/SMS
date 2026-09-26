<!-- File: /app/Views/examinations/marks/entry.php -->
<div class="container-fluid px-0">
    <form id="marksForm" onsubmit="return false;">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Marks Entry Form</h4>
            <small class="text-muted">
                <span class="badge bg-primary"><?= htmlspecialchars($examination['name'] ?? 'N/A') ?></span>
                <?= htmlspecialchars($academicYear['name'] ?? '') ?> |
                <?= htmlspecialchars($term['name'] ?? '') ?> |
                <strong><?= htmlspecialchars($class['name'] ?? '') ?></strong>
                <?= !empty($stream) ? ' (' . htmlspecialchars($stream['name']) . ')' : '' ?>
            </small>
        </div>
        <a href="<?= BASE_URL ?>/marks/entry" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <label for="subjectSelect" class="fw-semibold mb-0 text-nowrap small">Subject:</label>
                <select id="subjectSelect" class="form-select form-select-sm" style="width: 170px;" onchange="switchSubject(this.value)">
                    <option value="all" <?= empty($selectedSubject) ? 'selected' : '' ?>>-- All Subjects --</option>
                    <?php foreach ($allSubjects as $subj): ?>
                        <option value="<?= $subj['id'] ?>" <?= (!empty($selectedSubject) && $selectedSubject['id'] == $subj['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($subj['code'] ?? $subj['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span id="autoSaveStatus" class="small text-muted">
                    <i class="fas fa-clock me-1"></i> Auto-save active
                </span>
                <button type="button" class="btn btn-sm btn-primary fw-bold text-nowrap btn-save-marks" onclick="saveMarks(false)">
                    <i class="fas fa-save me-1"></i> Save Marks
                </button>
            </div>
        </div>
    </div>

        <div class="card">
            <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                <?php if (empty($students)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-users fa-2x mb-2 d-block opacity-50"></i>
                        <p class="mb-0">No students found for this selection.</p>
                    </div>
                <?php else: ?>
                    <table class="table table-bordered table-hover align-middle mb-0 text-nowrap small">
                        <thead class="sticky-top">
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th style="width: 110px;">Student No</th>
                                <th>Name</th>
                                <th style="width: 40px;" class="text-center">Sex</th>
                                <?php foreach ($subjects as $subj): ?>
                                    <th style="width: 75px;" class="text-center" title="<?= htmlspecialchars($subj['name']) ?>">
                                        <?= htmlspecialchars($subj['code'] ?? $subj['name']) ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $index => $student): ?>
                                <?php
                                    $rawSex = strtoupper(trim($student['gender'] ?? $student['sex'] ?? ''));
                                    if (in_array($rawSex, ['F', 'FEMALE', '2'])) {
                                        $sexDisplay = 'F';
                                    } elseif (in_array($rawSex, ['M', 'MALE', '1'])) {
                                        $sexDisplay = 'M';
                                    } else {
                                        $sexDisplay = '-';
                                    }
                                ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($student['admission_number']) ?></td>
                                    <td class="fw-bold text-uppercase"><?= htmlspecialchars(($student['last_name'] ?? '') . ' ' . ($student['first_name'] ?? '')) ?></td>
                                    <td class="text-center"><?= $sexDisplay ?></td>
                                    <?php foreach ($subjects as $subj): ?>
                                        <?php $val = $existingMarks[$student['id']][$subj['id']]['marks_obtained'] ?? ''; ?>
                                        <td class="p-1 text-center">
                                            <input type="number"
                                                   name="marks[<?= $student['id'] ?>][<?= $subj['id'] ?>]"
                                                   class="form-control form-control-sm text-center mark-input py-0"
                                                   min="0"
                                                   max="<?= $subj['max_marks'] ?? 100 ?>"
                                                   value="<?= htmlspecialchars($val) ?>"
                                                   step="0.01">
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<script>
function switchSubject(subjectId) {
    const url = new URL(window.location.href);
    url.searchParams.set('subject_id', subjectId);
    window.location.href = url.toString();
}

function saveMarks(isAutoSave = false) {
    const form = document.getElementById('marksForm');
    const statusEl = document.getElementById('autoSaveStatus');
    if (!form) return;

    const formData = new FormData(form);
    const marks = {};
    let hasData = false;

    document.querySelectorAll('.mark-input').forEach(input => {
        if (input.value.trim() !== '') hasData = true;
    });

    if (!hasData && !isAutoSave) {
        alert('Please enter at least one mark before saving.');
        return;
    }

    formData.forEach((value, key) => {
        const match = key.match(/marks\[(\d+)\]\[(\d+)\]/);
        if (match) {
            const studentId = match[1];
            const subjectId = match[2];
            if (!marks[studentId]) marks[studentId] = {};
            marks[studentId][subjectId] = value;
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
            academic_year_id: <?= (int)($academicYear['id'] ?? 0) ?>,
            term_id: <?= (int)($term['id'] ?? 0) ?>,
            examination_id: <?= (int)($examination['id'] ?? 0) ?>
        })
    })
    .then(async res => {
        const text = await res.text();
        try { return JSON.parse(text); }
        catch (e) { throw new Error('Server returned invalid response.'); }
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

setInterval(() => saveMarks(true), 30000);
</script>