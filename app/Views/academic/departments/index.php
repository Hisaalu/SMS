<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Departments</h4>
        <a href="<?= BASE_URL ?>/academic/departments/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Department
        </a>
    </div>

    <?php if ($flash = $this->getFlash('success')): ?>
        <div class="alert alert-success mb-2"><?= $flash ?></div>
    <?php endif; ?>

    <div class="card p-3">
        <?php if (!empty($departments)): ?>
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Department Name</th>
                        <th>Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $dept): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($dept->name) ?></strong></td>
                            <td><?= htmlspecialchars($dept->code ?? '-') ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/academic/departments/<?= $dept->id ?>/edit" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted text-center py-3">No departments found.</p>
        <?php endif; ?>
    </div>
</div>