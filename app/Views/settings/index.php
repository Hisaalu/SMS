<!-- File: /app/Views/settings/index.php -->
<div class="container-fluid px-0">
    <h4 class="mb-3 fw-bold">System Settings</h4>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card p-3 h-100">
                <h6 class="fw-bold mb-2">
                    <i class="fas fa-building me-2" style="color: var(--accent-color);"></i>School Profile
                </h6>
                <p class="small text-muted flex-grow-1">
                    Manage school information, contact details, and address.
                </p>
                <a href="<?= BASE_URL ?>/settings/school" class="btn btn-sm btn-primary mt-2">
                    Configure
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 h-100">
                <h6 class="fw-bold mb-2">
                    <i class="fas fa-paint-brush me-2" style="color: var(--success-color);"></i>Branding
                </h6>
                <p class="small text-muted flex-grow-1">
                    Upload logos, favicon, and manage school branding.
                </p>
                <a href="<?= BASE_URL ?>/settings/branding" class="btn btn-sm btn-primary mt-2">
                    Configure
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 h-100">
                <h6 class="fw-bold mb-2">
                    <i class="fas fa-palette me-2" style="color: var(--warning-color);"></i>Appearance
                </h6>
                <p class="small text-muted flex-grow-1">
                    Customize colors, theme, and dark mode settings.
                </p>
                <a href="<?= BASE_URL ?>/settings/appearance" class="btn btn-sm btn-primary mt-2">
                    Configure
                </a>
            </div>
        </div>
    </div>
</div>