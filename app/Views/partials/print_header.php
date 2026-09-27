<?php
// File: /app/Views/partials/print_header.php

use NexaT\Core\DocumentRenderer;

$profile = DocumentRenderer::schoolProfile();

$printTitle    = $print['title']    ?? '';
$printSubtitle = $print['subtitle'] ?? '';
$printMeta     = $print['meta']     ?? [];
$printCompact  = !empty($print['compact']);
$printFavicon  = $print['favicon']  ?? '';

$logoPath = $profile['report_logo'] !== '' ? $profile['report_logo'] : $profile['logo'];
$hasLogo  = DocumentRenderer::logoExists($logoPath);
$logoUrl  = $hasLogo ? DocumentRenderer::resolveUrl($logoPath) : '';

$faviconPath = $printFavicon !== ''
    ? $printFavicon
    : (!empty($profile['favicon']) ? $profile['favicon'] : $logoPath);

$faviconUrl = ($faviconPath !== '' && DocumentRenderer::logoExists($faviconPath))
    ? DocumentRenderer::resolveUrl($faviconPath)
    : '';

$addressBits = array_filter([
    $profile['po_box'] !== '' ? '' . $profile['po_box'] : '',
], fn($v) => trim((string)$v) !== '');

$contactBits = array_filter([
    $profile['telephone'] !== '' ? 'Tel: ' . $profile['telephone'] : '',
    $profile['email']     !== '' ? 'Email: ' . $profile['email'] : '',
], fn($v) => $v !== '');

$motto = trim((string)$profile['motto']);

