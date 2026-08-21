<!-- File: /app/Views/settings/index.php -->
<div class="container-fluid px-0">
    <h4 class="mb-3">System Settings</h4>
    
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card p-3">
                <h6><i class="fas fa-building me-2 text-primary"></i>School Profile</h6>
                <p class="small text-muted">Manage school information, contact details, and address.</p>
                <a href="<?= BASE_URL ?>/settings/school" class="btn btn-sm btn-outline-primary">Configure</a>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card p-3">
                <h6><i class="fas fa-paint-brush me-2 text-success"></i>Branding</h6>
                <p class="small text-muted">Upload logos, favicon, and manage school branding.</p>
                <a href="<?= BASE_URL ?>/settings/branding" class="btn btn-sm btn-outline-primary">Configure</a>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card p-3">
                <h6><i class="fas fa-palette me-2 text-warning"></i>Appearance</h6>
                <p class="small text-muted">Customize colors, theme, and dark mode settings.</p>
                <a href="<?= BASE_URL ?>/settings/appearance" class="btn btn-sm btn-outline-primary">Configure</a>
            </div>
        </div>
    </div>
</div>