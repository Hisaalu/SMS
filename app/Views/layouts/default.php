<?php
// File: /app/Views/layouts/default.php

use NexaT\Core\Toast;

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

$faviconUrl = $resolveDiskPath($customFavicon) ? $resolveUrl($customFavicon) : null;
$logoUrl    = $resolveDiskPath($customLogo)    ? $resolveUrl($customLogo)    : null;

$faviconUrl = $faviconUrl ? htmlspecialchars($faviconUrl, ENT_QUOTES, 'UTF-8') : null;
$logoUrl    = $logoUrl    ? htmlspecialchars($logoUrl,    ENT_QUOTES, 'UTF-8') : null;

$rawSchoolName = $schoolName ?? 'NexaT School';
$schoolFull    = htmlspecialchars($rawSchoolName, ENT_QUOTES, 'UTF-8');
$userName      = htmlspecialchars($user->first_name ?? 'User', ENT_QUOTES, 'UTF-8');

$initial     = strtoupper(substr($user->first_name ?? '', 0, 1));
$userInitial = $initial !== '' ? $initial : 'U';

$pageTitle = isset($pageTitle) && $pageTitle !== ''
    ? htmlspecialchars((string) $pageTitle, ENT_QUOTES, 'UTF-8')
    : 'Dashboard';

$csrfToken = '';
if (function_exists('csrf_token')) {
    $csrfToken = (string) csrf_token();
} elseif (!empty($_SESSION['csrf_token'])) {
    $csrfToken = (string) $_SESSION['csrf_token'];
} else {
    try {
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
    } catch (\Throwable $e) {
        $csrfToken = '';
    }
}
$csrfToken = htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8');

