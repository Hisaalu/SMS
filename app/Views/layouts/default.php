<?php
// File: /app/Views/layouts/default.php

$settingsService = new \NexaT\Core\SettingsService();
$themeService    = new \NexaT\Core\ThemeService($settingsService);
$googleFonts     = $themeService->googleFontsLink();
$cssVariables    = $themeService->cssVariables();

$customFavicon = $settingsService->get('branding.favicon', '');
$customLogo    = $settingsService->get('branding.logo', '');

$resolveDiskPath = static function (?string $relativePath): ?string {
    if (empty($relativePath)) {
        return null;
    }
    $relativePath = ltrim($relativePath, '/');
    foreach ([ROOT_PATH . '/public/' . $relativePath, ROOT_PATH . '/' . $relativePath] as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    return null;
};

$resolveUrl = static function (string $relativePath) use ($resolveDiskPath): string {
    $clean = ltrim($relativePath, '/');
    if ($resolveDiskPath($clean) && !str_contains(BASE_URL, '/public')) {
        return rtrim(BASE_URL, '/') . '/public/' . $clean;
    }
    return rtrim(BASE_URL, '/') . '/' . $clean;
};

$faviconUrl  = $resolveDiskPath($customFavicon) ? $resolveUrl($customFavicon) : null;
$logoUrl     = $resolveDiskPath($customLogo)    ? $resolveUrl($customLogo)    : null;
$schoolTitle = htmlspecialchars($schoolName ?? 'NexaT School', ENT_QUOTES, 'UTF-8');
$userName    = htmlspecialchars($user->first_name ?? 'User', ENT_QUOTES, 'UTF-8');

$initial     = strtoupper(substr($user->first_name ?? '', 0, 1));
$userInitial = $initial !== '' ? $initial : 'U';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $schoolTitle ?> - Dashboard</title>

    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme-mode') || 'system';
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                var resolved = stored === 'system' ? (prefersDark ? 'dark' : 'light') : stored;
                document.documentElement.setAttribute('data-theme', resolved);
                document.documentElement.setAttribute('data-theme-mode', stored);
            } catch (e) {}
        })();
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?php if ($googleFonts): ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="<?= htmlspecialchars($googleFonts) ?>" rel="stylesheet">
    <?php endif; ?>

    <?php if ($faviconUrl): ?>
        <link rel="icon" href="<?= $faviconUrl ?>" type="image/x-icon">
    <?php endif; ?>

    <style>
        <?= $cssVariables ?>

        :root {
            color-scheme: light;
        }
        [data-theme="dark"] {
            color-scheme: dark;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            background: var(--background-color);
            color: var(--text-color);
            font-family: var(--font-family-base);
            font-size: var(--font-size-base);
            line-height: var(--line-height-base);
            font-weight: var(--font-weight-normal);
            overflow-x: hidden;
        }

        @media (prefers-reduced-motion: no-preference) {
            body { transition: background-color 0.2s ease, color 0.2s ease; }
        }

        h1, h2, h3, h4, h5, h6 {
            font-weight: var(--font-weight-bold);
            color: var(--text-color);
        }
        h5 { font-size: var(--font-size-heading); }

        /* ============================================================
           NAVBAR
           ============================================================ */
        .navbar-custom {
            background: var(--navbar-bg);
            min-height: 60px;
            padding: 0.5rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
        }

        .navbar-brand {
            color: var(--navbar-text) !important;
            font-weight: var(--font-weight-bold);
            font-size: calc(var(--font-size-base) * 1.25);
            letter-spacing: -0.3px;
        }

        .navbar-custom a,
        .navbar-custom .dropdown-toggle,
        .navbar-custom .text-secondary,
        .navbar-custom .text-dark {
            color: var(--navbar-text) !important;
        }

        /* Theme toggle button */
        .theme-toggle {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--navbar-text);
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
            padding: 0;
        }
        .theme-toggle:hover {
            background: var(--border-color);
            border-color: var(--accent-color);
            color: var(--accent-color);
        }
        .theme-toggle:focus-visible {
            outline: 2px solid var(--accent-color);
            outline-offset: 2px;
        }

        /* ============================================================
           SIDEBAR
           ============================================================ */
        .desktop-sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 60px;
            bottom: 0;
            left: 0;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            z-index: 1020;
        }

        .desktop-sidebar::-webkit-scrollbar { width: 5px; }
        .desktop-sidebar::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }

        .sidebar-section-title {
            font-size: var(--font-size-small);
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 0.85rem 1rem 0.3rem 1rem;
            letter-spacing: 0.8px;
            font-weight: var(--font-weight-bold);
        }

        .sidebar-menu-wrapper .nav-link {
            padding: 0.5rem 0.75rem;
            font-size: var(--font-size-base);
            color: var(--sidebar-text);
            border-radius: var(--border-radius-base);
            margin: 0.125rem 0.5rem;
            font-weight: var(--font-weight-medium);
            display: flex;
            align-items: center;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .sidebar-menu-wrapper .nav-link:hover {
            background: rgba(37, 99, 235, 0.06);
            color: var(--accent-color);
        }

        .sidebar-menu-wrapper .nav-link.active {
            background: var(--sidebar-active-bg);
            color: var(--sidebar-active-text) !important;
            font-weight: var(--font-weight-bold);
        }

        .sidebar-menu-wrapper .sub-link {
            font-size: var(--font-size-small);
            color: var(--text-muted);
        }

        .sidebar-menu-wrapper .sub-link:hover {
            color: var(--accent-color);
            background: rgba(37, 99, 235, 0.04);
        }

        .sidebar-menu-wrapper .sub-link.active {
            background: rgba(37, 99, 235, 0.1);
            color: var(--accent-color) !important;
            font-weight: var(--font-weight-bold);
        }

        .menu-icon {
            width: 1.5rem;
            text-align: center;
            font-size: calc(var(--font-size-base) * 1.05);
            margin-right: 0.6rem;
            color: var(--text-muted);
        }

        .sidebar-menu-wrapper .nav-link.active .menu-icon,
        .sidebar-menu-wrapper .nav-link:hover .menu-icon { color: inherit; }

        .sub-menu-icon {
            width: 1.2rem;
            text-align: center;
            font-size: calc(var(--font-size-small) * 1.05);
            margin-right: 0.5rem;
        }

        .chevron-icon {
            transition: transform 0.2s ease;
            font-size: calc(var(--font-size-small) * 0.85);
            color: var(--text-muted);
        }

        [aria-expanded="true"] .chevron-icon { transform: rotate(90deg); }

        .sidebar-dropdown-toggle { color: var(--sidebar-text); }

        .sidebar-dropdown-toggle.active-group {
            font-weight: var(--font-weight-bold);
            color: var(--accent-color);
        }

        /* ============================================================
           MAIN CONTENT
           ============================================================ */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            padding: 1.5rem;
            min-height: calc(100vh - 60px);
        }

        @media (max-width: 991.98px) {
            .main-wrapper { margin-left: 0; padding: 1rem; }
        }

        /* ============================================================
           CARDS
           ============================================================ */
        .card {
            background: var(--surface-color);
            border: 1px solid var(--border-color) !important;
            border-radius: var(--border-radius-base);
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            color: var(--text-color);
        }

        .card-header {
            background: var(--surface-color);
            border-bottom: 1px solid var(--border-color);
            color: var(--text-color);
        }

        /* ============================================================
           BACKGROUND UTILITIES
           ============================================================ */
        .bg-white,
        .bg-light,
        .bg-body,
        .bg-body-tertiary {
            background: var(--surface-color) !important;
            color: var(--text-color);
        }

        /* ============================================================
           FORMS
           ============================================================ */
        .form-label,
        .form-check-label,
        label {
            color: var(--text-color);
        }

        .form-control,
        .form-select,
        textarea.form-control {
            background: var(--surface-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-base);
        }

        .form-control::placeholder { color: var(--text-muted); opacity: 0.7; }

        .form-control:focus,
        .form-select:focus {
            background: var(--surface-color);
            color: var(--text-color);
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
        }

        .form-select option {
            background: var(--surface-color);
            color: var(--text-color);
        }

        .form-control-sm,
        .form-select-sm {
            background: var(--surface-color);
            color: var(--text-color);
        }

        .input-group-text {
            background: var(--background-color);
            color: var(--text-muted);
            border: 1px solid var(--border-color);
        }

        /* Toggle switch */
        .form-check-input {
            background-color: var(--border-color);
            border-color: var(--border-color);
        }
        .form-check-input:checked {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        .form-check-input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
        }

        /* ============================================================
           TABLES
           ============================================================ */
        .table {
            color: var(--text-color);
            border-color: var(--border-color);
        }

        .table thead th,
        .table > :not(caption) > * > * {
            color: var(--text-color);
            background: transparent;
            border-bottom-color: var(--border-color);
        }

        .table-light,
        .table-secondary,
        .table > thead.table-light > tr > th,
        .table > thead.table-secondary > tr > th {
            background: var(--background-color) !important;
            color: var(--text-color) !important;
            border-color: var(--border-color) !important;
        }

        .table-bordered,
        .table-bordered > :not(caption) > * {
            border-color: var(--border-color);
        }

        .table-hover > tbody > tr:hover > * {
            background-color: rgba(37, 99, 235, 0.06);
            color: var(--text-color);
        }

        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: rgba(0, 0, 0, 0.02);
            color: var(--text-color);
        }

        /* ============================================================
           ALERTS
           ============================================================ */
        .alert {
            border-radius: var(--border-radius-base);
            border: 1px solid transparent;
        }
        .alert-success {
            background: rgba(0, 186, 124, 0.12);
            color: var(--success-color);
            border-color: rgba(0, 186, 124, 0.25);
        }
        .alert-danger {
            background: rgba(244, 33, 46, 0.12);
            color: var(--danger-color);
            border-color: rgba(244, 33, 46, 0.25);
        }
        .alert-warning {
            background: rgba(255, 212, 0, 0.15);
            color: #a37c00;
            border-color: rgba(255, 212, 0, 0.3);
        }
        .alert-info {
            background: rgba(37, 99, 235, 0.12);
            color: var(--accent-color);
            border-color: rgba(37, 99, 235, 0.25);
        }
        .alert-light {
            background: var(--background-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }
        .alert-light strong { color: var(--text-color); }

        /* ============================================================
           BUTTONS
           ============================================================ */
        .btn-primary {
            background: var(--accent-color);
            border-color: var(--accent-color);
            color: #fff;
        }
        .btn-primary:hover,
        .btn-primary:focus {
            background: var(--accent-color);
            border-color: var(--accent-color);
            color: #fff;
            box-shadow: inset 0 0 0 1000px rgba(0, 0, 0, 0.08);
        }

        .btn-secondary,
        .btn-light {
            background: var(--surface-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }
        .btn-secondary:hover,
        .btn-secondary:focus,
        .btn-light:hover,
        .btn-light:focus {
            background: var(--border-color);
            color: var(--text-color);
            border-color: var(--border-color);
        }

        .btn-outline-secondary {
            color: var(--text-color);
            border-color: var(--border-color);
            background: transparent;
        }
        .btn-outline-secondary:hover,
        .btn-outline-secondary:focus {
            background: var(--border-color);
            color: var(--text-color);
            border-color: var(--border-color);
        }

        /* ============================================================
           BADGES
           ============================================================ */
        .badge.bg-secondary {
            background: var(--text-muted) !important;
            color: #fff;
        }

        .badge.bg-primary-subtle {
            background: rgba(37, 99, 235, 0.15);
            color: var(--accent-color);
            border: 1px solid rgba(37, 99, 235, 0.3);
        }
        .badge.bg-success-subtle {
            background: rgba(0, 186, 124, 0.15);
            color: var(--success-color);
            border: 1px solid rgba(0, 186, 124, 0.3);
        }
        .badge.bg-danger-subtle {
            background: rgba(244, 33, 46, 0.15);
            color: var(--danger-color);
            border: 1px solid rgba(244, 33, 46, 0.3);
        }
        .badge.bg-warning-subtle {
            background: rgba(255, 212, 0, 0.18);
            color: #a37c00;
            border: 1px solid rgba(255, 212, 0, 0.35);
        }
        .badge.bg-info-subtle {
            background: rgba(13, 202, 240, 0.15);
            color: #0aa2c0;
            border: 1px solid rgba(13, 202, 240, 0.3);
        }
        .badge.bg-secondary-subtle {
            background: rgba(100, 116, 139, 0.18);
            color: var(--text-muted);
            border: 1px solid rgba(100, 116, 139, 0.3);
        }

        /* ============================================================
           MODALS
           ============================================================ */
        .modal-content {
            background: var(--surface-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-base);
        }
        .modal-header {
            background: var(--surface-color);
            color: var(--text-color);
            border-bottom: 1px solid var(--border-color);
        }
        .modal-footer {
            background: var(--surface-color);
            border-top: 1px solid var(--border-color);
        }
        .modal-title { color: var(--text-color); }
        .modal-body  { color: var(--text-color); }
        .modal-backdrop.show { opacity: 0.65; }

        .modal-header.text-bg-danger {
            background: var(--danger-color) !important;
            border-bottom: 1px solid var(--danger-color);
        }
        .modal-header.text-bg-danger,
        .modal-header.text-bg-danger .modal-title {
            color: #fff !important;
        }

        /* ============================================================
           OFFCANVAS
           ============================================================ */
        .offcanvas { background: var(--surface-color); color: var(--text-color); }
        .offcanvas .offcanvas-header { border-bottom: 1px solid var(--border-color); }

        /* ============================================================
           DROPDOWNS
           ============================================================ */
        .dropdown-menu {
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            color: var(--text-color);
        }
        .dropdown-item {
            color: var(--text-color);
        }
        .dropdown-item:hover,
        .dropdown-item:focus {
            background: var(--border-color);
            color: var(--text-color);
        }
        .dropdown-divider { border-color: var(--border-color); }

        /* ============================================================
           UTILITIES
           ============================================================ */
        .text-muted     { color: var(--text-muted) !important; }
        .text-secondary { color: var(--text-muted) !important; }
        .text-dark      { color: var(--text-color) !important; }
        .border         { border-color: var(--border-color) !important; }

        code {
            color: var(--accent-color);
            background: rgba(37, 99, 235, 0.08);
            padding: 0.15rem 0.4rem;
            border-radius: 0.25rem;
        }

        kbd {
            background: var(--background-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: 0.25rem;
            padding: 0.1rem 0.35rem;
            font-size: 0.75em;
            box-shadow: none;
        }

        .avatar-circle {
            background: var(--background-color) !important;
            color: var(--accent-color) !important;
        }

        /* ============================================================
           FILE PREVIEW / BRANDING THUMBNAILS
           ============================================================ */
        .preview-box {
            width: 64px;
            height: 64px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-base);
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--background-color);
            overflow: hidden;
            flex-shrink: 0;
            transition: opacity 0.2s ease, border-color 0.2s ease, filter 0.2s ease;
        }
        .preview-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .preview-box.marked-for-deletion {
            opacity: 0.3;
            border-color: var(--danger-color) !important;
            filter: grayscale(100%);
        }

        .current-file-badge {
            font-size: 0.78rem;
            color: var(--text-color);
            background: var(--background-color);
            border: 1px solid var(--border-color);
            border-radius: 0.4rem;
            padding: 0.2rem 0.5rem;
            display: inline-flex;
            align-items: center;
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn-delete-check { display: none; }
        .btn-delete-check:checked + .delete-btn-label {
            background: var(--danger-color) !important;
            color: #fff !important;
            border-color: var(--danger-color) !important;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-custom sticky-top">
        <div class="container-fluid px-2 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-link p-1 d-lg-none border-0 shadow-none"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#mobileSidebar"
                        aria-controls="mobileSidebar"
                        aria-label="Toggle navigation"
                        style="color: var(--navbar-text);">
                    <i class="fas fa-bars fs-5"></i>
                </button>

                <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL . '/dashboard' ?>">
                    <?php if ($logoUrl): ?>
                        <img src="<?= $logoUrl ?>" alt="Logo" style="height: 30px; width: auto; object-fit: contain;">
                    <?php else: ?>
                        <i class="fas fa-graduation-cap fs-5" style="color: var(--accent-color);"></i>
                    <?php endif; ?>
                    <span><?= $schoolTitle ?></span>
                </a>
            </div>

            <div class="d-flex align-items-center gap-3">

                <button type="button"
                        class="theme-toggle"
                        id="themeToggle"
                        aria-label="Toggle theme"
                        title="Theme: System">
                    <i class="fas fa-circle-half-stroke" id="themeToggleIcon"></i>
                </button>

                <a class="position-relative text-decoration-none" href="#" aria-label="Notifications">
                    <i class="fas fa-bell fs-6"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.55rem;">3</span>
                </a>

                <div class="dropdown">
                    <a class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle"
                       href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                             style="width: 32px; height: 32px; font-size: 0.8rem; background: var(--accent-color);">
                            <?= $userInitial ?>
                        </div>
                        <span class="d-none d-sm-inline fw-medium" style="font-size: 0.85rem;"><?= $userName ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle me-2 text-muted"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL . '/settings' ?>"><i class="fas fa-cog me-2 text-muted"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL . '/logout' ?>"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <aside class="desktop-sidebar d-none d-lg-block" id="desktopSidebar" aria-label="Desktop Sidebar">
        <?php include __DIR__ . '/_sidebar_menu.php'; ?>
    </aside>

    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel" style="width: 280px;">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold d-flex align-items-center gap-2" id="mobileSidebarLabel">
                <?php if ($logoUrl): ?>
                    <img src="<?= $logoUrl ?>" alt="Logo" style="height: 24px; width: auto; object-fit: contain;">
                <?php else: ?>
                    <i class="fas fa-graduation-cap fs-5" style="color: var(--accent-color);"></i>
                <?php endif; ?>
                <span><?= $schoolTitle ?></span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <?php include __DIR__ . '/_sidebar_menu.php'; ?>
        </div>
    </div>

    <main class="main-wrapper">
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Sidebar scroll memory + mobile auto-close
        (function () {
            const sidebar   = document.getElementById('desktopSidebar');
            const scrollKey = 'sidebarScrollTop';

            if (sidebar) {
                const saved = sessionStorage.getItem(scrollKey);
                if (saved !== null) {
                    sidebar.scrollTop = parseInt(saved, 10);
                }

                sidebar.querySelectorAll('.page-navigation').forEach(function (link) {
                    link.addEventListener('click', function () {
                        sessionStorage.setItem(scrollKey, sidebar.scrollTop);
                    });
                });
            }

            const mobileSidebar = document.getElementById('mobileSidebar');
            if (mobileSidebar) {
                const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(mobileSidebar);
                mobileSidebar.querySelectorAll('.page-navigation').forEach(function (link) {
                    link.addEventListener('click', function () {
                        bsOffcanvas.hide();
                    });
                });
            }
        })();

        // Theme toggle (System → Light → Dark)
        (function () {
            const STORAGE_KEY = 'theme-mode';
            const ORDER       = ['system', 'light', 'dark'];
            const ICONS       = {
                system: 'fa-circle-half-stroke',
                light:  'fa-sun',
                dark:   'fa-moon',
            };
            const LABELS = {
                system: 'Theme: System',
                light:  'Theme: Light',
                dark:   'Theme: Dark',
            };

            const button = document.getElementById('themeToggle');
            const icon   = document.getElementById('themeToggleIcon');
            if (!button || !icon) return;

            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

            function getStoredMode() {
                try {
                    const raw = localStorage.getItem(STORAGE_KEY) || 'system';
                    return ORDER.includes(raw) ? raw : 'system';
                } catch (e) {
                    return 'system';
                }
            }

            function applyMode(mode) {
                const resolved = mode === 'system'
                    ? (mediaQuery.matches ? 'dark' : 'light')
                    : mode;

                document.documentElement.setAttribute('data-theme', resolved);
                document.documentElement.setAttribute('data-theme-mode', mode);

                icon.className = 'fas ' + ICONS[mode];
                button.title = LABELS[mode];
                button.setAttribute('aria-label', LABELS[mode]);
            }

            function setMode(mode) {
                try { localStorage.setItem(STORAGE_KEY, mode); } catch (e) {}
                applyMode(mode);
            }

            applyMode(getStoredMode());

            button.addEventListener('click', function () {
                const current = getStoredMode();
                const next = ORDER[(ORDER.indexOf(current) + 1) % ORDER.length];
                setMode(next);
            });

            mediaQuery.addEventListener('change', function () {
                if (getStoredMode() === 'system') {
                    applyMode('system');
                }
            });
        })();
    </script>
</body>
</html>