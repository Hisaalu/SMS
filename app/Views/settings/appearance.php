<!-- File: /app/Views/settings/appearance.php -->
<?php 
    use NexaT\Core\View;
    $viewInstance = View::getInstance();
    $flashSuccess = $viewInstance->getFlash('success');
    $flashError   = $viewInstance->getFlash('error');
?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Appearance Settings</h4>
        <a href="<?= BASE_URL ?>/settings" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($flashSuccess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> <?= htmlspecialchars($flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card p-3">
        <form method="POST" action="<?= BASE_URL ?>/settings/appearance" id="appearanceForm">
            <div class="row g-3">
                
                <!-- Primary Color -->
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Primary Color</label>
                    <div class="input-group">
                        <input type="color" class="form-control color-picker" data-target="primary" value="<?= htmlspecialchars($theme['primary'] ?? '#000000') ?>" style="width: 50px; padding: 2px;">
                        <input type="text" class="form-control color-text" name="primary" value="<?= htmlspecialchars($theme['primary'] ?? '#000000') ?>" style="flex: 1;">
                    </div>
                </div>

                <!-- Accent Color -->
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Accent Color</label>
                    <div class="input-group">
                        <input type="color" class="form-control color-picker" data-target="accent" value="<?= htmlspecialchars($theme['accent'] ?? '#1D9BF0') ?>" style="width: 50px; padding: 2px;">
                        <input type="text" class="form-control color-text" name="accent" value="<?= htmlspecialchars($theme['accent'] ?? '#1D9BF0') ?>" style="flex: 1;">
                    </div>
                </div>

                <!-- Background Color -->
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Background Color</label>
                    <div class="input-group">
                        <input type="color" class="form-control color-picker" data-target="background" value="<?= htmlspecialchars($theme['background'] ?? '#F7F9F9') ?>" style="width: 50px; padding: 2px;">
                        <input type="text" class="form-control color-text" name="background" value="<?= htmlspecialchars($theme['background'] ?? '#F7F9F9') ?>" style="flex: 1;">
                    </div>
                </div>

                <!-- Text Color -->
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Text Color</label>
                    <div class="input-group">
                        <input type="color" class="form-control color-picker" data-target="text" value="<?= htmlspecialchars($theme['text'] ?? '#0F1419') ?>" style="width: 50px; padding: 2px;">
                        <input type="text" class="form-control color-text" name="text" value="<?= htmlspecialchars($theme['text'] ?? '#0F1419') ?>" style="flex: 1;">
                    </div>
                </div>

                <!-- Success Color -->
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Success Color</label>
                    <div class="input-group">
                        <input type="color" class="form-control color-picker" data-target="success" value="<?= htmlspecialchars($theme['success'] ?? '#198754') ?>" style="width: 50px; padding: 2px;">
                        <input type="text" class="form-control color-text" name="success" value="<?= htmlspecialchars($theme['success'] ?? '#198754') ?>" style="flex: 1;">
                    </div>
                </div>

                <!-- Danger Color -->
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Danger Color</label>
                    <div class="input-group">
                        <input type="color" class="form-control color-picker" data-target="danger" value="<?= htmlspecialchars($theme['danger'] ?? '#DC3545') ?>" style="width: 50px; padding: 2px;">
                        <input type="text" class="form-control color-text" name="danger" value="<?= htmlspecialchars($theme['danger'] ?? '#DC3545') ?>" style="flex: 1;">
                    </div>
                </div>

                <!-- Dark Mode Checkbox -->
                <div class="col-12 mt-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="dark_mode" id="darkMode" value="1" <?= !empty($theme['dark_mode']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold small" for="darkMode">Enable Dark Mode Theme</label>
                    </div>
                </div>
            </div>

            <!-- Live Theme Preview Box -->
            <div class="mt-4 p-3 border rounded preview-box" id="previewContainer" style="background: <?= htmlspecialchars($theme['background'] ?? '#F7F9F9') ?>; color: <?= htmlspecialchars($theme['text'] ?? '#0F1419') ?>;">
                <h6 class="fw-bold mb-2">Live Element Preview</h6>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <button type="button" class="btn text-white btn-preview-primary" style="background: <?= htmlspecialchars($theme['primary'] ?? '#000000') ?>;">Primary</button>
                    <button type="button" class="btn text-white btn-preview-accent" style="background: <?= htmlspecialchars($theme['accent'] ?? '#1D9BF0') ?>;">Accent</button>
                    <button type="button" class="btn text-white btn-preview-success" style="background: <?= htmlspecialchars($theme['success'] ?? '#198754') ?>;">Success</button>
                    <button type="button" class="btn text-white btn-preview-danger" style="background: <?= htmlspecialchars($theme['danger'] ?? '#DC3545') ?>;">Danger</button>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary mt-3">
                <i class="fas fa-save me-1"></i> Save Appearance
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('appearanceForm');

    // Sync input picker with text input and update live preview
    form.querySelectorAll('.color-picker').forEach(picker => {
        picker.addEventListener('input', function() {
            const group = this.closest('.input-group');
            const textInput = group.querySelector('.color-text');
            if (textInput) {
                textInput.value = this.value;
                updatePreview(this.dataset.target, this.value);
            }
        });
    });

    // Sync text input with input picker and update live preview
    form.querySelectorAll('.color-text').forEach(textInput => {
        textInput.addEventListener('input', function() {
            const group = this.closest('.input-group');
            const picker = group.querySelector('.color-picker');
            if (picker && this.value.match(/^#[0-9a-f]{6}$/i)) {
                picker.value = this.value;
                updatePreview(picker.dataset.target, this.value);
            }
        });
    });

    function updatePreview(key, hexColor) {
        const previewContainer = document.getElementById('previewContainer');
        if (key === 'background') {
            previewContainer.style.backgroundColor = hexColor;
        } else if (key === 'text') {
            previewContainer.style.color = hexColor;
        } else {
            const btn = previewContainer.querySelector(`.btn-preview-${key}`);
            if (btn) {
                btn.style.backgroundColor = hexColor;
            }
        }
    }
});
</script>