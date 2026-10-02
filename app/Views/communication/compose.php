<!-- File: /app/Views/communication/compose.php -->
<?php $audience = $audience ?? 'parents'; ?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-nowrap mb-3 gap-2">
        <div class="flex-grow-1">
            <h4 class="mb-0 fw-bold"><?= $audience === 'staff' ? 'Compose Message' : 'Compose Message' ?></h4>
            <small class="text-muted">
                <?= $audience === 'staff'
                    ? 'Send staff members Messages'
                    : 'Message parents & Gurdians' ?>
            </small>
        </div>
        <a href="<?= BASE_URL ?><?= $audience === 'staff' ? '/staff/communication' : '/communication' ?>"
           class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/communication/preview" id="composeForm">
        <input type="hidden" name="audience" value="<?= htmlspecialchars($audience) ?>">

        <div class="card mb-3">
            <div class="card-header fw-bold"><i class="fas fa-filter me-2 text-secondary"></i>1. Scope</div>
            <div class="card-body">
                <?php if ($audience === 'staff'): ?>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Staff Category</label>
                            <select name="staff_category_id" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                <?php foreach (($staffFilters['staff_categories'] ?? []) as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Staff Status</label>
                            <select name="staff_status_id" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                <?php foreach (($staffFilters['staff_statuses'] ?? []) as $s): ?>
                                    <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Department</label>
                            <select name="department_id" class="form-select form-select-sm">
                                <option value="">All Departments</option>
                                <?php foreach (($staffFilters['departments'] ?? []) as $d): ?>
                                    <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Academic Year <span class="text-danger">*</span></label>
                            <select name="academic_year_id" id="cm_year" class="form-select form-select-sm" required>
                                <option value="">Select Year</option>
                                <?php foreach ($filters['academic_years'] as $y): ?>
                                    <option value="<?= (int)$y['id'] ?>"><?= htmlspecialchars($y['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Term <span class="text-danger">*</span></label>
                            <select name="term_id" id="cm_term" class="form-select form-select-sm" required>
                                <option value="">Select Year First</option>
                                <?php foreach ($filters['terms'] as $t): ?>
                                    <option value="<?= (int)$t['id'] ?>" data-year="<?= (int)($t['academic_year_id'] ?? 0) ?>">
                                        <?= htmlspecialchars($t['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Section</label>
                            <select name="section_id" class="form-select form-select-sm">
                                <option value="">-- All --</option>
                                <?php foreach ($filters['sections'] as $s): ?>
                                    <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Class</label>
                            <select name="class_id" id="cm_class" class="form-select form-select-sm">
                                <option value="">-- All --</option>
                                <?php foreach ($filters['classes'] as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Stream</label>
                            <select name="stream_id" id="cm_stream" class="form-select form-select-sm">
                                <option value="">-- All --</option>
                                <?php foreach ($filters['streams'] as $st): ?>
                                    <option value="<?= (int)$st['id'] ?>" data-class="<?= (int)($st['class_id'] ?? 0) ?>">
                                        <?= htmlspecialchars($st['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header fw-bold"><i class="fas fa-envelope me-2 text-secondary"></i>2. Message</div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Message Type</label>
                        <select name="message_type" id="cm_type" class="form-select form-select-sm">
                            <?php foreach ($messageTypes as $key => $tpl): ?>
                                <option value="<?= htmlspecialchars($key) ?>"
                                        data-subject="<?= htmlspecialchars($tpl['subject']) ?>"
                                        data-body="<?= htmlspecialchars($tpl['body']) ?>">
                                    <?= htmlspecialchars($tpl['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Channel</label>
                        <select name="channel" class="form-select form-select-sm">
                            <option value="sms">SMS</option>
                            <option value="email">Email</option>
                            <option value="both">SMS + Email</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Event Date <small class="text-muted">(optional)</small></label>
                        <input type="date" name="event_date" class="form-control form-control-sm">
                    </div>
                    <?php if ($audience !== 'staff'): ?>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Fees Due Date <small class="text-muted">(optional)</small></label>
                            <input type="date" name="fees_due_date" class="form-control form-control-sm">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold">Subject</label>
                    <input type="text" name="subject" id="cm_subject" class="form-control form-control-sm" required>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold">Message Body</label>
                    <textarea name="body" id="cm_body" class="form-control" rows="10" required></textarea>
                    <small class="text-muted d-block mt-1">
                        <?php if ($audience === 'staff'): ?>
                            Placeholders:
                            <code>{{staff_name}}</code>, <code>{{staff_number}}</code>,
                            <code>{{staff_department}}</code>, <code>{{staff_category}}</code>, <code>{{staff_status}}</code>,
                            <code>{{term}}</code>, <code>{{academic_year}}</code>, <code>{{event_date}}</code>,
                            <code>{{custom_subject}}</code>, <code>{{custom_note}}</code>,
                            <code>{{today}}</code>, <code>{{school_name}}</code>
                        <?php else: ?>
                            Placeholders:
                            <code>{{student_name}}</code>, <code>{{guardian_name}}</code>, <code>{{admission_number}}</code>,
                            <code>{{class}}</code>, <code>{{stream}}</code>, <code>{{section}}</code>,
                            <code>{{term}}</code>, <code>{{academic_year}}</code>, <code>{{school_name}}</code>,
                            <code>{{fees_balance}}</code>, <code>{{fees_due_date}}</code>, <code>{{event_date}}</code>,
                            <code>{{custom_subject}}</code>, <code>{{custom_note}}</code>, <code>{{today}}</code>
                        <?php endif; ?>
                    </small>
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Custom Subject <small class="text-muted">(used by {{custom_subject}})</small></label>
                        <input type="text" name="custom_subject" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Custom Note <small class="text-muted">(used by {{custom_note}})</small></label>
                        <input type="text" name="custom_note" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-eye me-1"></i> Preview
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    const typeSel   = document.getElementById('cm_type');
    const subjInput = document.getElementById('cm_subject');
    const bodyInput = document.getElementById('cm_body');
    const yearSel   = document.getElementById('cm_year');
    const termSel   = document.getElementById('cm_term');
    const classSel  = document.getElementById('cm_class');
    const strmSel   = document.getElementById('cm_stream');

    if (typeSel) {
        typeSel.addEventListener('change', function () {
            const opt = typeSel.options[typeSel.selectedIndex];
            subjInput.value = opt.getAttribute('data-subject') || '';
            bodyInput.value = opt.getAttribute('data-body') || '';
        });
        typeSel.dispatchEvent(new Event('change'));
    }

    if (yearSel && termSel) {
        const allTerms = Array.from(termSel.querySelectorAll('option[data-year]'));
        yearSel.addEventListener('change', function () {
            const y = yearSel.value;
            termSel.innerHTML = y ? '<option value="">Select Term</option>' : '<option value="">Select Year First</option>';
            if (!y) return;
            allTerms.forEach(function (opt) {
                if (opt.getAttribute('data-year') === y) termSel.appendChild(opt.cloneNode(true));
            });
        });
    }

    if (classSel && strmSel) {
        const allStreams = Array.from(strmSel.querySelectorAll('option[data-class]'));
        classSel.addEventListener('change', function () {
            const c = classSel.value;
            strmSel.innerHTML = '<option value="">-- All --</option>';
            allStreams.forEach(function (opt) {
                if (!c || opt.getAttribute('data-class') === c) strmSel.appendChild(opt.cloneNode(true));
            });
        });
    }
})();
</script>