<?php
// File: /app/Views/layouts/print.php

$settingsService = new \NexaT\Core\SettingsService();
$themeService    = new \NexaT\Core\ThemeService($settingsService);
$cssVariables    = $themeService->cssVariables();

$customFavicon = $settingsService->get('branding.favicon', '');
$customLogo    = $settingsService->get('branding.logo', '');

$resolveDiskPath = static function (?string $relativePath): ?string {
    if (empty($relativePath)) return null;
    $relativePath = ltrim($relativePath, '/');
    foreach ([ROOT_PATH . '/public/' . $relativePath, ROOT_PATH . '/' . $relativePath] as $path) {
        if (file_exists($path)) return $path;
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
$faviconUrl = $faviconUrl ? htmlspecialchars($faviconUrl, ENT_QUOTES, 'UTF-8') : null;
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Document', ENT_QUOTES, 'UTF-8') ?></title>

    <?php if ($faviconUrl): ?>
        <link rel="icon" href="<?= $faviconUrl ?>" type="image/x-icon">
    <?php endif; ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        <?= $cssVariables ?>

        :root {
            color-scheme: light;
        }
        body {
            font-family: var(--font-family-base, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif);
            background: #f0f3f7;
            color: var(--text-color, #0F1419);
            margin: 0;
            padding: 2rem 1rem;
            -webkit-font-smoothing: antialiased;
        }
        .print-shell {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid var(--border-color, #EFF3F4);
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
            padding: 2rem 2.25rem;
        }
        .print-toolbar {
            max-width: 900px;
            margin: 0 auto 1rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }
        .print-toolbar .btn-primary {
            background: var(--accent-color);
            border-color: var(--accent-color);
            color: #fff;
        }
        .print-toolbar .btn-primary:hover,
        .print-toolbar .btn-primary:focus {
            background: var(--accent-color);
            border-color: var(--accent-color);
            color: #fff;
            filter: brightness(0.95);
        }
        .print-toolbar .btn-secondary {
            background: #ffffff;
            color: var(--text-color, #0F1419);
            border: 1px solid var(--border-color, #EFF3F4);
        }
        .print-toolbar .btn-secondary:hover,
        .print-toolbar .btn-secondary:focus {
            background: var(--border-color, #EFF3F4);
            color: var(--text-color, #0F1419);
        }
        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .print-shell {
                max-width: 100% !important;
                border: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
            }
            .print-toolbar { display: none !important; }
            .no-print { display: none !important; }
            @page { size: A4; margin: 12mm; }
        }
    </style>
</head>
<body>
    <div class="print-toolbar no-print">
        <button onclick="window.print()" class="btn btn-sm btn-primary">
            <i class="fas fa-print me-1"></i> Print
        </button>
        <button onclick="window.close()" class="btn btn-sm btn-secondary">Close</button>
    </div>

    <div class="print-shell">
        <?= $content ?>
    </div>
</body>
</html>