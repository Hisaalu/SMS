<!-- File: /app/Views/examinations/marks/selector.php -->
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
            <form method="GET" action="<?= BASE_URL ?>/marks/entry" id="marksEntryForm">
                
                <div class="row g-2 align-items-center">
                    
                    <!-- Academic Year Filter -->
                    <div class="col-md-4 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 110px;">Academic Year <span class="text-danger">*</span></label>
                        <select name="academic_year_id" class="form-select form-select-sm" required>
                            <option value="">-- Select Year --</option>
                            <?php foreach ($academicYears as $year): ?>
                                <option value="<?= $year['id'] ?>" <?= (($_GET['academic_year_id'] ?? '') == $year['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($year['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Term Filter -->
                    <div class="col-md-4 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 100px;">Term <span class="text-danger">*</span></label>
                        <select name="term_id" class="form-select form-select-sm" required>
                            <option value="">-- Select Term --</option>
                            <?php foreach ($terms as $term): ?>
                                <option value="<?= $term['id'] ?>" <?= (($_GET['term_id'] ?? '') == $term['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($term['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Examination Filter -->
                    <div class="col-md-4 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 100px;">Examination <span class="text-danger">*</span></label>
                        <select name="examination_id" class="form-select form-select-sm" required>
                            <option value="">-- Select Exam --</option>
                            <?php foreach ($examinations as $exam): ?>
                                <option value="<?= $exam['id'] ?>" <?= (($_GET['examination_id'] ?? '') == $exam['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($exam['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Class Filter -->
                    <div class="col-md-4 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 110px;">Class <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-select form-select-sm" required>
                            <option value="">-- Select Class --</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['id'] ?>" <?= (($_GET['class_id'] ?? '') == $class['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($class['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Stream Filter -->
                    <div class="col-md-4 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 100px;">Stream</label>
                        <select name="stream_id" class="form-select form-select-sm">
                            <option value="">-- All Streams --</option>
                            <?php foreach ($streams as $stream): ?>
                                <option value="<?= $stream['id'] ?>" <?= (($_GET['stream_id'] ?? '') == $stream['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($stream['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Subject Filter -->
                    <div class="col-md-4 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 100px;">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select form-select-sm" required>
                            <option value="">-- Select Subject --</option>
                            <?php foreach ($subjects as $subj): ?>
                                <option value="<?= $subj['id'] ?>" <?= (($_GET['subject_id'] ?? '') == $subj['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subj['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <!-- Action Bar -->
                <div class="row mt-3">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-sm btn-primary fw-bold text-nowrap me-1">
                            <i class="fas fa-pencil-alt me-1"></i> Enter Marks
                        </button>
                        <a href="<?= BASE_URL ?>/marks/entry" class="btn btn-sm btn-outline-secondary me-1" title="Reset Filters">
                            <i class="fas fa-undo me-1"></i> Reset
                        </a>
                        <a href="<?= BASE_URL ?>/examinations" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>

</div>