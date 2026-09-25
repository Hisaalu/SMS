<?php
// File: /app/Views/settings/branding.php
$resolveDiskPath = function (?string $path): ?string {
    if (empty($path)) return null;
    $clean = ltrim(str_replace('\\', '/', $path), '/');

    foreach ([
        ROOT_PATH . '/public/' . $clean,
        ROOT_PATH . '/' . $clean,
    ] as $candidate) {
        if (is_file($candidate)) {
            return $candidate;
        }
    }
    return null;
};

$resolveUrl = function (?string $path) use ($resolveDiskPath): string {
    if (empty($path)) return '';
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    $clean = ltrim(str_replace('\\', '/', $path), '/');

    $diskPath = $resolveDiskPath($clean);
    $isUnderPublic = $diskPath !== null && str_contains($diskPath, '/public/');

    if ($isUnderPublic && !str_contains(BASE_URL, '/public')) {
        return rtrim(BASE_URL, '/') . '/public/' . $clean;
    }

    return rtrim(BASE_URL, '/') . '/' . $clean;
};

$renderUploader = function (string $key, string $label, string $accept, string $hint, string $icon, array $branding) use ($resolveUrl, $resolveDiskPath) {
    $currentPath = $branding[$key] ?? '';
    $hasCurrent  = !empty($currentPath);
    $diskPath    = $hasCurrent ? $resolveDiskPath($currentPath) : null;
    $isBroken    = $hasCurrent && $diskPath === null;
    $currentName = $hasCurrent ? basename($currentPath) : '';
    $inputId     = 'file_' . $key;
    $checkboxId  = 'del_' . $key;
    $statusId    = 'status_' . $key;
    $previewId   = 'preview_' . $key;
    ?>
    <div class="col-12 col-lg-6">
        <div class="brand-row">
            <div class="brand-row-label"><?= htmlspecialchars($label) ?></div>
            <div class="brand-row-content">

                <div class="preview-box" id="<?= $previewId ?>">
                    <?php if ($hasCurrent && !$isBroken): ?>
                        <img src="<?= htmlspecialchars($resolveUrl($currentPath), ENT_QUOTES, 'UTF-8') ?>"
                             alt="<?= htmlspecialchars($label) ?>"
                             loading="lazy">
                    <?php elseif ($isBroken): ?>
                        <i class="fas fa-exclamation-triangle placeholder-icon text-warning"
                           title="Saved path points to a missing file"></i>
                    <?php else: ?>
                        <i class="fas <?= htmlspecialchars($icon) ?> placeholder-icon"></i>
                    <?php endif; ?>
                </div>

                <div class="brand-row-body">

                    <div class="file-control-row">
                        <label for="<?= $inputId ?>" class="file-input-label">
                            <span class="file-input-text" id="filetext_<?= $key ?>">
                                <?= $hasCurrent ? htmlspecialchars($currentName) : 'No file selected' ?>
                            </span>
                            <span class="file-input-btn">Browse…</span>
                        </label>
                        <input type="file"
                               id="<?= $inputId ?>"
                               name="<?= $key ?>"
                               accept="<?= htmlspecialchars($accept) ?>"
                               class="visually-hidden-file"
                               data-key="<?= $key ?>"
                               data-preview="<?= $previewId ?>"
                               data-status="<?= $statusId ?>"
                               data-text="filetext_<?= $key ?>">

                        <?php if ($hasCurrent): ?>
                            <input type="checkbox"
                                   name="delete_<?= $key ?>"
                                   value="1"
                                   id="<?= $checkboxId ?>"
                                   class="btn-delete-check"
                                   onchange="toggleDeleteMark('<?= $previewId ?>', this, '<?= $statusId ?>')">
                            <label for="<?= $checkboxId ?>"
                                   class="btn btn-sm btn-outline-danger btn-delete"
                                   title="Delete current <?= htmlspecialchars(strtolower($label)) ?>">
                                <i class="fas fa-trash-alt"></i>
                            </label>
                        <?php endif; ?>
                    </div>

                    <div class="mt-2" id="<?= $statusId ?>"></div>

                    <div class="form-text">
                        <?= $hint ?>
                        <?php if ($isBroken): ?>
                            <span class="d-block text-warning mt-1">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Saved path "<code><?= htmlspecialchars($currentPath) ?></code>" does not exist on disk.
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
};
?>

