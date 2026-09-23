<?php
//File: /app/Views/layouts/default.php
    $settingsService = new \NexaT\Core\SettingsService();
    $customFavicon   = $settingsService->get('branding.favicon', '');
    $customLogo      = $settingsService->get('branding.logo', '');

    $resolveDiskPath = function(?string $relativePath): ?string {
        if (empty($relativePath)) return null;
        $relativePath = ltrim($relativePath, '/');

        $possiblePaths = [
            ROOT_PATH . '/public/' . $relativePath,
            ROOT_PATH . '/' . $relativePath,
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        return null;
    };

    $resolveUrl = function(string $relativePath) use ($resolveDiskPath): string {
        $cleanPath = ltrim($relativePath, '/');
        if ($resolveDiskPath($cleanPath) && !str_contains(BASE_URL, '/public')) {
            return rtrim(BASE_URL, '/') . '/public/' . $cleanPath;
        }
        return rtrim(BASE_URL, '/') . '/' . $cleanPath;
    };

    $faviconUrl  = $resolveDiskPath($customFavicon) ? $resolveUrl($customFavicon) : null;
    $logoUrl     = $resolveDiskPath($customLogo) ? $resolveUrl($customLogo) : null;
    $schoolTitle = htmlspecialchars($schoolName ?? 'NexaT School', ENT_QUOTES, 'UTF-8');
    $userName    = htmlspecialchars($user->first_name ?? 'User', ENT_QUOTES, 'UTF-8');
    $userInitial = strtoupper(substr($user->first_name ?? 'U', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $schoolTitle ?> - Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?php if ($faviconUrl): ?>
        <link rel="icon" href="<?= $faviconUrl ?>" type="image/x-icon">
    <?php endif; ?>

    <style>
        :root {
            --primary-color: <?= $theme['primary'] ?? '#0F172A' ?>;
            --accent-color: <?= $theme['accent'] ?? '#2563EB' ?>;
            --background-color: <?= $theme['background'] ?? '#F8FAFC' ?>;
            --surface-color: <?= $theme['surface'] ?? '#FFFFFF' ?>;
            --text-color: <?= $theme['text'] ?? '#334155' ?>;
            --text-muted: <?= $theme['muted'] ?? '#64748B' ?>;
            --border-color: <?= $theme['border'] ?? '#E2E8F0' ?>;
            --sidebar-width: 270px;
        }

        body {
            background: var(--background-color);
            color: var(--text-color);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 0.875rem;
            overflow-x: hidden;
        }

        .navbar-custom {
            background: var(--surface-color);
            min-height: 60px;
            padding: 0.5rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            z-index: 1030;
        }

        .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: -0.3px;
        }

        .desktop-sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 60px;
            bottom: 0;
            left: 0;
            background: var(--surface-color);
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            z-index: 1020;
        }

        .desktop-sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .desktop-sidebar::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }

        .sidebar-section-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 0.85rem 1rem 0.3rem 1rem;
            letter-spacing: 0.8px;
            font-weight: 700;
        }

        .sidebar-menu-wrapper .nav-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
            color: var(--text-color);
            border-radius: 0.375rem;
            margin: 0.125rem 0.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .sidebar-menu-wrapper .nav-link:hover {
            background: rgba(37, 99, 235, 0.06);
            color: var(--accent-color);
        }

        .sidebar-menu-wrapper .nav-link.active {
            background: var(--accent-color);
            color: #ffffff !important;
            font-weight: 600;
        }

        .sidebar-menu-wrapper .sub-link {
            font-size: 0.82rem;
            color: var(--text-muted);
        }

        .sidebar-menu-wrapper .sub-link:hover {
            color: var(--accent-color);
            background: rgba(37, 99, 235, 0.04);
        }

        .sidebar-menu-wrapper .sub-link.active {
            background: rgba(37, 99, 235, 0.1);
            color: var(--accent-color) !important;
            font-weight: 600;
        }

        .menu-icon {
            width: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
            margin-right: 0.6rem;
            color: var(--text-muted);
        }

        .sidebar-menu-wrapper .nav-link.active .menu-icon,
        .sidebar-menu-wrapper .nav-link:hover .menu-icon {
            color: inherit;
        }

        .sub-menu-icon {
            width: 1.2rem;
            text-align: center;
            font-size: 0.75rem;
            margin-right: 0.5rem;
        }

        .chevron-icon {
            transition: transform 0.2s ease;
            font-size: 0.65rem;
            color: var(--text-muted);
        }

        [aria-expanded="true"] .chevron-icon {
            transform: rotate(90deg);
        }

        .sidebar-dropdown-toggle {
            color: var(--text-color);
        }

        .sidebar-dropdown-toggle.active-group {
            font-weight: 600;
            color: var(--accent-color);
        }

        .main-wrapper {
            margin-left: var(--sidebar-width);
            padding: 1.5rem;
            min-height: calc(100vh - 60px);
        }

        @media (max-width: 991.98px) {
            .main-wrapper {
                margin-left: 0;
                padding: 1rem;
            }
        }

        .card {
            background: var(--surface-color);
            border: 1px solid var(--border-color) !important;
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .offcanvas {
            background: var(--surface-color);
        }

        .offcanvas .offcanvas-header {
            border-bottom: 1px solid var(--border-color);
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-custom sticky-top">
        <div class="container-fluid px-2 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-link text-dark p-1 d-lg-none border-0 shadow-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-label="Toggle navigation">
                    <i class="fas fa-bars fs-5"></i>
                </button>

                <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL . '/dashboard' ?>">
                    <?php if ($logoUrl): ?>
                        <img src="<?= $logoUrl ?>" alt="Logo" style="height: 30px; width: auto; object-fit: contain;">
                    <?php else: ?>
                        <i class="fas fa-graduation-cap text-primary fs-5"></i>
                    <?php endif; ?>
                    <span><?= $schoolTitle ?></span>
                </a>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a class="text-secondary position-relative text-decoration-none" href="#" aria-label="Notifications">
                    <i class="fas fa-bell fs-6"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.55rem;">3</span>
                </a>

                <div class="dropdown">
                    <a class="text-dark d-flex align-items-center gap-2 text-decoration-none dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                            <?= $userInitial ?>
                        </div>
                        <span class="d-none d-sm-inline fw-medium text-dark" style="font-size: 0.85rem;"><?= $userName ?></span>
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
                    <i class="fas fa-graduation-cap text-primary fs-5"></i>
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
        (function () {
            const sidebar = document.getElementById('desktopSidebar');
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
    </script>
</body>
</html>