<!-- File: /app/Views/layouts/default.php -->
<?php
    $settingsService = new \NexaT\Core\SettingsService();
    $customFavicon   = $settingsService->get('branding.favicon', '');
    $customLogo      = $settingsService->get('branding.logo', '');

    // Helper to locate absolute file path on disk regardless of public folder setup
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

    // Helper to build web-accessible URL
    $resolveUrl = function(string $relativePath): string {
        $cleanPath = ltrim($relativePath, '/');
        if (file_exists(ROOT_PATH . '/public/' . $cleanPath) && !str_contains(BASE_URL, '/public')) {
            return rtrim(BASE_URL, '/') . '/public/' . $cleanPath;
        }
        return rtrim(BASE_URL, '/') . '/' . $cleanPath;
    };

    $faviconDiskPath = $resolveDiskPath($customFavicon);
    $logoDiskPath    = $resolveDiskPath($customLogo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($schoolName ?? 'NexaT School', ENT_QUOTES, 'UTF-8') ?> - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?php if ($faviconDiskPath): ?>
        <link rel="icon" href="<?= $resolveUrl($customFavicon) ?>" type="image/x-icon">
    <?php endif; ?>

    <style>
        :root {
            --primary-color: <?= $theme['primary'] ?? '#000000' ?>;
            --accent-color: <?= $theme['accent'] ?? '#1D9BF0' ?>;
            --background-color: <?= $theme['background'] ?? '#F7F9F9' ?>;
            --surface-color: <?= $theme['surface'] ?? '#FFFFFF' ?>;
            --text-color: <?= $theme['text'] ?? '#0F1419' ?>;
            --border-color: <?= $theme['border'] ?? '#EFF3F4' ?>;
            --sidebar-width: 260px;
        }

        body { 
            background: var(--background-color); 
            color: var(--text-color); 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            font-size: 0.875rem; 
            overflow-x: hidden;
        }

        .navbar-custom { 
            background: var(--primary-color); 
            min-height: 58px;
            padding: 0.5rem 1rem; 
            border-bottom: 1px solid rgba(255, 255, 255, 0.1); 
            z-index: 1030;
        }
        
        .navbar-brand { 
            color: #ffffff !important; 
            font-weight: 700; 
            font-size: 1.1rem; 
            letter-spacing: -0.3px; 
        }

        /* Sidebar styles */
        .sidebar-content {
            padding: 0.75rem 0;
        }

        .sidebar-content .nav-link { 
            padding: 0.45rem 0.9rem; 
            font-size: 0.82rem; 
            color: var(--text-color); 
            border-radius: 0.4rem; 
            margin: 0.1rem 0.5rem; 
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: all 0.15s ease-in-out;
        }

        .sidebar-content .nav-link:hover { 
            background: var(--border-color); 
            color: var(--accent-color); 
        }

        .sidebar-content .nav-link.active { 
            background: var(--accent-color); 
            color: #ffffff !important; 
            font-weight: 600; 
        }

        .sidebar-content .nav-link i { 
            width: 1.4rem; 
            text-align: center; 
            font-size: 0.9rem; 
            margin-right: 0.5rem; 
        }

        .sidebar-content .nav-section { 
            font-size: 0.6rem; 
            text-transform: uppercase; 
            color: #536471; 
            padding: 0.6rem 1rem 0.2rem; 
            letter-spacing: 0.6px; 
            font-weight: 700; 
        }

        .sidebar-content .nav-item .nav-link {
            padding-left: 1.2rem;
        }

        .sidebar-content .nav-item .nav-link i {
            margin-right: 0.5rem;
        }

        @media (min-width: 992px) {
            .desktop-sidebar {
                width: var(--sidebar-width);
                position: fixed;
                top: 58px;
                bottom: 0;
                left: 0;
                background: var(--surface-color);
                border-right: 1px solid var(--border-color);
                overflow-y: auto;
                z-index: 1020;
            }

            .desktop-sidebar::-webkit-scrollbar {
                width: 4px;
            }

            .desktop-sidebar::-webkit-scrollbar-thumb {
                background: var(--border-color);
                border-radius: 4px;
            }

            .main-wrapper {
                margin-left: var(--sidebar-width);
            }
        }

        .main-wrapper {
            padding: 1.25rem;
            min-height: calc(100vh - 58px);
        }

        /* Modern Box Shadow Utility */
        .card, .card-shadow { 
            background: var(--surface-color); 
            border: 1px solid rgba(0, 0, 0, 0.05) !important; 
            border-radius: 0.75rem; 
            box-shadow: 0 2px 16px rgba(0,0,0,0.05); 
        }

        /* Mobile Offcanvas */
        .offcanvas {
            background: var(--surface-color);
        }

        .offcanvas .offcanvas-header {
            border-bottom: 1px solid var(--border-color);
        }

        @media (max-width: 991.98px) {
            .main-wrapper {
                padding: 1rem 0.75rem;
            }
        }

        /* Smooth transitions */
        .nav-link, .btn, .card {
            transition: all 0.2s ease;
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <nav class="navbar navbar-custom sticky-top">
        <div class="container-fluid px-2 d-flex align-items-center justify-content-between">
            
            <div class="d-flex align-items-center gap-2">
                <!-- Mobile Menu Trigger -->
                <button class="btn btn-link text-white p-1 d-lg-none border-0 shadow-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                    <i class="fas fa-bars fs-5"></i>
                </button>

                <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL . '/dashboard' ?>">
                    <?php if ($logoDiskPath): ?>
                        <img src="<?= $resolveUrl($customLogo) ?>" alt="Logo" style="height: 32px; width: auto; object-fit: contain;">
                    <?php else: ?>
                        <i class="fas fa-graduation-cap text-info fs-5"></i>
                    <?php endif; ?>
                    <span><?= htmlspecialchars($schoolName ?? 'NexaT', ENT_QUOTES, 'UTF-8') ?></span>
                </a>
            </div>

            <!-- Right Utilities -->
            <div class="d-flex align-items-center gap-3">
                <a class="text-white position-relative text-decoration-none" href="#">
                    <i class="fas fa-bell fs-6"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.55rem;">3</span>
                </a>

                <div class="dropdown">
                    <a class="text-white d-flex align-items-center gap-2 text-decoration-none dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white font-weight-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                            <?= strtoupper(substr($user->first_name ?? 'U', 0, 1)) ?>
                        </div>
                        <span class="d-none d-sm-inline font-weight-medium" style="font-size: 0.85rem;"><?= htmlspecialchars($user->first_name ?? 'User', ENT_QUOTES, 'UTF-8') ?></span>
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

    <!-- Desktop Permanent Sidebar -->
    <aside class="desktop-sidebar d-none d-lg-block">
        <div class="sidebar-content">
            <?php include __DIR__ . '/_sidebar_menu.php'; ?>
        </div>
    </aside>

    <!-- Mobile Drawer Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel" style="width: 280px;">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold d-flex align-items-center gap-2" id="mobileSidebarLabel">
                <?php if ($logoDiskPath): ?>
                    <img src="<?= $resolveUrl($customLogo) ?>" alt="Logo" style="height: 24px; width: auto; object-fit: contain;">
                <?php else: ?>
                    <i class="fas fa-graduation-cap text-primary fs-5"></i>
                <?php endif; ?>
                <span><?= htmlspecialchars($schoolName ?? 'NexaT', ENT_QUOTES, 'UTF-8') ?></span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div class="sidebar-content">
                <?php include __DIR__ . '/_sidebar_menu.php'; ?>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="main-wrapper">
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto close mobile sidebar when clicking a link -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var offcanvasLinks = document.querySelectorAll('#mobileSidebar .nav-link');
            var offcanvas = document.getElementById('mobileSidebar');
            
            if (offcanvas) {
                var bsOffcanvas = new bootstrap.Offcanvas(offcanvas);
                offcanvasLinks.forEach(function(link) {
                    link.addEventListener('click', function() {
                        bsOffcanvas.hide();
                    });
                });
            }
        });
    </script>
</body>
</html>