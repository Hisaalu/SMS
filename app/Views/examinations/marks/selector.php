<!-- File: /app/Views/examinations/marks/selector.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Marks Entry</h4>
            <small class="text-muted">Select Academic Year, Term, Class, Examination, Stream, and Subject to enter marks</small>
        </div>
        <a href="<?= BASE_URL ?>/examinations" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/marks/entry" id="marksEntryForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                        <select name="academic_year_id" class="form-select" required>
                            <option value="">Select Academic Year</option>
                            <?php foreach ($academicYears as $year): ?>
                                <option value="<?= $year['id'] ?>" <?= (($_GET['academic_year_id'] ?? '') == $year['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($year['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Term <span class="text-danger">*</span></label>
                        <select name="term_id" class="form-select" required>
                            <option value="">Select Term</option>
                            <?php foreach ($terms as $term): ?>
                                <option value="<?= $term['id'] ?>" <?= (($_GET['term_id'] ?? '') == $term['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($term['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Examination <span class="text-danger">*</span></label>
                        <select name="examination_id" class="form-select" required>
                            <option value="">Select Examination</option>
                            <?php foreach ($examinations as $exam): ?>
                                <option value="<?= $exam['id'] ?>" <?= (($_GET['examination_id'] ?? '') == $exam['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($exam['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Class <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-select" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['id'] ?>" <?= (($_GET['class_id'] ?? '') == $class['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($class['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Stream <small class="text-muted">(Optional)</small></label>
                        <select name="stream_id" class="form-select">
                            <option value="">All Streams / No Stream</option>
                            <?php foreach ($streams as $stream): ?>
                                <option value="<?= $stream['id'] ?>" <?= (($_GET['stream_id'] ?? '') == $stream['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($stream['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subj): ?>
                                <option value="<?= $subj['id'] ?>" <?= (($_GET['subject_id'] ?? '') == $subj['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subj['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-pencil-alt me-1"></i> Enter Marks
                        </button>
                        <a href="<?= BASE_URL ?>/marks/entry" class="btn btn-secondary ms-2">
                            <i class="fas fa-undo me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>