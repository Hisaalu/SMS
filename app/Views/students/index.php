<!-- File: /app/Views/students/index.php -->
<div class="container-fluid px-0">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-user-graduate text-primary me-2"></i>Student Directory</h4>
            <p class="text-muted small mb-0">Total Enrolled: <strong><?= number_format($totalStudents) ?></strong> students</p>
        </div>
        <div>
            <a href="<?= BASE_URL . '/student/create' ?>" class="btn btn-primary btn-sm fw-bold">
                <i class="fas fa-plus me-1"></i> Register Student
            </a>
        </div>
    </div>

    <!-- Multi-Filter Bar -->
    <div class="card card-shadow border-0 mb-4">
        <div class="card-body bg-light rounded-3 p-3">
            <form method="GET" action="<?= BASE_URL . '/students' ?>" class="row g-2 align-items-end">
                
                <!-- Keyword Search -->
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Search Name / Adm / Reg</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search student..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>

                <!-- Academic Year Filter -->
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Academic Year</label>
                    <select name="academic_year_id" class="form-select form-select-sm">
                        <option value="">All Years</option>
                        <?php foreach ($academicYears as $ay): ?>
                            <option value="<?= $ay['id'] ?>" <?= $academicYearId == $ay['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ay['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Class Filter -->
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Class</label>
                    <select name="class_id" class="form-select form-select-sm">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $classId == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Stream Filter -->
                <div class="col-md-1">
                    <label class="form-label fw-bold small text-muted">Stream</label>
                    <select name="stream_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach ($streams as $str): ?>
                            <option value="<?= $str['id'] ?>" <?= $streamId == $str['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($str['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-md-1">
                    <label class="form-label fw-bold small text-muted">Status</label>
                    <select name="status_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach ($statuses as $stat): ?>
                            <option value="<?= $stat['id'] ?>" <?= $statusId == $stat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($stat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-dark btn-sm w-100 fw-bold" title="Apply Filters">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="<?= BASE_URL . '/students' ?>" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>

            </form>
        </div>
    </div>

    <!-- Student Table -->
    <div class="card card-shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Adm No</th>
                            <th>Reg No</th>
                            <th>Student Name</th>
                            <th>Gender</th>
                            <th>Class & Stream</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $st): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($st['admission_number']) ?></td>
                                    <td><?= htmlspecialchars($st['registration_number'] ?? '-') ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name']) ?></td>
                                    <td><?= htmlspecialchars($st['gender']) ?></td>
                                    <td>
                                        <?php if ($st['class_name']): ?>
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($st['class_name']) ?> <?= $st['stream_name'] ? '(' . htmlspecialchars($st['stream_name']) . ')' : '' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($st['category_name'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                            <?= htmlspecialchars($st['status_name'] ?? 'Active') ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL . '/student/show?id=' . $st['id'] ?>" class="btn btn-sm btn-outline-info me-1" title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL . '/student/edit?id=' . $st['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit Student">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fs-2 d-block mb-2"></i>
                                    No students matching the filter criteria.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Footer -->
        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center py-3">
                <span class="small text-muted">Showing page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong></span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php 
                            $queryParams = $_GET; 
                            
                            // Prev link
                            if ($page > 1): 
                                $queryParams['page'] = $page - 1;
                        ?>
                            <li class="page-item">
                                <a class="page-item-link page-link" href="<?= BASE_URL . '/students?' . http_build_query($queryParams) ?>">Previous</a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled"><span class="page-link">Previous</span></li>
                        <?php endif; ?>

                        <!-- Page number links -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): $queryParams['page'] = $i; ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL . '/students?' . http_build_query($queryParams) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next link -->
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

</div>