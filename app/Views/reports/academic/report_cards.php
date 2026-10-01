<!-- File: /app/Views/reports/academic/report_cards.php -->
<div class="container-fluid px-0 py-2">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">
                <i class="fas fa-layer-group text-primary me-2"></i>Report Cards
            </h4>
            <p class="text-muted small mb-0">Configure and generate students Report Cards.</p>
        </div>
        <div>
        </div>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_error']) ?>
            <?php unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="GET" action="<?= BASE_URL ?>/reports/academic/report-cards/view" target="_blank" id="ReportForm">

        <div class="card shadow-sm border-0 mb-4 rounded-3">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-filter me-2"></i>1. Select Academic Period &amp; Target Class
                </h6>
            </div>
            <div class="card-body bg-light bg-opacity-25">
                <div class="row g-3">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label small fw-semibold text-secondary">Academic Year <span class="text-danger">*</span></label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select form-select-sm shadow-none" required>
                            <option value="">Choose Year...</option>
                            <?php foreach ($filters['academic_years'] as $year): ?>
                                <option value="<?= (int)$year['id'] ?>"><?= htmlspecialchars($year['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label small fw-semibold text-secondary">Term <span class="text-danger">*</span></label>
                        <select name="term_id" id="term_id" class="form-select form-select-sm shadow-none" required disabled>
                            <option value="">Select Year First...</option>
                            <?php foreach ($filters['terms'] as $term): ?>
                                <option value="<?= (int)$term['id'] ?>" data-year="<?= (int)$term['academic_year_id'] ?>">
                                    <?= htmlspecialchars($term['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label small fw-semibold text-secondary">Class <span class="text-danger">*</span></label>
                        <select name="class_id" id="class_id" class="form-select form-select-sm shadow-none" required>
                            <option value="">Choose Class...</option>
                            <?php foreach ($filters['classes'] as $class): ?>
                                <option value="<?= (int)$class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label small fw-semibold text-secondary">Stream <span class="text-muted">(Optional)</span></label>
                        <select name="stream_id" id="stream_id" class="form-select form-select-sm shadow-none" disabled>
                            <option value="">All Streams</option>
                            <?php foreach ($filters['streams'] as $stream): ?>
                                <option value="<?= (int)$stream['id'] ?>" data-class="<?= (int)$stream['class_id'] ?>">
                                    <?= htmlspecialchars($stream['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-sliders-h me-2"></i>2. Report Parameters &amp; Configurations
                </h6>
            </div>
            <div class="card-body">
                <?php
                    $formAction     = BASE_URL . '/reports/academic/report-cards/view';
                    $includeStudent = false;
                    $defaults       = $defaults ?? [];
                    include __DIR__ . '/_report_config.php';
                ?>
            </div>
        </div>

        <div class="d-flex justify-content-end align-items-center bg-white p-3 rounded-3 shadow-sm">
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm">
                <i class="fas fa-print me-2"></i>Generate Report Cards
            </button>
        </div>
    </form>
</div>

<style>
    .form-select:disabled {
        background-color: #e9ecef !important;
        color: #495057 !important;
        opacity: 1 !important;
        cursor: not-allowed;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const yearSelect = document.getElementById('academic_year_id');
    const termSelect = document.getElementById('term_id');
    const classSelect = document.getElementById('class_id');
    const streamSelect = document.getElementById('stream_id');
    
    const examSelect = document.getElementById('exam_id') || document.getElementById('examination_id');

    const allTerms = Array.from(termSelect.querySelectorAll('option[data-year]'));
    const allStreams = Array.from(streamSelect.querySelectorAll('option[data-class]'));
    const allExams = examSelect ? Array.from(examSelect.querySelectorAll('option[data-year], option[data-term]')) : [];

    yearSelect.addEventListener('change', function () {
        const yearId = this.value;
        termSelect.value = '';
        termSelect.disabled = !yearId;
        
        if (!yearId) {
            termSelect.innerHTML = '<option value="">Select Year First...</option>';
        } else {
            termSelect.innerHTML = '<option value="">Choose Term...</option>';
            allTerms.forEach(opt => {
                if (opt.getAttribute('data-year') === yearId) {
                    termSelect.appendChild(opt.cloneNode(true));
                }
            });
        }
        triggerExamFiltering();
    });

    termSelect.addEventListener('change', function () {
        triggerExamFiltering();
    });

    classSelect.addEventListener('change', function () {
        const classId = this.value;
        streamSelect.value = '';
        streamSelect.disabled = !classId;

        if (!classId) {
            streamSelect.innerHTML = '<option value="">All Streams</option>';
            streamSelect.disabled = true;
            return;
        }

        streamSelect.innerHTML = '<option value="">All Streams</option>';
        let matches = 0;
        allStreams.forEach(opt => {
            if (opt.getAttribute('data-class') === classId) {
                streamSelect.appendChild(opt.cloneNode(true));
                matches++;
            }
        });
        streamSelect.disabled = matches === 0;
    });

    function triggerExamFiltering() {
        if (!examSelect) return;

        const yearId = yearSelect.value;
        const termId = termSelect.value;

        examSelect.value = '';
        
        if (!yearId && !termId) {
            examSelect.innerHTML = '<option value="">Select Year &amp; Term First...</option>';
            examSelect.disabled = true;
            return;
        }

        examSelect.disabled = false;
        examSelect.innerHTML = '<option value="">Choose Examination...</option>';

        let visibleCount = 0;
        allExams.forEach(opt => {
            const optYear = opt.getAttribute('data-year');
            const optTerm = opt.getAttribute('data-term');

            let matchesYear = !yearId || !optYear || optYear === yearId;
            let matchesTerm = !termId || !optTerm || optTerm === termId;

            if (matchesYear && matchesTerm) {
                examSelect.appendChild(opt.cloneNode(true));
                visibleCount++;
            }
        });

        if (visibleCount === 0) {
            examSelect.innerHTML = '<option value="">No examinations found for selected period</option>';
            examSelect.disabled = true;
        }
    }
});
</script>