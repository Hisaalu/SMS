<?php
// File: /app/Core/ThemeService.php

namespace NexaT\Core;

class ThemeService
{
    private SettingsService $settings;

    private const FONT_STACKS = [
        'system'    => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
        'inter'     => '"Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
        'roboto'    => '"Roboto", "Helvetica Neue", Arial, sans-serif',
        'open_sans' => '"Open Sans", "Segoe UI", Roboto, sans-serif',
        'lato'      => '"Lato", "Segoe UI", Roboto, sans-serif',
        'poppins'   => '"Poppins", "Segoe UI", Roboto, sans-serif',
        'georgia'   => 'Georgia, "Times New Roman", Times, serif',
        'mono'      => '"SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace',
    ];

    public function __construct(SettingsService $settings)
    {
        $this->settings = $settings;
    }

    public static function fontOptions(): array
    {
        return [
            'system'    => 'System Default (fastest)',
            'inter'     => 'Inter',
            'roboto'    => 'Roboto',
            'open_sans' => 'Open Sans',
            'lato'      => 'Lato',
            'poppins'   => 'Poppins',
            'georgia'   => 'Georgia (serif)',
            'mono'      => 'Monospace',
        ];
    }

    public function resolveFontStack(string $key): string
    {
        return self::FONT_STACKS[$key] ?? self::FONT_STACKS['system'];
    }

    public function cssVariables(): string
    {
        $t = $this->settings->getTheme();
        $fontStack = $this->resolveFontStack((string) $t['font_family']);

        $vars = [
            '--primary-color'     => $t['primary'],
            '--secondary-color'   => $t['secondary'],
            '--accent-color'      => $t['accent'],
            '--background-color'  => $t['background'],
            '--surface-color'     => $t['surface'],
            '--text-color'        => $t['text'],
            '--text-muted'        => $t['muted'],
            '--border-color'      => $t['border'],
            '--success-color'     => $t['success'],
            '--warning-color'     => $t['warning'],
            '--danger-color'      => $t['danger'],

            '--sidebar-bg'          => $t['sidebar_bg'],
            '--sidebar-text'        => $t['sidebar_text'],
            '--sidebar-active-bg'   => $t['sidebar_active_bg'],
            '--sidebar-active-text' => $t['sidebar_active_text'],
            '--navbar-bg'           => $t['navbar_bg'],
            '--navbar-text'         => $t['navbar_text'],

            '--font-family-base'    => $fontStack,
            '--font-size-base'      => $t['font_size_base'] . 'rem',
            '--font-size-small'     => $t['font_size_small'] . 'rem',
            '--font-size-heading'   => $t['font_size_heading'] . 'rem',
            '--line-height-base'    => (string) $t['line_height'],
            '--font-weight-normal'  => (string) $t['font_weight_normal'],
            '--font-weight-medium'  => (string) $t['font_weight_medium'],
            '--font-weight-bold'    => (string) $t['font_weight_bold'],

            '--sidebar-width'       => $t['sidebar_width'] . 'px',
            '--border-radius-base'  => $t['border_radius'] . 'rem',
        ];

        $css = ":root {\n";
        foreach ($vars as $name => $value) {
            $css .= "    {$name}: {$value};\n";
        }
        $css .= "}\n";

        // Dark palette — applied via [data-theme="dark"] on <html>,
        // so it can be toggled at runtime by the user.
        $css .= $this->darkModeOverrides();

        return $css;
    }

    /**
     * Dark palette, activated when <html data-theme="dark"> is set.
     * If admin enabled "use_system_setting", also honors OS preference
     * when no explicit user choice is stored.
     */
    private function darkModeOverrides(): string
    {
        $t = $this->settings->getTheme();

        $dark = [
            '--primary-color'     => '#F1F5F9',
            '--secondary-color'   => '#E2E8F0',
            '--background-color'  => '#0B1220',
            '--surface-color'     => '#111827',
            '--text-color'        => '#E5E7EB',
            '--text-muted'        => '#9CA3AF',
            '--border-color'      => '#1F2937',
            '--sidebar-bg'        => '#0F172A',
            '--sidebar-text'      => '#CBD5E1',
            '--navbar-bg'         => '#0F172A',
            '--navbar-text'       => '#F1F5F9',
        ];

        $css = "[data-theme=\"dark\"] {\n";
        foreach ($dark as $name => $value) {
            $css .= "    {$name}: {$value};\n";
        }
        $css .= "}\n";

        // If admin allows system preference AND user hasn't made an explicit
        // choice, follow the OS. The body-level JS sets data-theme on load
        // based on stored preference, so this handles the "no JS yet" case.
        if (!empty($t['use_system_setting'])) {
            $css .= "@media (prefers-color-scheme: dark) {\n";
            $css .= "    :root:not([data-theme=\"light\"]):not([data-theme=\"dark\"]) {\n";
            foreach ($dark as $name => $value) {
                $css .= "        {$name}: {$value};\n";
            }
            $css .= "    }\n}\n";
        }

        return $css;
    }

    public function googleFontsLink(): ?string
    {
        $t = $this->settings->getTheme();
        $key = (string) $t['font_family'];

        $map = [
            'inter'     => 'Inter:wght@400;500;600;700',
            'roboto'    => 'Roboto:wght@400;500;700',
            'open_sans' => 'Open+Sans:wght@400;500;600;700',
            'lato'      => 'Lato:wght@400;700',
            'poppins'   => 'Poppins:wght@400;500;600;700',
        ];

        if (!isset($map[$key])) {
            return null;
        }

        return 'https://fonts.googleapis.com/css2?family=' . $map[$key] . '&display=swap';
    }
}