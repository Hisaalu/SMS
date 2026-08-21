<!-- File: /app/Views/academic/classes/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Classes</h4>
        <a href="<?= BASE_URL ?>/academic/classes/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Class
        </a>
    </div>

    <div class="card p-3">
        <?php if (!empty($classes)): ?>
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Class Name</th>
                        <th>Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($class->name) ?></strong></td>
                            <td><?= htmlspecialchars($class->code ?? '-') ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/academic/classes/<?= $class->id ?>/edit" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted text-center py-3">No classes found.</p>
        <?php endif; ?>
    </div>
</div>