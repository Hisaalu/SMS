<?php
// File: /app/Views/settings/appearance.php
use NexaT\Core\View;

$viewInstance = View::getInstance();
$flashSuccess = $viewInstance->getFlash('success');
$flashError   = $viewInstance->getFlash('error');

$colorSwatches = [
    '#2563EB' => 'Blue',
    '#EAB308' => 'Yellow',
    '#EC4899' => 'Pink',
    '#7f2677' => 'Purple',
    '#f06724' => 'Orange',
    '#23a74c' => 'Green',
];

$fontSizePresets = [
    1 => 0.75,
    2 => 0.875,
    3 => 1.0,
    4 => 1.125,
    5 => 1.25,
];

$currentFontSize = (float) ($theme['font_size_base'] ?? 0.875);
$currentPreset   = 2;
$closestDelta    = PHP_FLOAT_MAX;
foreach ($fontSizePresets as $step => $size) {
    $delta = abs($size - $currentFontSize);
    if ($delta < $closestDelta) {
        $closestDelta  = $delta;
        $currentPreset = $step;
    }
}

$isDarkMode    = !empty($theme['dark_mode']);
$currentAccent = strtolower((string) ($theme['accent'] ?? '#2563EB'));

$isPresetColor = in_array($currentAccent, array_map('strtolower', array_keys($colorSwatches)), true);
$customColor   = $isPresetColor ? '' : $currentAccent;
?>

