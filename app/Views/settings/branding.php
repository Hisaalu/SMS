<!-- File: /app/Views/settings/branding.php -->
<style>
    .branding-card { 
        padding: 2rem; 
        background: #ffffff; 
        border-radius: 0.5rem; 
        box-shadow: 0 2px 16px rgba(0,0,0,0.05); 
        border: 1px solid #EFF3F4; 
    }
    .form-control, .form-select { 
        padding: 0.45rem 0.75rem; 
        font-size: 0.9rem; 
        border-radius: 0.5rem; 
        border: 1px solid #EFF3F4; 
        box-shadow: none;
    }
    .form-control:focus, .form-select:focus { 
        border-color: #1D9BF0; 
        box-shadow: 0 0 0 3px rgba(29,155,240,0.1); 
        outline: none;
    }
    .form-label { 
        font-size: 0.85rem; 
        font-weight: 500; 
        margin-bottom: 0.2rem; 
    }
    .btn-save { 
        background: #1D9BF0; 
        color: #ffffff !important; 
        border: none; 
        border-radius: 0.5rem; 
        padding: 0.5rem 1.25rem; 
        font-weight: 600; 
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-save:hover { 
        opacity: 0.9; 
        color: #ffffff !important; 
    }
    .btn-save * {
        color: #ffffff !important;
    }
    .alert { 
        padding: 0.5rem 0.75rem; 
        font-size: 0.85rem; 
        border-radius: 0.4rem; 
        transition: opacity 0.5s ease;
    }
    .preview-box {
        width: 64px;
        height: 64px;
        border: 1px solid #EFF3F4;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F7F9F9;
        overflow: hidden;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }
    .preview-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    .btn-delete-check {
        display: none;
    }
    .delete-btn-label {
        border-radius: 0.5rem;
        padding: 0.45rem 0.75rem;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-delete-check:checked + .delete-btn-label {
        background-color: #dc3545 !important;
        color: #ffffff !important;
        border-color: #dc3545 !important;
    }
    .marked-for-deletion {
        opacity: 0.3;
        border-color: #dc3545 !important;
        filter: grayscale(100%);
    }
    .current-file-badge {
        font-size: 0.78rem;
        color: #0f1419;
        background-color: #f7f9f9;
        border: 1px solid #eff3f4;
        border-radius: 0.4rem;
        padding: 0.2rem 0.5rem;
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<?php
// Helper closure to build clean, working image URLs
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
        <a href="<?= BASE_URL ?>/settings" class="btn btn-sm btn-outline-secondary">
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

    <div class="branding-card">
        <form method="POST" action="<?= BASE_URL ?>/settings/branding" enctype="multipart/form-data">
            <!-- CSRF Token -->
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

            <div class="row g-4">
                <!-- System Logo -->
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
                                <input type="file" class="form-control" name="logo" accept="image/*" onchange="previewImage(this, 'preview_logo', 'del_logo', 'badge_logo')">
                                <?php if (!empty($branding['logo'])): ?>
                                    <input type="checkbox" name="delete_logo" value="1" id="del_logo" class="btn-delete-check" onchange="toggleDeleteMark('preview_logo', this, 'badge_logo')">
                                    <label for="del_logo" class="btn btn-outline-danger delete-btn-label d-flex align-items-center gap-1" title="Delete current logo">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </label>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($branding['logo'])): ?>
                                <div class="mt-1 d-flex align-items-center gap-1" id="badge_logo">
                                    <span class="current-file-badge">
                                        <i class="fas fa-paperclip me-1 text-primary"></i>
                                        <?= htmlspecialchars(basename($branding['logo'])) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Recommended: PNG or SVG with transparent background.</div>
                        </div>
                    </div>
                </div>

                <!-- Favicon -->
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
                                <input type="file" class="form-control" name="favicon" accept="image/x-icon,image/png" onchange="previewImage(this, 'preview_favicon', 'del_favicon', 'badge_favicon')">
                                <?php if (!empty($branding['favicon'])): ?>
                                    <input type="checkbox" name="delete_favicon" value="1" id="del_favicon" class="btn-delete-check" onchange="toggleDeleteMark('preview_favicon', this, 'badge_favicon')">
                                    <label for="del_favicon" class="btn btn-outline-danger delete-btn-label d-flex align-items-center gap-1" title="Delete current favicon">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </label>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($branding['favicon'])): ?>
                                <div class="mt-1 d-flex align-items-center gap-1" id="badge_favicon">
                                    <span class="current-file-badge">
                                        <i class="fas fa-paperclip me-1 text-primary"></i>
                                        <?= htmlspecialchars(basename($branding['favicon'])) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Recommended: 32x32 PNG or ICO format.</div>
                        </div>
                    </div>
                </div>

                <!-- Official School Stamp -->
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
                                <input type="file" class="form-control" name="school_stamp" accept="image/*" onchange="previewImage(this, 'preview_stamp', 'del_stamp', 'badge_stamp')">
                                <?php if (!empty($branding['school_stamp'])): ?>
                                    <input type="checkbox" name="delete_school_stamp" value="1" id="del_stamp" class="btn-delete-check" onchange="toggleDeleteMark('preview_stamp', this, 'badge_stamp')">
                                    <label for="del_stamp" class="btn btn-outline-danger delete-btn-label d-flex align-items-center gap-1" title="Delete current stamp">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </label>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($branding['school_stamp'])): ?>
                                <div class="mt-1 d-flex align-items-center gap-1" id="badge_stamp">
                                    <span class="current-file-badge">
                                        <i class="fas fa-paperclip me-1 text-primary"></i>
                                        <?= htmlspecialchars(basename($branding['school_stamp'])) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Used on official reports, receipts, and documents.</div>
                        </div>
                    </div>
                </div>

                <!-- Report Logo -->
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
                                <input type="file" class="form-control" name="report_logo" accept="image/*" onchange="previewImage(this, 'preview_report_logo', 'del_report_logo', 'badge_report_logo')">
                                <?php if (!empty($branding['report_logo'])): ?>
                                    <input type="checkbox" name="delete_report_logo" value="1" id="del_report_logo" class="btn-delete-check" onchange="toggleDeleteMark('preview_report_logo', this, 'badge_report_logo')">
                                    <label for="del_report_logo" class="btn btn-outline-danger delete-btn-label d-flex align-items-center gap-1" title="Delete current report logo">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </label>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($branding['report_logo'])): ?>
                                <div class="mt-1 d-flex align-items-center gap-1" id="badge_report_logo">
                                    <span class="current-file-badge">
                                        <i class="fas fa-paperclip me-1 text-primary"></i>
                                        <?= htmlspecialchars(basename($branding['report_logo'])) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Used on printable student report cards and invoices.</div>
                        </div>
                    </div>
                </div>

                <!-- Footer / Credits Toggle -->
                <div class="col-12 border-top pt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="show_nexat_branding" name="show_nexat_branding" value="1" <?= (!isset($branding['show_nexat_branding']) || $branding['show_nexat_branding']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="show_nexat_branding">
                            Display "Powered by NexaT" in system footer
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn-save">
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

            reader.onload = function(e) {
                const deleteCheckbox = document.getElementById(deleteCheckboxId);
                if (deleteCheckbox) {
                    deleteCheckbox.checked = false;
                    previewContainer.classList.remove('marked-for-deletion');
                }

                // Render live preview inside box immediately
                previewContainer.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';

                // Update or attach current file badge
                let fileBadge = document.getElementById(badgeId);
                if (!fileBadge) {
                    fileBadge = document.createElement('div');
                    fileBadge.id = badgeId;
                    fileBadge.className = 'mt-1 d-flex align-items-center gap-1';
                    input.closest('.flex-grow-1').insertBefore(fileBadge, input.closest('.flex-grow-1').querySelector('.form-text'));
                }
                fileBadge.innerHTML = '<span class="current-file-badge"><i class="fas fa-paperclip me-1 text-primary"></i> ' + input.files[0].name + '</span>';
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

    document.addEventListener('DOMContentLoaded', function () {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function (alert) {
            setTimeout(function () {
                alert.style.opacity = '0';
                setTimeout(function () {
                    alert.remove();
                }, 500);
            }, 4000);
        });
    });
</script>