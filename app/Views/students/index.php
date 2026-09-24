<!-- File: /app/Views/students/index.php -->
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

                    <div class="col-md-3 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Name, Adm, Reg..." value="<?= htmlspecialchars($search) ?>" onchange="this.form.submit();">
                    </div>

                    <div class="col-md-2 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 40px;">Year</label>
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
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 45px;">Class</label>
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
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 55px;">Stream</label>
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

                    <div class="col-md-1 text-end">
                        <a href="<?= BASE_URL . '/student/create' ?>" class="btn btn-sm btn-primary fw-bold text-nowrap" title="Register Student">
                            <i class="fas fa-plus me-1"></i> Register
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0 text-nowrap small">
                <thead class="sticky-top">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Student No</th>
                        <th>Reg No</th>
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
                                <td><?= $offsetIndex ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($st['admission_number'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($st['registration_number'] ?? '-') ?></td>
                                <td class="fw-bold text-uppercase"><?= htmlspecialchars(($st['last_name'] ?? '') . ' ' . ($st['first_name'] ?? '')) ?></td>
                                <td class="text-center"><?= $sexDisplay ?></td>
                                <td><?= htmlspecialchars($st['class_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($st['stream_name'] ?? 'GENERAL') ?></td>
                                <td><?= htmlspecialchars($st['category_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($st['status_name'] ?? 'Active') ?></td>
                                <td class="text-center">
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
                            <td colspan="10" class="text-center py-4 text-muted">
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