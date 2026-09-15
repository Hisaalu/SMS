<!-- File: /app/Views/examinations/results/selector.php -->
<div class="container-fluid px-3 py-3 bg-white border">

    <!-- Flash Notifications -->
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Bar Card -->
    <div class="card mb-3 bg-light border">
        <div class="card-body p-2">
            <form action="<?= BASE_URL ?>/results/class" method="GET" id="resultsFilterForm">
                
                <div class="row g-2 align-items-center">
                    
                    <!-- Academic Year Filter -->
                    <div class="col-md-4 d-flex align-items-center">
                        <label for="academic_year_id" class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 110px;">Academic Year <span class="text-danger">*</span></label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select form-select-sm" required>
                            <option value="">-- Select Year --</option>
                            <?php foreach ($academicYears as $year): ?>
                                <option value="<?= $year['id'] ?>" <?= (isset($_GET['academic_year_id']) && $_GET['academic_year_id'] == $year['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($year['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Term Filter -->
                    <div class="col-md-4 d-flex align-items-center">
                        <label for="term_id" class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 100px;">Term <span class="text-danger">*</span></label>
                        <select name="term_id" id="term_id" class="form-select form-select-sm" required>
                            <option value="">-- Select Term --</option>
                            <?php foreach ($terms as $term): ?>
                                <option value="<?= $term['id'] ?>" <?= (isset($_GET['term_id']) && $_GET['term_id'] == $term['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($term['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Class Filter -->
                    <div class="col-md-4 d-flex align-items-center">
                        <label for="class_id" class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 100px;">Class <span class="text-danger">*</span></label>
                        <select name="class_id" id="class_id" class="form-select form-select-sm" required>
                            <option value="">-- Select Class --</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['id'] ?>" <?= (isset($_GET['class_id']) && $_GET['class_id'] == $class['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($class['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Stream Filter -->
                    <div class="col-md-4 d-flex align-items-center">
                        <label for="stream_id" class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 110px;">Stream</label>
                        <select name="stream_id" id="stream_id" class="form-select form-select-sm">
                            <option value="">-- All Streams --</option>
                            <?php if (isset($streams) && is_array($streams)): ?>
                                <?php foreach ($streams as $str): ?>
                                    <option value="<?= $str['id'] ?>" <?= (isset($_GET['stream_id']) && $_GET['stream_id'] == $str['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($str['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Examination Filter -->
                    <div class="col-md-4 d-flex align-items-center">
                        <label for="examination_id" class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 100px;">Examination <span class="text-danger">*</span></label>
                        <select name="examination_id" id="examination_id" class="form-select form-select-sm" required>
                            <option value="">-- Select Exam --</option>
                            <?php foreach ($examinations as $exam): ?>
                                <option value="<?= $exam['id'] ?>" <?= (isset($_GET['examination_id']) && $_GET['examination_id'] == $exam['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($exam['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Subject Filter -->
                    <div class="col-md-4 d-flex align-items-center">
                        <label for="subject_id" class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 100px;">Subject</label>
                        <select name="subject_id" id="subject_id" class="form-select form-select-sm">
                            <option value="all" <?= (!isset($_GET['subject_id']) || $_GET['subject_id'] === 'all') ? 'selected' : '' ?>>-- All Subjects --</option>
                            <?php if (isset($subjects) && is_array($subjects)): ?>
                                <?php foreach ($subjects as $subj): ?>
                                    <option value="<?= $subj['id'] ?>" <?= (isset($_GET['subject_id']) && $_GET['subject_id'] == $subj['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($subj['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                </div>

                <!-- Action Bar -->
                <div class="row mt-3">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-sm btn-primary fw-bold text-nowrap">
                            <i class="fas fa-search me-1"></i> View Results
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- Results Data Grid -->
    <?php if (isset($examination) && $examination): ?>
        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <h6 class="fw-bold mb-0">
                Results for <?= htmlspecialchars($examination['name']) ?>
            </h6>
            <span class="badge bg-secondary">
                Total Students: <?= isset($students) ? count($students) : count($results) ?>
            </span>
        </div>

        <div class="table-responsive border" style="max-height: 550px; overflow-y: auto;">
            <?php if (!empty($_GET['subject_id']) && $_GET['subject_id'] !== 'all'): ?>
                <!-- Single Subject View -->
                <table class="table table-bordered table-hover align-middle mb-0 text-nowrap small">
                    <thead class="table-secondary sticky-top">
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th style="width: 110px;">Student No</th>
                            <th>Name</th>
                            <th style="width: 40px;" class="text-center">Sex</th>
                            <th>Subject</th>
                            <th class="text-center" style="width: 100px;">Marks</th>
                            <th class="text-center" style="width: 90px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($results)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No marks recorded for this selection.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($results as $index => $row): ?>
                                <?php 
                                    $rawSex = strtoupper(trim($row['gender'] ?? $row['sex'] ?? ''));
                                    $sexDisplay = in_array($rawSex, ['F', 'FEMALE', '2']) ? 'F' : (in_array($rawSex, ['M', 'MALE', '1']) ? 'M' : '-');
                                ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['admission_number']) ?></td>
                                    <td class="fw-bold text-uppercase">
                                        <a href="<?= BASE_URL ?>/results/student/<?= $row['student_id'] ?>" class="text-decoration-none text-dark">
                                            <?= htmlspecialchars(($row['last_name'] ?? '') . ' ' . ($row['first_name'] ?? '')) ?>
                                        </a>
                                    </td>
                                    <td class="text-center"><?= $sexDisplay ?></td>
                                    <td><?= htmlspecialchars($row['subject_name']) ?></td>
                                    <td class="text-center fw-bold"><?= $row['marks_obtained'] ?></td>
                                    <td class="text-center">
                                        <?php if ($row['marks_obtained'] >= 50): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2">Pass</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2">Fail</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <!-- All Subjects Matrix View -->
                <table class="table table-bordered table-hover align-middle mb-0 text-nowrap small">
                    <thead class="table-secondary sticky-top">
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th style="width: 110px;">Student No</th>
                            <th>Name</th>
                            <th style="width: 40px;" class="text-center">Sex</th>
                            <?php if (!empty($subjects)): ?>
                                <?php foreach ($subjects as $subj): ?>
                                    <th style="width: 65px;" class="text-center" title="<?= htmlspecialchars($subj['name']) ?>">
                                        <?= htmlspecialchars($subj['code'] ?? $subj['name']) ?>
                                    </th>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="<?= 4 + count($subjects ?? []) ?>" class="text-center py-4 text-muted">No students found for this class/stream.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $index => $student): ?>
                                <?php 
                                    $rawSex = strtoupper(trim($student['gender'] ?? $student['sex'] ?? ''));
                                    $sexDisplay = in_array($rawSex, ['F', 'FEMALE', '2']) ? 'F' : (in_array($rawSex, ['M', 'MALE', '1']) ? 'M' : '-');
                                ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($student['admission_number']) ?></td>
                                    <td class="fw-bold text-uppercase">
                                        <a href="<?= BASE_URL ?>/results/student/<?= $student['id'] ?>" class="text-decoration-none text-dark">
                                            <?= htmlspecialchars(($student['last_name'] ?? '') . ' ' . ($student['first_name'] ?? '')) ?>
                                        </a>
                                    </td>
                                    <td class="text-center"><?= $sexDisplay ?></td>
                                    <?php if (!empty($subjects)): ?>
                                        <?php foreach ($subjects as $subj): ?>
                                            <td class="text-center fw-semibold">
                                                <?= htmlspecialchars($resultsMatrix[$student['id']][$subj['id']] ?? '') ?>
                                            </td>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const classSelect  = document.getElementById('class_id');
    const streamSelect = document.getElementById('stream_id');
    const selectedStream = "<?= $_GET['stream_id'] ?? '' ?>";

    function updateStreams(classId, preserveSelected = false) {
        if (!classId) {
            streamSelect.innerHTML = '<option value="">-- All Streams --</option>';
            streamSelect.disabled = false;
            return;
        }

        fetch(`<?= BASE_URL ?>/api/streams-by-class?class_id=${classId}`)
            .then(response => response.json())
            .then(data => {
                streamSelect.innerHTML = '<option value="">-- All Streams --</option>';

                if (data.length === 0) {
                    streamSelect.innerHTML = '<option value="">No streams assigned</option>';
                    streamSelect.disabled = true;
                } else {
                    streamSelect.disabled = false;
                    data.forEach(stream => {
                        const option = document.createElement('option');
                        option.value = stream.id;
                        option.textContent = stream.name;
                        if (preserveSelected && selectedStream == stream.id) {
                            option.selected = true;
                        }
                        streamSelect.appendChild(option);
                    });
                }
            })
            .catch(() => {
                streamSelect.disabled = false;
            });
    }

    classSelect.addEventListener('change', function () {
        updateStreams(this.value);
    });

    if (classSelect.value) {
        updateStreams(classSelect.value, true);
    }
});
</script>