<!-- File: /app/Views/examinations/marks/selector.php -->
<div class="container-fluid px-0">

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/marks/entry" id="marksEntryForm">
                <div class="row g-3 align-items-center">

                    <div class="col-md-4">
                        <label class="form-label fw-semibold mb-1">
                            Academic Year <span class="text-danger">*</span>
                        </label>
                        <select name="academic_year_id" id="academicYearSelect" class="form-select form-select-sm" required>
                            <option value="">-- Select Year --</option>
                            <?php foreach ($academicYears as $year): ?>
                                <option value="<?= (int)$year['id'] ?>"
                                        <?= (($_GET['academic_year_id'] ?? '') == $year['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($year['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold mb-1">
                            Term <span class="text-danger">*</span>
                        </label>
                        <select name="term_id" id="termSelect" class="form-select form-select-sm" required>
                            <option value="">-- Select Term --</option>
                            <?php foreach ($terms as $term): ?>
                                <option value="<?= (int)$term['id'] ?>"
                                        <?= (($_GET['term_id'] ?? '') == $term['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($term['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold mb-1">
                            Examination <span class="text-danger">*</span>
                        </label>
                        <select name="examination_id" id="examinationSelect" class="form-select form-select-sm" required>
                            <option value="">-- Select Exam --</option>
                            <?php foreach ($examinations as $exam): ?>
                                <option value="<?= (int)$exam['id'] ?>"
                                        <?= (($_GET['examination_id'] ?? '') == $exam['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($exam['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold mb-1">
                            Class <span class="text-danger">*</span>
                        </label>
                        <select name="class_id" id="classSelect" class="form-select form-select-sm" required>
                            <option value="">-- Select Class --</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= (int)$class['id'] ?>"
                                        <?= (($_GET['class_id'] ?? '') == $class['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($class['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold mb-1">Stream</label>
                        <select name="stream_id" id="streamSelect" class="form-select form-select-sm">
                            <option value="">-- All Streams --</option>
                            <?php foreach ($streams as $stream): ?>
                                <option value="<?= (int)$stream['id'] ?>"
                                        <?= (($_GET['stream_id'] ?? '') == $stream['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($stream['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold mb-1">
                            Subject <span class="text-danger">*</span>
                        </label>
                        <select name="subject_id" id="subjectSelect" class="form-select form-select-sm" required>
                            <option value="">-- Select Subject --</option>
                            <?php foreach ($subjects as $subj): ?>
                                <option value="<?= (int)$subj['id'] ?>"
                                        <?= (($_GET['subject_id'] ?? '') == $subj['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subj['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                    <button type="submit" class="btn btn-sm btn-primary fw-bold">
                        <i class="fas fa-pencil-alt me-1"></i> Enter Marks
                    </button>
                    <a href="<?= BASE_URL ?>/marks/entry" class="btn btn-sm btn-secondary" title="Reset Filters">
                        <i class="fas fa-undo me-1"></i> Reset
                    </a>
                    <a href="<?= BASE_URL ?>/examinations" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
(function () {
    const url      = '<?= BASE_URL ?>';
    const endpoint = url + '/api/marks-entry/lookup';

    const yearSel  = document.getElementById('academicYearSelect');
    const termSel  = document.getElementById('termSelect');
    const examSel  = document.getElementById('examinationSelect');
    const classSel = document.getElementById('classSelect');
    const strmSel  = document.getElementById('streamSelect');
    const subjSel  = document.getElementById('subjectSelect');

    function setOptions(select, items, placeholder, selected) {
        const current = selected || select.value;

        select.innerHTML = '<option value="">' + placeholder + '</option>';

        if (Array.isArray(items)) {
            items.forEach(function (item) {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.name + (item.code ? ' (' + item.code + ')' : '');
                if (String(current) === String(item.id)) opt.selected = true;
                select.appendChild(opt);
            });
        }
    }

    function setLoading(select, label) {
        select.innerHTML = '<option value="">' + (label || 'Loading…') + '</option>';
    }

    function fetchJSON(params) {
        const qs = new URLSearchParams(params).toString();
        return fetch(endpoint + '?' + qs, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.json(); });
    }

    function onYearChange() {
        const yearId = yearSel.value;

        setLoading(termSel, '-- Loading terms… --');
        setLoading(examSel, '-- Select Exam --');
        setOptions(subjSel, [], '-- Select Subject --');

        if (!yearId) {
            setOptions(termSel, [], '-- Select Term --');
            setOptions(examSel, [], '-- Select Exam --');
            return;
        }

        fetchJSON({ type: 'terms', year_id: yearId })
            .then(function (list) {
                setOptions(termSel, list, '-- Select Term --');
            })
            .catch(function () {
                setOptions(termSel, [], '-- Failed to load terms --');
            });

        fetchJSON({ type: 'examinations', year_id: yearId })
            .then(function (list) {
                setOptions(examSel, list, '-- Select Exam --');
            });
    }

    function onTermChange() {
        const yearId = yearSel.value;
        const termId = termSel.value;

        setLoading(examSel, '-- Loading exams… --');
        setOptions(subjSel, [], '-- Select Subject --');

        if (!yearId) {
            setOptions(examSel, [], '-- Select Exam --');
            return;
        }

        fetchJSON({ type: 'examinations', year_id: yearId, term_id: termId })
            .then(function (list) {
                setOptions(examSel, list, '-- Select Exam --');
            })
            .catch(function () {
                setOptions(examSel, [], '-- Failed to load exams --');
            });
    }

    function onExamChange() {
        const examId  = examSel.value;
        const classId = classSel.value;

        setOptions(subjSel, [], '-- Select Subject --');

        fetchJSON({ type: 'subjects', examination_id: examId, class_id: classId })
            .then(function (list) {
                setOptions(subjSel, list, '-- Select Subject --');
            })
            .catch(function () {
                setOptions(subjSel, [], '-- Failed to load subjects --');
            });
    }

    function onClassChange() {
        const classId = classSel.value;

        setLoading(strmSel, '-- Loading streams… --');
        setOptions(subjSel, [], '-- Select Subject --');

        if (!classId) {
            setOptions(strmSel, [], '-- All Streams --');
            return;
        }

        fetchJSON({ type: 'streams', class_id: classId })
            .then(function (list) {
                setOptions(strmSel, list, '-- All Streams --');
            })
            .catch(function () {
                setOptions(strmSel, [], '-- Failed to load streams --');
            });

        if (examSel.value) {
            onExamChange();
        }
    }

    yearSel.addEventListener('change',  onYearChange);
    termSel.addEventListener('change',  onTermChange);
    examSel.addEventListener('change',  onExamChange);
    classSel.addEventListener('change', onClassChange);

    (function () {
        if (yearSel.value) {
            onYearChange();
        }
    })();
})();
</script>