<style>
    .branding-page .preview-box {
        width: 96px;
        height: 96px;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        background: var(--surface-color);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
        position: relative;
        transition: border-color 0.2s ease, opacity 0.2s ease, filter 0.2s ease;
    }
    .branding-page .preview-box img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
        padding: 6px;
    }
    .branding-page .preview-box .placeholder-icon {
        color: var(--text-muted);
        font-size: 1.8rem;
        opacity: 0.6;
    }
    .branding-page .preview-box.marked-for-deletion {
        border-color: #DC2626;
        filter: grayscale(1) brightness(0.6);
        opacity: 0.7;
    }
    .branding-page .preview-box.marked-for-deletion::after {
        content: "\f2ed";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #DC2626;
        font-size: 1.6rem;
        background: rgba(220, 38, 38, 0.12);
    }

    .branding-page .brand-row {
        padding: 1rem;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        background: rgba(var(--accent-rgb), 0.02);
        height: 100%;
    }
    .branding-page .brand-row-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.5rem;
    }
    .branding-page .brand-row-content {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    .branding-page .brand-row-body {
        flex: 1 1 auto;
        min-width: 0;
    }

    .branding-page .file-control-row {
        display: flex;
        align-items: stretch;
        gap: 0.5rem;
        flex-wrap: nowrap;
    }
    .branding-page .file-input-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        flex: 1 1 auto;
        min-width: 0;
        margin: 0;
        padding: 0.35rem 0.4rem 0.35rem 0.75rem;
        background: var(--input-bg);
        color: var(--text-color);
        border: 1px solid var(--input-border);
        border-radius: var(--border-radius-base);
        cursor: pointer;
        font-size: 0.85rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .branding-page .file-input-label:hover { border-color: var(--accent-color); }
    .branding-page .file-input-label:focus-within {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.12);
    }
    .branding-page .file-input-text {
        flex: 1 1 auto;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: var(--text-color);
    }
    .branding-page .file-input-btn {
        flex: 0 0 auto;
        padding: 0.2rem 0.55rem;
        font-size: 0.78rem;
        background: var(--surface-color);
        color: var(--text-color);
        border: 1px solid var(--input-border);
        border-radius: 6px;
    }
    .branding-page .file-input-label:hover .file-input-btn { background: var(--border-color); }

    .branding-page .visually-hidden-file {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    .branding-page .file-control-row .btn-delete {
        flex: 0 0 auto;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.35rem 0.6rem;
    }
    .branding-page .btn-delete-check {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 0;
        height: 0;
    }
    .branding-page .pending-file-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.75rem;
        color: var(--text-color);
        background: rgba(var(--accent-rgb), 0.08);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 0.15rem 0.5rem;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .branding-page .pending-file-chip i { color: var(--accent-color) !important; flex-shrink: 0; }
    .branding-page .form-text {
        color: var(--text-muted) !important;
        font-size: 0.75rem;
        margin-top: 0.5rem;
        line-height: 1.4;
    }
    .branding-page code {
        font-size: 0.72rem;
        padding: 0.1rem 0.35rem;
        border-radius: 4px;
        background: rgba(var(--accent-rgb), 0.08);
        color: var(--accent-color);
    }

    @media (max-width: 575.98px) {
        .branding-page .brand-row-content {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }
        .branding-page .preview-box { margin: 0 auto; }
        .branding-page .file-control-row { flex-wrap: wrap; }
        .branding-page .file-control-row .file-input-label { flex: 1 1 100%; }
        .branding-page .file-control-row .btn-delete { flex: 1 1 100%; }
    }
</style>

<div class="container-fluid px-0 branding-page">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="mb-0 fw-bold">Branding Settings</h4>
        <a href="<?= BASE_URL ?>/settings" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if ($flashSuccess = $this->getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($flashSuccess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($flashError = $this->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card p-3 p-md-4">
        <form method="POST" action="<?= BASE_URL ?>/settings/branding" enctype="multipart/form-data">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

            <div class="row g-3">
                <?php
                $renderUploader('logo', 'Main System Logo', 'image/*',
                    'PNG or SVG with transparent background works best.', 'fa-school', $branding);

                $renderUploader('favicon', 'Favicon', 'image/x-icon,image/png',
                    '32×32 PNG or ICO. Shown in the browser tab.', 'fa-globe', $branding);

                $renderUploader('school_stamp', 'Official Stamp / Seal', 'image/*',
                    'Used on official reports, receipts, and documents.', 'fa-stamp', $branding);

                $renderUploader('report_logo', 'Report Sheet Header Logo', 'image/*',
                    'Used on printable student report cards and invoices.', 'fa-file-invoice', $branding);
                ?>

                <div class="col-12">
                    <div class="border-top pt-3 mt-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="show_nexat_branding"
                                   name="show_nexat_branding" value="1"
                                   <?= (!isset($branding['show_nexat_branding']) || $branding['show_nexat_branding']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="show_nexat_branding">
                                Display "Powered by NexaT" in system footer
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i> Save Branding
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.visually-hidden-file').forEach(function (input) {
        input.addEventListener('change', function () {
            const key       = input.dataset.key;
            const previewId = input.dataset.preview;
            const statusId  = input.dataset.status;
            const textId    = input.dataset.text;

            const previewBox = document.getElementById(previewId);
            const statusBox  = document.getElementById(statusId);
            const textSpan   = document.getElementById(textId);
            const deleteBox  = document.getElementById('del_' + key);

            if (!input.files || !input.files[0]) return;

            if (deleteBox) {
                deleteBox.checked = false;
                previewBox.classList.remove('marked-for-deletion');
            }
            if (textSpan) {
                textSpan.textContent = input.files[0].name;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                previewBox.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                if (statusBox) {
                    statusBox.innerHTML =
                        '<span class="pending-file-chip">'
                        + '<i class="fas fa-paperclip"></i>'
                        + '<span class="text-truncate">' + escapeHtml(input.files[0].name) + '</span>'
                        + '</span>';
                }
            };
            reader.readAsDataURL(input.files[0]);
        });
    });

    function toggleDeleteMark(previewId, checkbox, statusId) {
        const previewBox = document.getElementById(previewId);
        if (!previewBox) return;
        if (checkbox.checked) {
            previewBox.classList.add('marked-for-deletion');
        } else {
            previewBox.classList.remove('marked-for-deletion');
        }
        const statusBox = statusId ? document.getElementById(statusId) : null;
        if (statusBox) statusBox.style.opacity = checkbox.checked ? '0.4' : '1';
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
</script>