$pendingToasts = Toast::pull();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
    <title><?= $schoolFull ?> - <?= $pageTitle ?></title>

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
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" rel="stylesheet">

    <?php if ($googleFonts): ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="<?= htmlspecialchars($googleFonts, ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
    <?php endif; ?>

    <?php if ($faviconUrl): ?>
        <link rel="icon" href="<?= $faviconUrl ?>" type="image/x-icon">
    <?php endif; ?>

    <style>
        <?= $cssVariables ?>

        :root {
            color-scheme: light;
            --navbar-height: 65px;
        }
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
            padding: 0.55rem 1rem;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .navbar-custom .container-fluid {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
            padding-left: 0;
            padding-right: 0;
            flex-wrap: nowrap;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 0;
            flex: 1 1 0;
            align-self: center;
        }

        .navbar-brand {
            color: var(--navbar-text) !important;
            font-weight: var(--font-weight-bold);
            font-size: 0.95rem;
            letter-spacing: -0.2px;
            line-height: 1.15;
            display: flex;
            align-items: center;
            gap: 0.55rem;
            min-width: 0;
            flex: 1 1 auto;
            text-decoration: none;
            margin: 0;
            padding: 0;
        }

        .navbar-brand .brand-text {
            min-width: 0;
            max-width: 100%;
            flex: 1 1 auto;
        }

        .navbar-brand .brand-text span {
            display: block;
            white-space: normal;
            overflow-wrap: break-word;
            word-break: normal;
            hyphens: none;
            line-height: 1.15;
        }

        .navbar-brand .brand-logo {
            height: 36px;
            width: auto;
            max-width: 60px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .navbar-brand .brand-icon {
            flex-shrink: 0;
            font-size: 1.35rem;
            color: var(--accent-color);
            line-height: 1;
        }

        .navbar-custom a,
        .navbar-custom .dropdown-toggle,
        .navbar-custom .text-secondary,
        .navbar-custom .text-dark {
            color: var(--navbar-text) !important;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 0 0 auto;
            flex-shrink: 0;
            flex-wrap: nowrap;
            align-self: center;
        }

        .nav-toggle-btn {
            background: transparent;
            border: none;
            padding: 0.25rem;
            color: var(--navbar-text);
            font-size: 1.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            flex-shrink: 0;
        }
        .nav-toggle-btn:hover { background: var(--border-color); }
        .nav-toggle-btn:focus-visible {
            outline: 2px solid var(--accent-color);
            outline-offset: 2px;
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
            flex-shrink: 0;
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

        .user-chip {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            text-decoration: none;
            color: var(--navbar-text) !important;
            min-width: 0;
        }
        .user-chip .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.8rem;
            background: var(--accent-color);
            flex-shrink: 0;
        }
        .user-chip .user-name {
            font-size: 0.85rem;
            font-weight: 500;
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .desktop-sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: var(--navbar-height);
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
            background: rgba(var(--accent-rgb), 0.08);
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
            background: rgba(var(--accent-rgb), 0.04);
        }
        .sidebar-menu-wrapper .sub-link.active {
            background: rgba(var(--accent-rgb), 0.1);
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
            min-height: calc(100vh - var(--navbar-height));
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
        .card-body { padding: 1.25rem; }

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
        label { color: var(--text-color); }

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
            box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.12);
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
            background-color: rgba(var(--accent-rgb), 0.04);
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

        .dropdown-toggle-no-caret::after { display: none !important; }

        .notif-menu {
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-base);
        }

        .notif-item {
            display: flex;
            gap: 0.65rem;
            padding: 0.7rem 1rem;
            border-bottom: 1px solid var(--border-color);
            text-decoration: none;
            color: var(--text-color);
            transition: background-color 0.12s ease;
            cursor: pointer;
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-item:hover { background: var(--border-color); }
        .notif-item.unread { background: rgba(var(--accent-rgb), 0.04); }

        .notif-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
            font-size: 0.8rem;
        }

        .notif-title {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-color);
            line-height: 1.25;
        }
        .notif-msg {
            font-size: 0.74rem;
            color: var(--text-muted);
            margin-top: 0.15rem;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .notif-time {
            font-size: 0.68rem;
            color: var(--text-muted);
            margin-top: 0.2rem;
        }

        @media (max-width: 767.98px) {
            .navbar-custom { padding: 0.5rem 0.75rem; }
            .navbar-brand { font-size: 0.85rem; }
            .navbar-brand .brand-logo { height: 30px; max-width: 50px; }
            .navbar-brand .brand-icon { font-size: 1.15rem; }
            .navbar-actions { gap: 0.55rem; }
            .theme-toggle { width: 32px; height: 32px; }
            .user-chip .avatar { width: 28px; height: 28px; font-size: 0.72rem; }
        }

        @media (max-width: 575.98px) {
            .navbar-custom { padding: 0.45rem 0.6rem; }
            .navbar-brand { font-size: 0.8rem; gap: 0.45rem; }
            .navbar-brand .brand-logo { height: 26px; max-width: 42px; }
            .user-chip .user-name { display: none; }
            .navbar-actions { gap: 0.45rem; }
            .notif-menu { width: 92vw !important; max-width: 92vw !important; }
        }

        .badge.bg-primary-subtle {
            background: #DBEAFE !important;
            color: #1E40AF !important;
        }
        .badge.bg-success-subtle {
            background: #D1FAE5 !important;
            color: #065F46 !important;
        }
        .badge.bg-warning-subtle {
            background: #FEF3C7 !important;
            color: #92400E !important;
        }
        .badge.bg-danger-subtle {
            background: #FEE2E2 !important;
            color: #991B1B !important;
        }
        .badge.bg-info-subtle {
            background: #CFFAFE !important;
            color: #075985 !important;
        }
        .badge.bg-secondary-subtle {
            background: #E2E8F0 !important;
            color: #334155 !important;
        }
        .badge.bg-light {
            background: #F1F5F9 !important;
            color: #334155 !important;
        }

        .badge.bg-success {
            background: #16A34A !important;
            color: #FFFFFF !important;
        }
        .badge.bg-warning {
            background: #F59E0B !important;
            color: #422006 !important;
        }
        .badge.bg-danger {
            background: #DC2626 !important;
            color: #FFFFFF !important;
        }
        .badge.bg-info {
            background: #0EA5E9 !important;
            color: #FFFFFF !important;
        }
        .badge.bg-secondary {
            background: #64748B !important;
            color: #FFFFFF !important;
        }
        .badge.bg-primary {
            background: var(--accent-color) !important;
            color: #FFFFFF !important;
        }

        .alert-success {
            background: #ECFDF5 !important;
            border-color: #A7F3D0 !important;
            color: #065F46 !important;
        }
        .alert-danger {
            background: #FEF2F2 !important;
            border-color: #FECACA !important;
            color: #991B1B !important;
        }
        .alert-warning {
            background: #FFFBEB !important;
            border-color: #FDE68A !important;
            color: #92400E !important;
        }
        .alert-info {
            background: #EFF6FF !important;
            border-color: #BFDBFE !important;
            color: #1E40AF !important;
        }
        .alert-light {
            background: #F8FAFC !important;
            border-color: #E2E8F0 !important;
            color: #334155 !important;
        }

        [data-theme="dark"] .alert {
            border-width: 1px;
            border-style: solid;
        }
        [data-theme="dark"] .alert-light {
            background: rgba(148, 163, 184, 0.10) !important;
            border-color: rgba(148, 163, 184, 0.22) !important;
            color: #E2E8F0 !important;
        }
        [data-theme="dark"] .alert-success {
            background: rgba(16, 185, 129, 0.12) !important;
            border-color: rgba(16, 185, 129, 0.32) !important;
            color: #6EE7B7 !important;
        }
        [data-theme="dark"] .alert-danger {
            background: rgba(239, 68, 68, 0.12) !important;
            border-color: rgba(239, 68, 68, 0.32) !important;
            color: #FCA5A5 !important;
        }
        [data-theme="dark"] .alert-warning {
            background: rgba(245, 158, 11, 0.14) !important;
            border-color: rgba(245, 158, 11, 0.34) !important;
            color: #FCD34D !important;
        }
        [data-theme="dark"] .alert-info {
            background: rgba(59, 130, 246, 0.14) !important;
            border-color: rgba(59, 130, 246, 0.34) !important;
            color: #93C5FD !important;
        }

        [data-theme="dark"] .badge.bg-primary-subtle {
            background: rgba(59, 130, 246, 0.20) !important;
            color: #BFDBFE !important;
        }
        [data-theme="dark"] .badge.bg-success-subtle {
            background: rgba(16, 185, 129, 0.20) !important;
            color: #A7F3D0 !important;
        }
        [data-theme="dark"] .badge.bg-warning-subtle {
            background: rgba(245, 158, 11, 0.22) !important;
            color: #FDE68A !important;
        }
        [data-theme="dark"] .badge.bg-danger-subtle {
            background: rgba(239, 68, 68, 0.20) !important;
            color: #FECACA !important;
        }
        [data-theme="dark"] .badge.bg-info-subtle {
            background: rgba(56, 189, 248, 0.22) !important;
            color: #BAE6FD !important;
        }
        [data-theme="dark"] .badge.bg-secondary-subtle {
            background: rgba(148, 163, 184, 0.16) !important;
            color: #CBD5E1 !important;
        }
        [data-theme="dark"] .badge.bg-light {
            background: rgba(148, 163, 184, 0.16) !important;
            color: #E2E8F0 !important;
        }

        [data-theme="dark"] .badge.bg-success {
            background: #059669 !important;
            color: #ECFDF5 !important;
        }
        [data-theme="dark"] .badge.bg-warning {
            background: #D97706 !important;
            color: #FFF7ED !important;
        }
        [data-theme="dark"] .badge.bg-danger {
            background: #DC2626 !important;
            color: #FEF2F2 !important;
        }
        [data-theme="dark"] .badge.bg-info {
            background: #0284C7 !important;
            color: #F0F9FF !important;
        }
        [data-theme="dark"] .badge.bg-primary {
            background: var(--accent-color) !important;
            color: #FFFFFF !important;
        }
        [data-theme="dark"] .badge.bg-secondary {
            background: #475569 !important;
            color: #F1F5F9 !important;
        }

        [data-theme="dark"] .table-borderless > :not(caption) > * > * {
            border-color: transparent;
        }
        [data-theme="dark"] .table > :not(caption) > * > * {
            background-color: transparent;
            border-color: var(--border-color);
        }
        [data-theme="dark"] .table-hover > tbody > tr:hover > * {
            background-color: rgba(var(--accent-rgb), 0.08);
            color: var(--text-color);
        }

        [data-theme="dark"] .text-success { color: #6EE7B7 !important; }
        [data-theme="dark"] .text-danger  { color: #FCA5A5 !important; }
        [data-theme="dark"] .text-warning { color: #FCD34D !important; }
        [data-theme="dark"] .text-info    { color: #93C5FD !important; }

        [data-theme="dark"] .table-sm > :not(caption) > * > * {
            padding: 0.4rem 0.5rem;
        }

        [data-theme="dark"] code {
            background: rgba(148, 163, 184, 0.12);
            color: #FBCFE8;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            font-size: 0.85em;
        }

        [data-theme="dark"] hr {
            border-color: var(--border-color);
            opacity: 0.5;
        }

        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select {
            color-scheme: dark;
        }
    </style>
</head>
<body>

    <?php
        $flashBag = ['success' => [], 'error' => [], 'warning' => [], 'info' => []];
        foreach ($pendingToasts as $t) {
            $type = $t['type'] ?? 'info';
            if (isset($flashBag[$type])) {
                $flashBag[$type][] = $t['message'] ?? '';
            }
        }
        include __DIR__ . '/../partials/toasts.php';
    ?>

    <nav class="navbar navbar-custom" id="mainNavbar">
        <div class="container-fluid">

            <div class="navbar-left">
                <button class="nav-toggle-btn d-lg-none"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#mobileSidebar"
                        aria-controls="mobileSidebar"
                        aria-expanded="false"
                        aria-label="Toggle navigation">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </button>

                <a class="navbar-brand" href="<?= BASE_URL . '/dashboard' ?>">
                    <?php if ($logoUrl): ?>
                        <img class="brand-logo" src="<?= $logoUrl ?>" alt="<?= $schoolFull ?>">
                    <?php else: ?>
                        <i class="fas fa-graduation-cap brand-icon" aria-hidden="true"></i>
                    <?php endif; ?>
                    <span class="brand-text">
                        <span><?= $schoolFull ?></span>
                    </span>
                </a>
            </div>

            <div class="navbar-actions">
                <button type="button"
                        class="theme-toggle"
                        id="themeToggle"
                        aria-label="Toggle theme"
                        title="Theme: System">
                    <i class="fas fa-circle-half-stroke" id="themeToggleIcon" aria-hidden="true"></i>
                </button>

                <div class="dropdown">
                    <a class="position-relative text-decoration-none dropdown-toggle-no-caret"
                    href="#" role="button"
                    id="notifBell"
                    data-bs-toggle="dropdown"
                    data-bs-auto-close="outside"
                    aria-expanded="false"
                    aria-label="Notifications"
                    style="color: var(--navbar-text);">
                        <i class="fas fa-bell fs-6" aria-hidden="true"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill d-none"
                            id="notifBadge"
                            style="font-size: 0.55rem; background-color: var(--accent-color); color: #fff;">0</span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end shadow-sm notif-menu"
                        aria-labelledby="notifBell"
                        id="notifMenu"
                        style="width: 360px; padding: 0;">

                        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom"
                            style="border-color: var(--border-color) !important;">
                            <span class="fw-bold small" style="color: var(--text-color);">Notifications</span>
                            <button type="button"
                                    class="btn btn-link btn-sm p-0 text-decoration-none"
                                    id="notifMarkAll"
                                    style="color: var(--accent-color); font-size: 0.75rem;">
                                Mark all as read
                            </button>
                        </div>

                        <div id="notifList" style="max-height: 380px; overflow-y: auto;">
                            <div class="text-center py-4 small" style="color: var(--text-muted);">
                                <i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Loading…
                            </div>
                        </div>

                        <div class="border-top text-center py-2"
                            style="border-color: var(--border-color) !important;">
                            <a href="<?= BASE_URL . '/notifications' ?>"
                            class="small text-decoration-none fw-semibold"
                            style="color: var(--accent-color);">
                                View All Notifications
                            </a>
                        </div>
                    </div>
                </div>

                <div class="dropdown">
                    <a class="user-chip dropdown-toggle-no-caret" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar"><?= $userInitial ?></div>
                        <span class="user-name d-none d-sm-inline"><?= $userName ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle me-2 text-muted" aria-hidden="true"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL . '/settings' ?>"><i class="fas fa-cog me-2 text-muted" aria-hidden="true"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL . '/logout' ?>"><i class="fas fa-sign-out-alt me-2" aria-hidden="true"></i>Logout</a></li>
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
                    <img src="<?= $logoUrl ?>" alt="<?= $schoolFull ?>" style="height: 24px; width: auto; object-fit: contain;">
                <?php else: ?>
                    <i class="fas fa-graduation-cap fs-5" style="color: var(--accent-color);" aria-hidden="true"></i>
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

    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            (function () {
                const navbar = document.getElementById('mainNavbar');
                if (!navbar) return;

                let raf = 0;
                const syncHeight = () => {
                    if (raf) return;
                    raf = requestAnimationFrame(() => {
                        raf = 0;
                        const h = navbar.getBoundingClientRect().height;
                        document.documentElement.style.setProperty('--navbar-height', h + 'px');
                    });
                };

                syncHeight();
                window.addEventListener('resize', syncHeight);
                window.addEventListener('orientationchange', syncHeight);

                if ('ResizeObserver' in window) {
                    new ResizeObserver(syncHeight).observe(navbar);
                }
            })();

            (function () {
                const sidebar   = document.getElementById('desktopSidebar');
                const scrollKey = 'sidebarScrollTop';

                if (sidebar) {
                    const saved = sessionStorage.getItem(scrollKey);
                    if (saved !== null) {
                        sidebar.scrollTop = parseInt(saved, 10) || 0;
                    }

                    sidebar.querySelectorAll('.page-navigation').forEach(function (link) {
                        link.addEventListener('click', function () {
                            sessionStorage.setItem(scrollKey, sidebar.scrollTop);
                        });
                    });
                }

                const mobileSidebar = document.getElementById('mobileSidebar');
                const toggleBtn     = document.querySelector('[data-bs-target="#mobileSidebar"]');

                if (mobileSidebar && window.bootstrap) {
                    const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(mobileSidebar);

                    mobileSidebar.querySelectorAll('.page-navigation').forEach(function (link) {
                        link.addEventListener('click', function () {
                            bsOffcanvas.hide();
                        });
                    });

                    if (toggleBtn) {
                        mobileSidebar.addEventListener('shown.bs.offcanvas', () => {
                            toggleBtn.setAttribute('aria-expanded', 'true');
                        });
                        mobileSidebar.addEventListener('hidden.bs.offcanvas', () => {
                            toggleBtn.setAttribute('aria-expanded', 'false');
                        });
                    }
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

            (function () {
                const bell   = document.getElementById('notifBell');
                const badge  = document.getElementById('notifBadge');
                const list   = document.getElementById('notifList');
                const markAll= document.getElementById('notifMarkAll');
                if (!bell || !badge || !list) return;

                const FEED_URL  = '<?= BASE_URL ?>/api/notifications/feed';
                const READ_URL  = '<?= BASE_URL ?>/notifications/mark-read';
                const ALL_URL   = '<?= BASE_URL ?>/notifications/mark-all-read';

                const CSRF_TOKEN = (function () {
                    const meta = document.querySelector('meta[name="csrf-token"]');
                    return meta ? meta.getAttribute('content') : '';
                })();

                const POLL_INTERVAL = 45000;
                let pollId = null;

                const typeColor = (type) => ({
                    'success': 'var(--success-color)',
                    'warning': 'var(--warning-color)',
                    'danger':  'var(--danger-color)',
                    'message': 'var(--accent-color)',
                    'system':  'var(--secondary-color, #6f42c1)',
                }[type] || 'var(--accent-color)');

                const typeIcon = (item) => item.icon || ({
                    'success': 'fas fa-check-circle',
                    'warning': 'fas fa-exclamation-triangle',
                    'danger':  'fas fa-times-circle',
                    'message': 'fas fa-comment-dots',
                    'system':  'fas fa-cog',
                }[item.type] || 'fas fa-info-circle');

                const escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
                }[c]));

                function render(items) {
                    if (!Array.isArray(items) || items.length === 0) {
                        list.innerHTML = '<div class="text-center py-4 small" style="color: var(--text-muted);">'
                                       + '<i class="fas fa-bell-slash fa-lg mb-2 d-block opacity-50"></i>'
                                       + 'You\'re all caught up.</div>';
                        return;
                    }

                    list.innerHTML = items.map(item => {
                        const wrap = item.action_url ? 'a' : 'div';
                        const href = item.action_url ? ` href="${escapeHtml(item.action_url)}"` : '';
                        const dataOpen = item.action_url ? ` data-notif-open="${escapeHtml(String(item.id ?? ''))}"` : '';
                        const cls = 'notif-item' + (item.is_unread ? ' unread' : '');

                        return `<${wrap} class="${cls}"${href}${dataOpen}>
                            <span class="notif-icon" style="background: ${typeColor(item.type)};">
                                <i class="${escapeHtml(typeIcon(item))}"></i>
                            </span>
                            <span class="flex-grow-1 min-width-0">
                                <span class="notif-title d-block">${escapeHtml(item.title)}</span>
                                ${item.message ? `<span class="notif-msg d-block">${escapeHtml(item.message)}</span>` : ''}
                                <span class="notif-time d-block">${escapeHtml(item.time_ago)}</span>
                            </span>
                        </${wrap}>`;
                    }).join('');

                    list.querySelectorAll('[data-notif-open]').forEach(el => {
                        el.addEventListener('click', function () {
                            const id = this.dataset.notifOpen;
                            const body = 'id=' + encodeURIComponent(id)
                                       + (CSRF_TOKEN ? '&csrf_token=' + encodeURIComponent(CSRF_TOKEN) : '');
                            fetch(READ_URL, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: body,
                                keepalive: true,
                            }).catch(() => {});
                        });
                    });
                }

                function refresh() {
                    fetch(FEED_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(r => r.json())
                        .then(data => {
                            const count = parseInt(data.unread || 0, 10);
                            if (count > 0) {
                                badge.textContent = count > 99 ? '99+' : count;
                                badge.classList.remove('d-none');
                            } else {
                                badge.classList.add('d-none');
                            }
                            render(data.items || []);
                        })
                        .catch(() => {
                            list.innerHTML = '<div class="text-center py-4 small" style="color: var(--text-muted);">'
                                           + 'Failed to load notifications.</div>';
                        });
                }

                function startPolling() {
                    if (pollId !== null) return;
                    pollId = setInterval(refresh, POLL_INTERVAL);
                }

                function stopPolling() {
                    if (pollId === null) return;
                    clearInterval(pollId);
                    pollId = null;
                }

                if (markAll) {
                    markAll.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        fetch(ALL_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: CSRF_TOKEN ? 'csrf_token=' + encodeURIComponent(CSRF_TOKEN) : '',
                        }).then(() => refresh()).catch(() => {});
                    });
                }

                bell.addEventListener('show.bs.dropdown', refresh);
                refresh();
                startPolling();

                document.addEventListener('visibilitychange', function () {
                    if (document.hidden) {
                        stopPolling();
                    } else {
                        refresh();
                        startPolling();
                    }
                });
            })();
        });
    </script>
</body>
</html>