<style>
    .appearance-shell {
        max-width: 720px;
        margin: 0 auto;
    }

    .appearance-section {
        margin-bottom: 2.25rem;
    }

    .appearance-section h5 {
        font-weight: 700;
        font-size: 1.15rem;
        margin-bottom: 1.1rem;
        color: var(--text-color);
    }

    .font-slider-wrap {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .font-slider-wrap .aa-small { font-size: 0.85rem; font-weight: 600; }
    .font-slider-wrap .aa-large { font-size: 1.4rem;  font-weight: 700; }

    .font-slider-wrap input[type="range"] {
        flex: 1;
        -webkit-appearance: none;
        appearance: none;
        height: 4px;
        background: var(--border-color);
        border-radius: 2px;
        outline: none;
    }

    .font-slider-wrap input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--accent-color);
        cursor: pointer;
        border: 2px solid var(--surface-color);
        box-shadow: 0 0 0 2px var(--accent-color);
    }

    .font-slider-wrap input[type="range"]::-moz-range-thumb {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--accent-color);
        cursor: pointer;
        border: 2px solid var(--surface-color);
        box-shadow: 0 0 0 2px var(--accent-color);
    }

    .color-swatches {
        display: flex;
        gap: 1.25rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .color-swatch {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        cursor: pointer;
        border: none;
        position: relative;
        transition: transform 0.15s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 0.95rem;
        padding: 0;
    }

    .color-swatch:hover { transform: scale(1.08); }

    .color-swatch.selected::after {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        font-size: 0.95rem;
    }

    .color-swatch.custom {
        background: conic-gradient(
            #ff0000, #ffff00, #00ff00, #00ffff, #0000ff, #ff00ff, #ff0000
        );
        position: relative;
        overflow: hidden;
    }

    .color-swatch.custom::before {
        content: '';
        position: absolute;
        inset: 6px;
        border-radius: 50%;
        background: var(--surface-color);
    }

    .color-swatch.custom .plus {
        position: relative;
        z-index: 1;
        color: var(--text-color);
        font-size: 1rem;
        font-weight: 700;
    }

    .color-swatch.custom.selected::after {
        content: '';
    }

    .custom-color-input {
        position: absolute;
        width: 0;
        height: 0;
        opacity: 0;
        pointer-events: none;
    }

    .custom-color-preview {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.75rem;
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .custom-color-preview .chip {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        border: 1px solid var(--border-color);
        display: inline-block;
        background: <?= htmlspecialchars($customColor ?: $currentAccent) ?>;
    }

    .background-options {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .bg-option {
        border: 2px solid var(--border-color);
        border-radius: 0.5rem;
        padding: 1.5rem 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        font-weight: 600;
        transition: border-color 0.15s ease, background 0.15s ease;
        background: var(--surface-color);
        color: var(--text-color);
    }

    .bg-option:hover { border-color: var(--accent-color); }

    .bg-option.selected {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 1px var(--accent-color);
    }

    .bg-option .bg-circle {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 2px solid var(--border-color);
        background: transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        color: #fff;
    }

    .bg-option.selected .bg-circle {
        background: var(--accent-color);
        border-color: var(--accent-color);
    }

    .bg-option.selected .bg-circle::after {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
    }

    .system-toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .system-toggle-row .label-main {
        font-weight: 700;
        color: var(--text-color);
        margin-bottom: 0.15rem;
    }

    .system-toggle-row .label-sub {
        color: var(--text-muted);
        font-size: 0.875rem;
    }

    .save-bar {
        display: flex;
        justify-content: flex-end;
        margin-top: 2rem;
    }
</style>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Appearance</h4>
        <a href="<?= BASE_URL ?>/settings" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($flashSuccess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="fas fa-exclamation-circle me-1"></i> <?= htmlspecialchars($flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/settings/appearance" id="appearanceForm" class="appearance-shell">

        <div class="appearance-section">
            <h5>Font size</h5>
            <div class="font-slider-wrap">
                <span class="aa-small">Aa</span>
                <input type="range"
                       name="font_preset"
                       id="fontSlider"
                       min="1"
                       max="5"
                       step="1"
                       value="<?= $currentPreset ?>">
                <span class="aa-large">Aa</span>
            </div>
            <input type="hidden" name="font_size_base" id="fontSizeValue"
                   value="<?= $currentFontSize ?>">
        </div>

        <div class="appearance-section">
            <h5>Color</h5>
            <div class="color-swatches" id="colorSwatches">
                <?php foreach ($colorSwatches as $hex => $name): ?>
                    <button type="button"
                            class="color-swatch <?= $isPresetColor && strcasecmp($currentAccent, $hex) === 0 ? 'selected' : '' ?>"
                            style="background: <?= $hex ?>;"
                            data-color="<?= $hex ?>"
                            title="<?= htmlspecialchars($name) ?>"
                            aria-label="<?= htmlspecialchars($name) ?>"></button>
                <?php endforeach; ?>

                <button type="button"
                        class="color-swatch custom <?= !$isPresetColor ? 'selected' : '' ?>"
                        id="customSwatch"
                        title="Custom colour"
                        aria-label="Custom colour">
                    <span class="plus"><i class="fas fa-plus"></i></span>
                </button>

                <input type="color"
                       id="customColorPicker"
                       class="custom-color-input"
                       value="<?= htmlspecialchars($customColor ?: $currentAccent) ?>"
                       aria-label="Pick custom colour">
            </div>

            <div class="custom-color-preview">
                <span class="chip" id="customColorChip" style="background: <?= htmlspecialchars($customColor ?: $currentAccent) ?>;"></span>
                <span id="customColorLabel"><?= htmlspecialchars($customColor ?: $currentAccent) ?></span>
            </div>

            <input type="hidden" name="accent" id="accentValue"
                   value="<?= htmlspecialchars($currentAccent) ?>">
        </div>

        <div class="appearance-section">
            <h5>Background</h5>
            <div class="background-options">
                <div class="bg-option <?= !$isDarkMode ? 'selected' : '' ?>"
                     data-mode="light" role="button" tabindex="0">
                    <span class="bg-circle"></span>
                    <span>Default</span>
                </div>
                <div class="bg-option <?= $isDarkMode ? 'selected' : '' ?>"
                     data-mode="dark" role="button" tabindex="0">
                    <span class="bg-circle"></span>
                    <span>Lights out</span>
                </div>
            </div>
            <input type="hidden" name="dark_mode" id="darkModeValue"
                   value="<?= $isDarkMode ? '1' : '0' ?>">
        </div>

        <div class="appearance-section">
            <div class="system-toggle-row">
                <div>
                    <div class="label-main">Use system setting</div>
                    <div class="label-sub">Choose your preferred theme</div>
                </div>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input"
                           type="checkbox"
                           role="switch"
                           id="systemSetting"
                           name="use_system_setting"
                           value="1"
                           <?= !empty($theme['use_system_setting']) ? 'checked' : '' ?>>
                </div>
            </div>
        </div>

        <div class="save-bar">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i> Save
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fontSlider     = document.getElementById('fontSlider');
    const fontSizeValue  = document.getElementById('fontSizeValue');
    const accentValue    = document.getElementById('accentValue');
    const darkModeValue  = document.getElementById('darkModeValue');

    const swatches       = document.querySelectorAll('#colorSwatches .color-swatch');
    const customSwatch   = document.getElementById('customSwatch');
    const customPicker   = document.getElementById('customColorPicker');
    const customChip     = document.getElementById('customColorChip');
    const customLabel    = document.getElementById('customColorLabel');

    const fontPresets = {1: 0.75, 2: 0.875, 3: 1.0, 4: 1.125, 5: 1.25};

    function applyFontSize(size) {
        const root = document.documentElement;
        root.style.setProperty('--font-size-base',  size + 'rem');
        root.style.setProperty('--font-size-small', (size * 0.85).toFixed(3) + 'rem');
        root.style.setProperty('--font-size-heading', (size * 1.4).toFixed(3) + 'rem');
    }

    fontSlider.addEventListener('input', function () {
        const size = fontPresets[this.value] || 0.875;
        fontSizeValue.value = size;
        applyFontSize(size);
    });

    function clearSelection() {
        swatches.forEach(el => el.classList.remove('selected'));
    }

    function applyAccent(color) {
        const c = String(color).toLowerCase();
        accentValue.value = c;
        document.documentElement.style.setProperty('--accent-color', c);
        customChip.style.background = c;
        customLabel.textContent = c;
    }

    swatches.forEach(function (btn) {
        if (btn === customSwatch) return;
        btn.addEventListener('click', function () {
            clearSelection();
            this.classList.add('selected');
            applyAccent(this.dataset.color);
        });
    });

    customSwatch.addEventListener('click', function () {
        customPicker.click();
    });

    customPicker.addEventListener('input', function () {
        clearSelection();
        customSwatch.classList.add('selected');
        applyAccent(this.value);
    });

    document.querySelectorAll('.bg-option').forEach(function (opt) {
        const choose = function () {
            document.querySelectorAll('.bg-option').forEach(el => el.classList.remove('selected'));
            opt.classList.add('selected');
            darkModeValue.value = opt.dataset.mode === 'dark' ? '1' : '0';
        };
        opt.addEventListener('click', choose);
        opt.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                choose();
            }
        });
    });
});
</script>