<!-- File: /app/Views/students/index.php -->
<style>
    .stu-table {
        border-collapse: collapse;
        font-size: 0.85rem;
        white-space: nowrap;
        background: #ffffff;
        color: #000000;
    }
    .stu-table thead th {
        background: #f1f5f9;
        color: #1e293b;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-weight: 800;
        border: 1px solid #94a3b8;
        border-bottom: 2px solid #64748b;
        vertical-align: middle;
    }
    .stu-table td {
        border: 1px solid #94a3b8;
        padding: 6px 8px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .stu-table tbody tr:hover td {
        background: rgba(var(--accent-rgb), 0.10);
    }
    .stu-table .stu-num   { text-align: center; color: #475569; }
    .stu-table .stu-adm   { font-weight: 700; color: #000000; }
    .stu-table .stu-name  { font-weight: 700; color: #000000; text-transform: uppercase; }
    .stu-table .stu-center{ text-align: center; color: #000000; }
    .stu-table .stu-actions { text-align: center; white-space: nowrap; }

    /* Non-data status colours stay visible on white */
    .stu-table .badge.bg-success  { background: #16A34A !important; color: #fff !important; }
    .stu-table .badge.bg-secondary{ background: #64748B !important; color: #fff !important; }

    [data-theme="dark"] .stu-table {
        background: var(--surface-color);
        color: var(--text-color);
    }
    [data-theme="dark"] .stu-table thead th {
        background: var(--border-color);
        color: var(--accent-color);
        border: 1px solid var(--border-color);
        border-bottom: 2px solid var(--border-color);
    }
    [data-theme="dark"] .stu-table td {
        background: var(--surface-color);
        color: var(--text-color);
        border: 1px solid var(--border-color);
    }
    [data-theme="dark"] .stu-table tbody tr:hover td {
        background: rgba(var(--accent-rgb), 0.14);
    }
    [data-theme="dark"] .stu-table .stu-num   { color: var(--text-muted); }
    [data-theme="dark"] .stu-table .stu-adm,
    [data-theme="dark"] .stu-table .stu-name,
    [data-theme="dark"] .stu-table .stu-center{ color: var(--text-color); }
</style>

<div class="container-fluid px-0">

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="GET" action="<?= BASE_URL . '/students' ?>" id="filterForm">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-center">

                    <div class="col-md-2 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Name, Adm, Reg..." value="<?= htmlspecialchars($search) ?>" onchange="this.form.submit();">
                    </div>

                    <div class="col-md-2 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Year</label>
                        <select name="academic_year_id" class="form-select form-select-sm" onchange="this.form.submit();">
                            <option value="">-- All --</option>
                            <?php foreach ($academicYears as $ay): ?>
                                <option value="<?= $ay['id'] ?>" <?= $academicYearId == $ay['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ay['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Class</label>
                        <select name="class_id" class="form-select form-select-sm" onchange="this.form.submit();">
                            <option value="">-- All --</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $classId == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Stream</label>
                        <select name="stream_id" class="form-select form-select-sm" onchange="this.form.submit();">
                            <option value="">-- All --</option>
                            <?php foreach ($streams as $str): ?>
                                <option value="<?= $str['id'] ?>" <?= $streamId == $str['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($str['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Section</label>
                        <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit();">
                            <option value="">-- All --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <div class="row mt-2">
                    <div class="col-12 text-end">
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                id="printStudentsBtnTop">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-outline-success"
                                id="exportStudentsBtnTop">
                            <i class="fas fa-file-excel me-1"></i> Export
                        </button>
                        <a href="<?= BASE_URL . '/student/create' ?>" class="btn btn-sm btn-primary fw-bold text-nowrap" title="Register Student">
                            <i class="fas fa-plus me-1"></i> Register
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header bg-white pt-3 pb-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fas fa-users me-2 text-secondary"></i>Students
                <small class="text-muted fw-normal ms-1">(<?= number_format($totalStudents) ?> total)</small>
            </h6>
            <div class="d-flex gap-2">
                <button type="button"
                        class="btn btn-sm btn-outline-secondary no-print"
                        id="printStudentsBtn">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                <button type="button"
                        class="btn btn-sm btn-outline-success no-print"
                        id="exportStudentsBtn">
                    <i class="fas fa-file-excel me-1"></i> Export
                </button>
            </div>
        </div>

        <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
            <table class="table stu-table mb-0">
                <thead class="sticky-top">
                    <tr>
                        <th style="width: 40px;" class="text-center">No</th>
                        <th>Student No</th>
                        <th>Name</th>
                        <th class="text-center">Sex</th>
                        <th>Class</th>
                        <th>Stream</th>
                        <th>Section</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $index => $st): ?>
                            <?php
                                $offsetIndex = (($page - 1) * 15) + ($index + 1);
                                $rawSex = strtoupper(trim($st['gender'] ?? ''));
                                $sexDisplay = in_array($rawSex, ['F', 'FEMALE', '2']) ? 'F' : (in_array($rawSex, ['M', 'MALE', '1']) ? 'M' : '-');
                            ?>
                            <tr>
                                <td class="stu-num"><?= $offsetIndex ?></td>
                                <td class="stu-adm"><?= htmlspecialchars($st['admission_number'] ?? '-') ?></td>
                                <td class="stu-name"><?= htmlspecialchars(($st['last_name'] ?? '') . ' ' . ($st['first_name'] ?? '')) ?></td>
                                <td class="stu-center"><?= $sexDisplay ?></td>
                                <td><?= htmlspecialchars($st['class_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($st['stream_name'] ?? 'GENERAL') ?></td>
                                <td><?= htmlspecialchars($st['category_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($st['status_name'] ?? 'Active') ?></td>
                                <td class="stu-actions">
                                    <a href="<?= BASE_URL . '/student/show?id=' . $st['id'] ?>" class="btn btn-sm btn-secondary py-0 px-1 me-1" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL . '/student/edit?id=' . $st['id'] ?>" class="btn btn-sm btn-secondary py-0 px-1 me-1" title="Edit Student">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= BASE_URL . '/students/admission-letter?id=' . $st['id'] ?>" target="_blank" class="btn btn-sm btn-secondary py-0 px-1" title="Print Admission Letter">
                                        <i class="fas fa-file-lines"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                No students found matching the selected criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-between align-items-center pt-2 px-1">
            <span class="small text-muted">Showing page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong> (Total: <?= number_format($totalStudents) ?>)</span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php
                        $queryParams = $_GET;
                        if ($page > 1):
                            $queryParams['page'] = $page - 1;
                    ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= BASE_URL . '/students?' . http_build_query($queryParams) ?>">Prev</a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled"><span class="page-link">Prev</span></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): $queryParams['page'] = $i; ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL . '/students?' . http_build_query($queryParams) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): $queryParams['page'] = $page + 1; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= BASE_URL . '/students?' . http_build_query($queryParams) ?>">Next</a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled"><span class="page-link">Next</span></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>

</div>

<style>
    @media print {
        .card-header button,
        .no-print,
        form { display: none !important; }
    }
</style>

<script>
(function () {
    const url = '<?= BASE_URL ?>';

    const printBtns  = [
        document.getElementById('printStudentsBtn'),
        document.getElementById('printStudentsBtnTop'),
    ].filter(Boolean);

    const exportBtns = [
        document.getElementById('exportStudentsBtn'),
        document.getElementById('exportStudentsBtnTop'),
    ].filter(Boolean);

    function currentQuery() {
        const params = new URLSearchParams(window.location.search);

        params.delete('page');

        return params.toString();
    }

    function openPrint() {
        const qs = currentQuery();
        window.open(url + '/students/print' + (qs ? '?' + qs : ''), '_blank');
    }

    function openExport() {
        const qs = currentQuery();
        window.open(url + '/students/export' + (qs ? '?' + qs : ''), '_blank');
    }

    printBtns.forEach(function (btn)  { btn.addEventListener('click', openPrint); });
    exportBtns.forEach(function (btn) { btn.addEventListener('click', openExport); });
})();
</script>