$website = trim((string)$profile['website']);
?>
<?php if ($faviconUrl): ?>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($faviconUrl, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/png" href="<?= htmlspecialchars($faviconUrl, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($faviconUrl, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

<style>
    .doc-header {
        margin-bottom: 1rem;
    }
    .doc-header.compact { margin-bottom: 1rem; }

    .doc-header .doc-brand-row {
        display: flex;
        align-items: center;
        gap: 1.1rem;
    }

    .doc-header .doc-logo-wrap {
        flex: 0 0 auto;
        width: 110px;
        height: 110px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .doc-header.compact .doc-logo-wrap {
        width: 180px;
        height: 180px;
    }
    .doc-header .doc-logo-wrap img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
    }

    .doc-header .doc-brand-meta {
        flex: 1 1 auto;
        min-width: 0;
    }

    .doc-header .doc-school-name {
        font-size: 1.55rem;
        font-weight: 700;
        letter-spacing: 0.015em;
        text-transform: uppercase;
        color: var(--accent-color, #0F1419);
        margin: 0;
        line-height: 1.15;
        overflow-wrap: anywhere;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .doc-header.compact .doc-school-name { font-size: 2.5rem; }

    .doc-header .doc-address {
        font-size: 0.82rem;
        color: var(--text-color, #334155);
        margin-top: 0.3rem;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }
    .doc-header.compact .doc-address { font-size: 0.76rem; margin-top: 0.2rem; }

    .doc-header .doc-contact {
        font-size: 0.82rem;
        color: var(--text-color, #334155);
        margin-top: 0.15rem;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }
    .doc-header.compact .doc-contact { font-size: 0.76rem; }

    .doc-header .doc-motto {
        font-size: 0.82rem;
        font-style: italic;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--accent-color, #1D9BF0);
        margin-top: 0.3rem;
    }
    .doc-header.compact .doc-motto { font-size: 0.74rem; margin-top: 0.2rem; }

    .doc-header .doc-website {
        font-size: 0.75rem;
        color: var(--text-muted, #64748B);
        margin-top: 0.15rem;
        overflow-wrap: anywhere;
    }

    .doc-header .doc-rule {
        border-top: 7px solid var(--accent-color, #0F1419);
        margin: 0.85rem 0 0;
    }
    .doc-header.compact .doc-rule { margin-top: 0.6rem; }

    .doc-title-banner {
        background: var(--accent-color, #0F1419);
        color: #ffffff;
        text-align: center;
        padding: 0.55rem 1rem;
        border-radius: 4px;
        margin-top: 0.5rem;
        margin-bottom: 0.85rem;
    }
    .doc-title-banner .doc-title {
        font-size: 0.95rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin: 0;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }
    .doc-title-banner .doc-title-sep {
        opacity: 0.85;
        margin: 0 0.3rem;
    }
    .doc-title-banner .doc-subtitle {
        display: inline;
        font-size: 0.88rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: #ffffff;
        opacity: 0.92;
    }
    .doc-header.compact + .doc-title-banner {
        padding: 0.4rem 0.75rem;
        margin-bottom: 0.6rem;
    }

    .doc-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 1.5rem;
        padding: 0.5rem 0.75rem;
        background: var(--background-color, #f8f9fa);
        border: 1px solid var(--border-color, #EFF3F4);
        border-radius: 6px;
        font-size: 0.82rem;
        margin-bottom: 0.75rem;
    }
    .doc-meta .meta-item { display: inline-flex; gap: 0.35rem; align-items: baseline; }
    .doc-meta .meta-label {
        color: var(--text-muted, #536471);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.04em;
    }
    .doc-meta .meta-value {
        color: var(--text-color, #0F1419);
        font-weight: 600;
    }

    @media print {
        .doc-header .doc-school-name { color: #000 !important; }
        .doc-header .doc-address,
        .doc-header .doc-contact { color: #222 !important; }
        .doc-header .doc-motto { color: #000 !important; }
        .doc-header .doc-rule { border-top-color: #000 !important; }
        .doc-title-banner {
            background: #000 !important;
            color: #fff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .doc-title-banner .doc-title,
        .doc-title-banner .doc-subtitle { color: #fff !important; }
        .doc-meta { background: #f5f5f5 !important; border-color: #ccc !important; }
    }

    @media (max-width: 575.98px) {
        .doc-header .doc-brand-row {
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 0.6rem;
        }
        .doc-header .doc-logo-wrap { width: 84px; height: 84px; }
        .doc-header .doc-school-name { font-size: 1.15rem; }
        .doc-header .doc-address,
        .doc-header .doc-contact { font-size: 0.74rem; }
    }
</style>

<div class="doc-header <?= $printCompact ? 'compact' : '' ?>">

    <div class="doc-brand-row">
        <?php if ($hasLogo): ?>
            <div class="doc-logo-wrap">
                <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($profile['name'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
        <?php endif; ?>

        <div class="doc-brand-meta">
            <?php if ($profile['name'] !== ''): ?>
                <h1 class="doc-school-name"><?= htmlspecialchars($profile['name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <?php endif; ?>

            <?php if (!empty($addressBits)): ?>
                <div class="doc-address">
                    <?= htmlspecialchars(implode(', ', $addressBits), ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($contactBits)): ?>
                <div class="doc-contact">
                    <?= htmlspecialchars(implode(' | ', $contactBits), ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if ($motto !== ''): ?>
                <div class="doc-motto"><?= htmlspecialchars($motto, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if ($website !== ''): ?>
                <div class="doc-website">Website: <?= htmlspecialchars($website, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="doc-rule"></div>
</div>

<?php if ($printTitle !== ''): ?>
    <div class="doc-title-banner">
        <span class="doc-title"><?= htmlspecialchars($printTitle, ENT_QUOTES, 'UTF-8') ?></span>
        <?php if ($printSubtitle !== ''): ?>
            <span class="doc-title-sep">:</span>
            <span class="doc-subtitle"><?= htmlspecialchars($printSubtitle, ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (!empty($printMeta)): ?>
    <div class="doc-meta">
        <?php foreach ($printMeta as $item): ?>
            <?php
                $label = $item['label'] ?? '';
                $value = $item['value'] ?? '';
                if ($label === '' && $value === '') continue;
            ?>
            <span class="meta-item">
                <?php if ($label !== ''): ?>
                    <span class="meta-label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>:</span>
                <?php endif; ?>
                <span class="meta-value"><?= htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') ?></span>
            </span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>