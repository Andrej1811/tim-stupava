<?php
/**
 * Social profiles.
 *
 * The networks come from Nastavenia webu → Sociálne siete, so the footer prints
 * whatever the client filled in and nothing when they filled in nothing. Icons
 * are inline SVG — the theme uses no icon font.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Social;

use function NexDigital\Theme\Fields\option;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Icon paths, drawn on the 24×24 lucide grid so they sit next to the theme's
 * other icons without looking imported. TikTok has no lucide equivalent, so its
 * glyph is drawn as a filled path.
 *
 * @return array<string, array{label: string, path: string, fill: bool}>
 */
function icons(): array {
    return [
        'facebook' => [
            'label' => 'Facebook',
            'fill'  => false,
            'path'  => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
        ],
        'instagram' => [
            'label' => 'Instagram',
            'fill'  => false,
            'path'  => '<rect width="20" height="20" x="2" y="2" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><path d="M17.5 6.5h.01"/>',
        ],
        'youtube' => [
            'label' => 'YouTube',
            'fill'  => false,
            'path'  => '<path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/>',
        ],
        'linkedin' => [
            'label' => 'LinkedIn',
            'fill'  => false,
            'path'  => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/>',
        ],
        'tiktok' => [
            'label' => 'TikTok',
            'fill'  => true,
            'path'  => '<path d="M16.5 2h-3v13.2a2.7 2.7 0 1 1-2.2-2.65V9.4a5.9 5.9 0 1 0 5.2 5.85V8.9a6.6 6.6 0 0 0 3.9 1.27V7.05A3.9 3.9 0 0 1 16.5 3.5z"/>',
        ],
    ];
}

/**
 * Profiles the client actually filled in.
 *
 * @return array<int, array{network: string, label: string, url: string}>
 */
function profiles(): array {
    $rows = option('opt_socialne');
    $icons = icons();
    $profiles = [];

    foreach (is_array($rows) ? $rows : [] as $row) {
        $network = (string) ($row['siet'] ?? '');
        $url = trim((string) ($row['url'] ?? ''));

        if ($url === '' || !isset($icons[$network])) {
            continue;
        }

        $profiles[] = [
            'network' => $network,
            'label'   => $icons[$network]['label'],
            'url'     => $url,
        ];
    }

    return $profiles;
}

/**
 * Print the icon list.
 *
 * @param string $classes Utility classes for each link.
 */
function links(string $classes): void {
    $icons = icons();

    foreach (profiles() as $profile) {
        $icon = $icons[$profile['network']];

        printf(
            '<a href="%1$s" class="%2$s" target="_blank" rel="noopener noreferrer">'
                . '<span class="sr-only">%3$s</span>'
                . '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="%4$s" stroke="%5$s" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%6$s</svg>'
                . '</a>',
            esc_url($profile['url']),
            esc_attr($classes),
            esc_html($profile['label']),
            $icon['fill'] ? 'currentColor' : 'none',
            $icon['fill'] ? 'none' : 'currentColor',
            $icon['path'] // Literal paths from icons() above.
        );
    }
}
