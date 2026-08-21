<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Streams</h4>
        <a href="<?= BASE_URL ?>/academic/streams/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Stream
        </a>
    </div>

    <?php if ($flash = $this->getFlash('success')): ?>
        <div class="alert alert-success mb-2"><?= $flash ?></div>
    <?php endif; ?>

    <div class="card p-3">
        <?php if (!empty($streams)): ?>
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Stream Name</th>
                        <th>Associated Class</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($streams as $stream): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($stream->name) ?></strong></td>
                            <td><?= htmlspecialchars($classList[$stream->class_id] ?? 'N/A') ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/academic/streams/<?= $stream->id ?>/edit" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted text-center py-3">No streams found.</p>
        <?php endif; ?>
    </div>
</div>