<!-- File: /app/Views/settings/branding.php -->
<?php
$resolveUrl = function (?string $path): string {
    if (empty($path)) return '';
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
};
?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
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

    <div class="card p-4">
        <form method="POST" action="<?= BASE_URL ?>/settings/branding" enctype="multipart/form-data">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

            <div class="row g-4">

                <div class="col-md-6">
                    <label class="form-label">Main System Logo</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="preview-box" id="preview_logo">
                            <?php if (!empty($branding['logo'])): ?>
                                <img src="<?= $resolveUrl($branding['logo']) ?>" alt="Logo">
                            <?php else: ?>
                                <i class="fas fa-school text-muted fs-4"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <div class="input-group">
                                <input type="file" class="form-control" name="logo" accept="image/*"
                                       onchange="previewImage(this, 'preview_logo', 'del_logo', 'badge_logo')">
                                <?php if (!empty($branding['logo'])): ?>
                                    <input type="checkbox" name="delete_logo" value="1" id="del_logo"
                                           class="btn-delete-check"
                                           onchange="toggleDeleteMark('preview_logo', this, 'badge_logo')">
                                    <label for="del_logo" class="btn btn-outline-danger delete-btn-label d-flex align-items-center gap-1"
                                           title="Delete current logo">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </label>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($branding['logo'])): ?>
                                <div class="mt-1 d-flex align-items-center gap-1" id="badge_logo">
                                    <span class="current-file-badge">
                                        <i class="fas fa-paperclip me-1" style="color: var(--accent-color);"></i>
                                        <?= htmlspecialchars(basename($branding['logo'])) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Recommended: PNG or SVG with transparent background.</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Favicon</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="preview-box" id="preview_favicon">
                            <?php if (!empty($branding['favicon'])): ?>
                                <img src="<?= $resolveUrl($branding['favicon']) ?>" alt="Favicon">
                            <?php else: ?>
                                <i class="fas fa-globe text-muted fs-4"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <div class="input-group">
                                <input type="file" class="form-control" name="favicon"
                                       accept="image/x-icon,image/png"
                                       onchange="previewImage(this, 'preview_favicon', 'del_favicon', 'badge_favicon')">
                                <?php if (!empty($branding['favicon'])): ?>
                                    <input type="checkbox" name="delete_favicon" value="1" id="del_favicon"
                                           class="btn-delete-check"
                                           onchange="toggleDeleteMark('preview_favicon', this, 'badge_favicon')">
                                    <label for="del_favicon" class="btn btn-outline-danger delete-btn-label d-flex align-items-center gap-1"
                                           title="Delete current favicon">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </label>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($branding['favicon'])): ?>
                                <div class="mt-1 d-flex align-items-center gap-1" id="badge_favicon">
                                    <span class="current-file-badge">
                                        <i class="fas fa-paperclip me-1" style="color: var(--accent-color);"></i>
                                        <?= htmlspecialchars(basename($branding['favicon'])) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Recommended: 32x32 PNG or ICO format.</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Official Stamp / Seal</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="preview-box" id="preview_stamp">
                            <?php if (!empty($branding['school_stamp'])): ?>
                                <img src="<?= $resolveUrl($branding['school_stamp']) ?>" alt="Stamp">
                            <?php else: ?>
                                <i class="fas fa-stamp text-muted fs-4"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <div class="input-group">
                                <input type="file" class="form-control" name="school_stamp" accept="image/*"
                                       onchange="previewImage(this, 'preview_stamp', 'del_stamp', 'badge_stamp')">
                                <?php if (!empty($branding['school_stamp'])): ?>
                                    <input type="checkbox" name="delete_school_stamp" value="1" id="del_stamp"
                                           class="btn-delete-check"
                                           onchange="toggleDeleteMark('preview_stamp', this, 'badge_stamp')">
                                    <label for="del_stamp" class="btn btn-outline-danger delete-btn-label d-flex align-items-center gap-1"
                                           title="Delete current stamp">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </label>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($branding['school_stamp'])): ?>
                                <div class="mt-1 d-flex align-items-center gap-1" id="badge_stamp">
                                    <span class="current-file-badge">
                                        <i class="fas fa-paperclip me-1" style="color: var(--accent-color);"></i>
                                        <?= htmlspecialchars(basename($branding['school_stamp'])) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Used on official reports, receipts, and documents.</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Report Sheet Header Logo</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="preview-box" id="preview_report_logo">
                            <?php if (!empty($branding['report_logo'])): ?>
                                <img src="<?= $resolveUrl($branding['report_logo']) ?>" alt="Report Logo">
                            <?php else: ?>
                                <i class="fas fa-file-invoice text-muted fs-4"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <div class="input-group">
                                <input type="file" class="form-control" name="report_logo" accept="image/*"
                                       onchange="previewImage(this, 'preview_report_logo', 'del_report_logo', 'badge_report_logo')">
                                <?php if (!empty($branding['report_logo'])): ?>
                                    <input type="checkbox" name="delete_report_logo" value="1" id="del_report_logo"
                                           class="btn-delete-check"
                                           onchange="toggleDeleteMark('preview_report_logo', this, 'badge_report_logo')">
                                    <label for="del_report_logo" class="btn btn-outline-danger delete-btn-label d-flex align-items-center gap-1"
                                           title="Delete current report logo">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </label>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($branding['report_logo'])): ?>
                                <div class="mt-1 d-flex align-items-center gap-1" id="badge_report_logo">
                                    <span class="current-file-badge">
                                        <i class="fas fa-paperclip me-1" style="color: var(--accent-color);"></i>
                                        <?= htmlspecialchars(basename($branding['report_logo'])) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Used on printable student report cards and invoices.</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 border-top pt-3">
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

            <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i> Save Branding
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(input, previewContainerId, deleteCheckboxId, badgeId) {
        const previewContainer = document.getElementById(previewContainerId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                const deleteCheckbox = document.getElementById(deleteCheckboxId);
                if (deleteCheckbox) {
                    deleteCheckbox.checked = false;
                    previewContainer.classList.remove('marked-for-deletion');
                }

                previewContainer.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';

                let fileBadge = document.getElementById(badgeId);
                if (!fileBadge) {
                    fileBadge = document.createElement('div');
                    fileBadge.id = badgeId;
                    fileBadge.className = 'mt-1 d-flex align-items-center gap-1';
                    input.closest('.flex-grow-1').insertBefore(fileBadge, input.closest('.flex-grow-1').querySelector('.form-text'));
                }
                fileBadge.innerHTML = '<span class="current-file-badge"><i class="fas fa-paperclip me-1" style="color: var(--accent-color);"></i> ' + input.files[0].name + '</span>';
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleDeleteMark(previewId, checkbox, badgeId) {
        const previewBox = document.getElementById(previewId);
        const badge = document.getElementById(badgeId);

        if (checkbox.checked) {
            previewBox.classList.add('marked-for-deletion');
            if (badge) badge.style.opacity = '0.4';
        } else {
            previewBox.classList.remove('marked-for-deletion');
            if (badge) badge.style.opacity = '1';
        }
    }
</script>