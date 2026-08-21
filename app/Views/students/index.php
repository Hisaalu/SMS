<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Students</h4>
        <a href="<?= BASE_URL ?>/students/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Student
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Adm No</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['admission_number']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']) ?>
                                    </td>
                                    <td><?= ucfirst(htmlspecialchars($student['gender'])) ?></td>
                                    <td><?= htmlspecialchars($student['category_name']) ?></td>
                                    <td>
                                        <span class="badge bg-success"><?= htmlspecialchars($student['status_name']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/students/show?id=<?= $student['id'] ?>" class="btn btn-sm btn-outline-info">View</a>
                                        <a href="<?= BASE_URL ?>/students/edit?id=<?= $student['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No students found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>