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

$faviconUrl     = $resolveDiskPath($customFavicon) ? $resolveUrl($customFavicon) : null;
$logoUrl        = $resolveDiskPath($customLogo)    ? $resolveUrl($customLogo)    : null;
$rawSchoolName  = $schoolName ?? 'NexaT School';
$userName       = htmlspecialchars($user->first_name ?? 'User', ENT_QUOTES, 'UTF-8');

$words   = explode(' ', trim($rawSchoolName));
$mid     = ceil(count($words) / 2);
$partOne = htmlspecialchars(implode(' ', array_slice($words, 0, $mid)), ENT_QUOTES, 'UTF-8');
$partTwo = htmlspecialchars(implode(' ', array_slice($words, $mid)), ENT_QUOTES, 'UTF-8');
$schoolFull = htmlspecialchars($rawSchoolName, ENT_QUOTES, 'UTF-8');

$initial     = strtoupper(substr($user->first_name ?? '', 0, 1));
$userInitial = $initial !== '' ? $initial : 'U';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $schoolFull ?> - Dashboard</title>

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

        :root { color-scheme: light; }
        [data-theme="dark"] { color-scheme: dark; }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            background: var(--background-color);
            color: var(--text-color);
            font-family: var(--font-family-base);
            font-size: var(--font-size-base);
            line-height: var(--line-height-base);
            font-weight: var(--font-weight-normal);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        @media (prefers-reduced-motion: no-preference) {
            body { transition: background-color 0.2s ease, color 0.2s ease; }
        }

        h1, h2, h3, h4, h5, h6 {
            font-weight: var(--font-weight-bold);
            color: var(--text-color);
        }
        h5 { font-size: var(--font-size-heading); }

        .navbar-custom {
            background: var(--navbar-bg);
            min-height: 65px;
            padding: 0.5rem 1rem;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .navbar-brand {
            color: var(--navbar-text) !important;
            font-weight: var(--font-weight-bold);
            font-size: 0.95rem;
            letter-spacing: -0.2px;
            line-height: 1.15;
        }

        .navbar-custom a,
        .navbar-custom .dropdown-toggle,
        .navbar-custom .text-secondary,
        .navbar-custom .text-dark {
            color: var(--navbar-text) !important;
        }

        .theme-toggle {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--navbar-text);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
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

        .desktop-sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 65px;
            bottom: 0;
            left: 0;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            z-index: 1020;
            transition: transform 0.3s ease;
        }

        .desktop-sidebar::-webkit-scrollbar { width: 4px; }
        .desktop-sidebar::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }

        .sidebar-section-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 1rem 1rem 0.3rem 1rem;
            letter-spacing: 0.8px;
            font-weight: var(--font-weight-bold);
        }

        .sidebar-menu-wrapper .nav-link {
            padding: 0.55rem 0.75rem;
            font-size: 0.9rem;
            color: var(--sidebar-text);
            border-radius: var(--border-radius-base);
            margin: 0.125rem 0.75rem;
            font-weight: var(--font-weight-medium);
            display: flex;
            align-items: center;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .sidebar-menu-wrapper .nav-link:hover {
            background: rgba(37, 99, 235, 0.08);
            color: var(--accent-color);
        }

        .sidebar-menu-wrapper .nav-link.active {
            background: var(--accent-color);
            color: var(--sidebar-active-text) !important;
            font-weight: var(--font-weight-bold);
        }

        .sidebar-menu-wrapper .sub-link {
            font-size: 0.85rem;
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
            font-size: 1rem;
            margin-right: 0.6rem;
            color: var(--text-muted);
        }

        .sidebar-menu-wrapper .nav-link.active .menu-icon,
        .sidebar-menu-wrapper .nav-link:hover .menu-icon { color: inherit; }

        .sub-menu-icon {
            width: 1.2rem;
            text-align: center;
            font-size: 0.8rem;
            margin-right: 0.5rem;
        }

        .chevron-icon {
            transition: transform 0.2s ease;
            font-size: 0.65rem;
            color: var(--text-muted);
        }

        [aria-expanded="true"] .chevron-icon { transform: rotate(90deg); }

        .sidebar-dropdown-toggle { color: var(--sidebar-text); }

        .sidebar-dropdown-toggle.active-group {
            font-weight: var(--font-weight-bold);
            color: var(--accent-color);
        }

        .main-wrapper {
            margin-left: var(--sidebar-width);
            padding: 1.5rem 2rem;
            min-height: calc(100vh - 65px);
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .main-wrapper {
                margin-left: 0;
                padding: 1rem;
            }
        }

        .card,
        .modal-content,
        .offcanvas,
        .dropdown-menu {
            background: var(--surface-color);
            color: var(--text-color);
        }

        .card {
            border: 1px solid var(--border-color) !important;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
            margin-bottom: 1.25rem;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }
        
        .card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.06);
        }

        .card-header {
            background: var(--surface-color);
            border-bottom: 1px solid var(--border-color);
            color: var(--text-color);
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.25rem;
        }

        .bg-white,
        .bg-light,
        .bg-body,
        .bg-body-tertiary {
            background: var(--surface-color) !important;
            color: var(--text-color);
        }

        .list-group {
            --bs-list-group-bg: transparent;
            --bs-list-group-color: var(--text-color);
            --bs-list-group-border-color: var(--border-color);
            color: var(--text-color);
        }
        .list-group-item {
            background: var(--surface-color);
            color: var(--text-color);
            border-color: var(--border-color);
        }
        .list-group-item-action { color: var(--text-color); }
        .list-group-item-action:hover,
        .list-group-item-action:focus {
            background: var(--border-color);
            color: var(--text-color);
        }

        .form-label,
        .form-check-label,
        label {
            color: var(--text-color);
        }

        .form-control,
        .form-select,
        textarea.form-control {
            background-color: var(--input-bg);
            color: var(--text-color);
            border: 1px solid var(--input-border);
            border-radius: var(--border-radius-base);
            padding: 0.55rem 0.75rem;
            box-shadow: var(--input-shadow);
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }

        .form-control::placeholder { color: var(--text-muted); opacity: 0.7; }

        .form-control:focus,
        .form-select:focus {
            background-color: var(--input-bg);
            color: var(--text-color);
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
            outline: none;
        }

        .form-select {
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2364748B'%3E%3Cpath d='M4.646 6.146a.5.5 0 0 1 .708 0L8 8.793l2.646-2.647a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 0 1 0-.708 z'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 16px 12px !important;
            padding-right: 2.5rem;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
        }

        .form-select option {
            background: var(--surface-color);
            color: var(--text-color);
        }

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

        .table-hover > tbody > tr:hover > * {
            background-color: rgba(37, 99, 235, 0.04);
            color: var(--text-color);
        }

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
            border: 1px solid var(--input-border);
        }
        .btn-secondary:hover,
        .btn-secondary:focus,
        .btn-light:hover,
        .btn-light:focus {
            background: var(--border-color);
            color: var(--text-color);
        }

        .text-muted     { color: var(--text-muted) !important; }
        .text-secondary { color: var(--text-muted) !important; }
        .text-dark      { color: var(--text-color) !important; }
        .border         { border-color: var(--border-color) !important; }

        .dropdown-menu {
            background-color: var(--surface-color);
            border-color: var(--border-color);
        }

        .dropdown-item {
            color: var(--text-color);
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: var(--border-color);
            color: var(--text-color);
        }

        .dropdown-item.active,
        .dropdown-item:active {
            background-color: var(--accent-color);
            color: #ffffff;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-custom">
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
                        <img src="<?= $logoUrl ?>" alt="Logo" style="height: 36px; width: auto; object-fit: contain;">
                    <?php else: ?>
                        <i class="fas fa-graduation-cap fs-5" style="color: var(--accent-color);"></i>
                    <?php endif; ?>
                    <span>
                        <?= $partOne ?>
                        <?php if (!empty($partTwo)): ?>
                            <span class="d-block d-sm-inline"><?= $partTwo ?></span>
                        <?php endif; ?>
                    </span>
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
                <span><?= $schoolFull ?></span>
            </h5>
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