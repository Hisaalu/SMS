<!-- File: /app/Views/students/enrollment.php -->
<div class="container-fluid px-0">
    
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-users-cog text-primary me-2"></i>Batch Student Enrollment</h4>
            <p class="text-muted small mb-0">Select target Academic Year, Term, and Class to enroll students in bulk.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Bar for Loading Candidates -->
    <div class="card card-shadow border-0 mb-4">
        <div class="card-body bg-light rounded-3 p-3">
            <form method="GET" action="<?= BASE_URL . '/student/enrollments' ?>" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Target Academic Year</label>
                    <select name="academic_year_id" class="form-select form-select-sm" required>
                        <option value="">-- Choose Year --</option>
                        <?php foreach ($academicYears as $ay): ?>
                            <option value="<?= $ay['id'] ?>" <?= $selectedYear == $ay['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ay['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Filter Candidate Source</label>
                    <select name="filter" id="filterType" class="form-select form-select-sm" onchange="togglePreviousClassSelect()">
                        <option value="unenrolled" <?= $filterStatus === 'unenrolled' ? 'selected' : '' ?>>Unenrolled Students</option>
                        <option value="previous_class" <?= $filterStatus === 'previous_class' ? 'selected' : '' ?>>From Previous Class</option>
                    </select>
                </div>

                <div class="col-md-3" id="previousClassContainer" style="<?= $filterStatus !== 'previous_class' ? 'display: none;' : '' ?>">
                    <label class="form-label fw-bold small text-muted">Promote From Class</label>
                    <select name="previous_class_id" class="form-select form-select-sm">
                        <option value="">-- Choose Previous Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $previousClassId == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-dark btn-sm w-100 fw-bold">
                        <i class="fas fa-filter me-1"></i> Load Students
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Batch Form -->
    <form action="<?= BASE_URL . '/student/enrollments/store' ?>" method="POST">
        <div class="row g-4">
            
            <!-- Left Panel: Destination Details -->
            <div class="col-lg-4">
                <div class="card card-shadow border-0 sticky-top" style="top: 80px;">
                    <div class="card-header bg-primary text-white fw-bold py-3">
                        <i class="fas fa-arrow-right-to-bracket me-1"></i> Target Enrollment Class
                    </div>
                    <div class="card-body">
                        
                        <input type="hidden" name="academic_year_id" value="<?= $selectedYear ?>">

                        <div class="mb-3">
                            <label class="form-label fw-medium">Term</label>
                            <select name="term_id" class="form-select">
                                <option value="">-- All / Default Term --</option>
                                <?php foreach ($terms as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Target Class <span class="text-danger">*</span></label>
                            <select name="class_id" class="form-select" required>
                                <option value="">-- Select Class --</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Target Stream</label>
                            <select name="stream_id" class="form-select">
                                <option value="">-- No Stream / Optional --</option>
                                <?php foreach ($streams as $str): ?>
                                    <option value="<?= $str['id'] ?>"><?= htmlspecialchars($str['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                                <select name="status_id" class="form-select" required>
                                    <?php foreach ($statuses as $stat): ?>
                                        <option value="<?= $stat['id'] ?>"><?= htmlspecialchars($stat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium">Enrollment Date</label>
                            <input type="date" name="enrollment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-bold py-2" <?= empty($candidateStudents) ? 'disabled' : '' ?>>
                            <i class="fas fa-check-double me-1"></i> Enroll Selected Students
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Student Selection Table -->
            <div class="col-lg-8">
                <div class="card card-shadow border-0">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                        <span class="fw-bold"><i class="fas fa-list-check me-1 text-primary"></i> Select Candidates</span>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll" onclick="toggleSelectAll(this)">
                            <label class="form-check-label fw-bold small" for="selectAll">Select All</label>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>Admission No</th>
                                        <th>Student Name</th>
                                        <th>Gender</th>
                                        <th>Current Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($candidateStudents)): ?>
                                        <?php foreach ($candidateStudents as $st): ?>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input student-checkbox" type="checkbox" name="student_ids[]" value="<?= $st['id'] ?>">
                                                </td>
                                                <td class="fw-bold"><?= htmlspecialchars($st['admission_number']) ?></td>
                                                <td><?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name']) ?></td>
                                                <td><?= htmlspecialchars($st['gender']) ?></td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= htmlspecialchars($st['current_class']) ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="fas fa-user-slash fs-3 d-block mb-2"></i>
                                                No eligible students found for the selected filter.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
function togglePreviousClassSelect() {
    var filterType = document.getElementById('filterType').value;
    var container = document.getElementById('previousClassContainer');
    container.style.display = (filterType === 'previous_class') ? 'block' : 'none';
}

function toggleSelectAll(source) {
    var checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(function(cb) {
        cb.checked = source.checked;
    });
}
</script>