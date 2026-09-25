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

    public function hexToRgb(string $hex, string $fallback = '37,99,235'): string
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return $fallback;
        }

        return sprintf(
            '%d,%d,%d',
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        );
    }

    public function cssVariables(): string
    {
        $t = $this->settings->getTheme();
        $fontStack = $this->resolveFontStack((string) $t['font_family']);

        $accentRgb = $this->hexToRgb((string) $t['accent']);

        $vars = [
            '--primary-color'     => $t['primary'],
            '--secondary-color'   => $t['secondary'],
            '--accent-color'      => $t['accent'],
            '--accent-rgb'        => $accentRgb,
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

        $css .= $this->themeOverrides();

        return $css;
    }

    private function themeOverrides(): string
    {
        $t = $this->settings->getTheme();

        $light = [
            '--input-bg'           => '#FFFFFF',
            '--input-border'       => '#CBD5E1',
            '--input-border-focus' => $t['accent'],
            '--input-shadow'       => 'inset 0 1px 2px rgba(15, 23, 42, 0.04)',
        ];

        $dark = [
            '--primary-color'      => '#F8FAFC',
            '--secondary-color'    => '#CBD5E1',
            '--background-color'   => '#0F1115',
            '--surface-color'      => '#171A21',
            '--text-color'         => '#F1F5F9',
            '--text-muted'         => '#94A3B8',
            '--border-color'       => '#2A2F3A',
            '--sidebar-bg'         => '#111318',
            '--sidebar-text'       => '#CBD5E1',
            '--navbar-bg'          => '#111318',
            '--navbar-text'        => '#F8FAFC',
            '--input-bg'           => '#0F1115',
            '--input-border'       => '#3F4756',
            '--input-border-focus' => $t['accent'],
            '--input-shadow'       => 'inset 0 1px 2px rgba(0, 0, 0, 0.35)',
        ];

        $css = ":root {\n";
        foreach ($light as $name => $value) {
            $css .= "    {$name}: {$value};\n";
        }
        $css .= "}\n";

        $css .= "[data-theme=\"dark\"] {\n";
        foreach ($dark as $name => $value) {
            $css .= "    {$name}: {$value};\n";
        }
        $css .= "}\n";

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