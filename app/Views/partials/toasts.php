<?php
// File: /app/Views/partials/toasts.php

use NexaT\Core\Toast;

$toasts = Toast::pull();

foreach (['success' => 'success', 'error' => 'error', 'warning' => 'warning', 'info' => 'info'] as $key => $type) {
    if (!empty($_SESSION['flash_' . $key])) {
        $toasts[] = [
            'type'    => $type,
            'message' => (string) $_SESSION['flash_' . $key],
        ];
        unset($_SESSION['flash_' . $key]);
    }
}

if (isset($flashBag) && is_array($flashBag)) {
    foreach ($flashBag as $type => $messages) {
        foreach ((array) $messages as $msg) {
            $toasts[] = ['type' => $type, 'message' => (string) $msg];
        }
    }
}

if (empty($toasts)) {
    echo '<div id="toastRoot" class="toast-root" aria-live="polite" aria-atomic="true"></div>';
    return;
}
?>
<div id="toastRoot" class="toast-root" aria-live="polite" aria-atomic="true">
    <?php foreach ($toasts as $i => $t):
        $type = in_array($t['type'] ?? 'info', ['success','error','warning','info'], true) ? $t['type'] : 'info';
        $msg  = (string) ($t['message'] ?? '');
        $title = (string) ($t['title'] ?? '');
        $duration = (int) ($t['duration'] ?? 5000);
        ?>
        <div class="toast-card toast-<?= $type ?>"
             role="alert"
             data-toast
             data-duration="<?= $duration ?>">
            <span class="toast-icon" aria-hidden="true">
                <?php if ($type === 'success'): ?>
                    <i class="fas fa-check-circle"></i>
                <?php elseif ($type === 'error'): ?>
                    <i class="fas fa-times-circle"></i>
                <?php elseif ($type === 'warning'): ?>
                    <i class="fas fa-exclamation-triangle"></i>
                <?php else: ?>
                    <i class="fas fa-info-circle"></i>
                <?php endif; ?>
            </span>
            <div class="toast-body">
                <?php if ($title !== ''): ?>
                    <div class="toast-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <div class="toast-msg"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <button type="button" class="toast-close" aria-label="Dismiss" data-toast-close>
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .toast-root {
        position: fixed;
        top: 16px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2000;
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: min(92vw, 440px);
        pointer-events: none;
    }

    .toast-card {
        pointer-events: auto;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 14px;
        background: var(--surface-color, #ffffff);
        color: var(--text-color, #0f1419);
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 14px;
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.06),
            0 14px 36px -14px rgba(15, 23, 42, 0.28);
        transform: translateY(-16px);
        opacity: 0;
        animation: toastIn 0.28s cubic-bezier(0.21, 1.02, 0.73, 1) forwards;
        will-change: transform, opacity;
    }

    .toast-card.toast-hide {
        animation: toastOut 0.22s ease forwards;
    }

    @keyframes toastIn {
        from { opacity: 0; transform: translateY(-16px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0)     scale(1); }
    }
    @keyframes toastOut {
        from { opacity: 1; transform: translateY(0)     scale(1); }
        to   { opacity: 0; transform: translateY(-12px) scale(0.98); }
    }

    .toast-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        flex-shrink: 0;
        font-size: 0.95rem;
        color: #fff;
    }

    .toast-success .toast-icon { background: #10b981; }
    .toast-error   .toast-icon { background: #ef4444; }
    .toast-warning .toast-icon { background: #f59e0b; }
    .toast-info    .toast-icon { background: #3b82f6; }

    .toast-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 3px;
        border-top-left-radius: 14px;
        border-top-right-radius: 14px;
    }
    .toast-card { position: relative; overflow: hidden; }
    .toast-success::before { background: #10b981; }
    .toast-error::before   { background: #ef4444; }
    .toast-warning::before { background: #f59e0b; }
    .toast-info::before    { background: #3b82f6; }

    .toast-body {
        flex: 1;
        min-width: 0;
    }
    .toast-title {
        font-size: 0.86rem;
        font-weight: 700;
        line-height: 1.25;
        margin-bottom: 2px;
    }
    .toast-msg {
        font-size: 0.82rem;
        line-height: 1.35;
        color: var(--text-color, #0f1419);
        word-wrap: break-word;
        overflow-wrap: anywhere;
    }
    .toast-title + .toast-msg {
        color: var(--text-muted, #536471);
        font-size: 0.8rem;
    }

    .toast-close {
        border: none;
        background: transparent;
        color: var(--text-muted, #94a3b8);
        cursor: pointer;
        padding: 4px;
        margin: -2px -4px 0 0;
        line-height: 1;
        border-radius: 6px;
        transition: color 0.15s ease, background-color 0.15s ease;
        flex-shrink: 0;
    }
    .toast-close:hover {
        color: var(--text-color, #0f1419);
        background: rgba(15, 23, 42, 0.06);
    }

    [data-theme="dark"] .toast-card {
        background: #1f2329;
        border-color: #2f3540;
        box-shadow:
            0 1px 2px rgba(0, 0, 0, 0.4),
            0 14px 36px -14px rgba(0, 0, 0, 0.7);
    }
    [data-theme="dark"] .toast-msg { color: #e5e7eb; }
    [data-theme="dark"] .toast-title { color: #f8fafc; }
    [data-theme="dark"] .toast-close:hover {
        background: rgba(255, 255, 255, 0.06);
        color: #f8fafc;
    }

    @media (prefers-reduced-motion: reduce) {
        .toast-card,
        .toast-card.toast-hide {
            animation-duration: 0.01ms !important;
        }
    }
</style>

<script>
    (function () {
        const root = document.getElementById('toastRoot');
        if (!root) return;

        const MAX_VISIBLE = 4;

        function prune() {
            const cards = root.querySelectorAll('[data-toast]:not(.toast-hide)');
            if (cards.length > MAX_VISIBLE) {
                cards[0].dispatchEvent(new Event('toast:dismiss'));
            }
        }

        function dismiss(card) {
            if (!card || card.classList.contains('toast-hide')) return;
            card.classList.add('toast-hide');
            card.addEventListener('animationend', () => card.remove(), { once: true });
            setTimeout(() => card.remove(), 400);
        }

        function bind(card) {
            if (card.dataset.bound === '1') return;
            card.dataset.bound = '1';

            const closeBtn = card.querySelector('[data-toast-close]');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => dismiss(card));
            }
            card.addEventListener('toast:dismiss', () => dismiss(card));

            const duration = parseInt(card.dataset.duration || '5000', 10);
            if (duration > 0) {
                setTimeout(() => dismiss(card), duration);
            }
        }

        root.querySelectorAll('[data-toast]').forEach(bind);
        prune();

        const typeMeta = {
            success: { icon: 'fas fa-check-circle',    duration: 4000 },
            error:   { icon: 'fas fa-times-circle',    duration: 6500 },
            warning: { icon: 'fas fa-exclamation-triangle', duration: 5500 },
            info:    { icon: 'fas fa-info-circle',     duration: 4500 },
        };

        function show(type, message, title, duration) {
            type = typeMeta[type] ? type : 'info';
            const meta = typeMeta[type];
            const card = document.createElement('div');
            card.className = 'toast-card toast-' + type;
            card.setAttribute('role', 'alert');
            card.setAttribute('data-toast', '');
            card.dataset.duration = String(duration ?? meta.duration);

            const titleHtml = title
                ? `<div class="toast-title">${escapeHtml(title)}</div>`
                : '';

            card.innerHTML = `
                <span class="toast-icon" aria-hidden="true"><i class="${meta.icon}"></i></span>
                <div class="toast-body">
                    ${titleHtml}
                    <div class="toast-msg">${escapeHtml(message ?? '')}</div>
                </div>
                <button type="button" class="toast-close" aria-label="Dismiss" data-toast-close>
                    <i class="fas fa-times"></i>
                </button>
            `;

            root.appendChild(card);
            bind(card);
            prune();
        }

        function escapeHtml(s) {
            return String(s ?? '').replace(/[&<>"']/g, (c) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[c]));
        }

        window.NexaToast = {
            show,
            success: (m, t, d) => show('success', m, t, d),
            error:   (m, t, d) => show('error',   m, t, d),
            warning: (m, t, d) => show('warning', m, t, d),
            info:    (m, t, d) => show('info',    m, t, d),
            clear:   () => root.querySelectorAll('[data-toast]').forEach(dismiss),
        };
    })();
</script>