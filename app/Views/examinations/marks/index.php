<!-- File: /app/Views/examinations/marks/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Enter Marks</h4>
            <small class="text-muted"><?= htmlspecialchars($examination['name']) ?> - <?= htmlspecialchars($examination['assessment_type_name'] ?? '') ?></small>
        </div>
        <a href="<?= BASE_URL ?>/examinations" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Examinations
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if (empty($students) || count($students) === 0): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                    <p>No students enrolled for this examination.</p>
                </div>
            <?php else: ?>
                <form id="marksForm" method="POST">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Admission No.</th>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Marks</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $index => $student): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($student['admission_number']) ?></td>
                                        <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                        <td><?= htmlspecialchars($student['class_name'] ?? '') ?></td>
                                        <td>
                                            <input type="number" 
                                                   name="marks[<?= $student['id'] ?>][marks_obtained]" 
                                                   class="form-control form-control-sm" 
                                                   style="width: 120px;"
                                                   min="0" 
                                                   max="100"
                                                   value="<?= htmlspecialchars($existingMarks[$student['id']]['marks_obtained'] ?? '') ?>"
                                                   step="0.01">
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   name="marks[<?= $student['id'] ?>][remarks]" 
                                                   class="form-control form-control-sm"
                                                   placeholder="Optional remarks"
                                                   value="<?= htmlspecialchars($existingMarks[$student['id']]['remarks'] ?? '') ?>"
                                                   style="width: 200px;">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" onclick="saveMarks()">
                            <i class="fas fa-save me-1"></i> Save All Marks
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function saveMarks() {
    const form = document.getElementById('marksForm');
    const formData = new FormData(form);
    const marks = {};
    
    formData.forEach((value, key) => {
        if (key.startsWith('marks[')) {
            const match = key.match(/marks\[(\d+)\]\[(marks_obtained|remarks)\]/);
            if (match) {
                const studentId = match[1];
                const field = match[2];
                if (!marks[studentId]) {
                    marks[studentId] = {};
                }
                marks[studentId][field] = value;
            }
        }
    });
    
    // Check if we have any data
    const hasData = Object.keys(marks).length > 0;
    if (!hasData) {
        alert('No marks to save.');
        return;
    }
    
    if (!confirm('Save all marks?')) {
        return;
    }
    
    fetch('<?= BASE_URL ?>/marks/bulk-save/<?= $examination['id'] ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ marks: marks })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Marks saved successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to save marks'));
        }
    })
    .catch(error => {
        alert('An error occurred: ' + error);
    });
}
</script>