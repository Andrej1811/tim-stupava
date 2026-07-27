<?php
/**
 * Navigation helpers.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Nav;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Default primary navigation, used until a menu is assigned in wp-admin.
 *
 * Mirrors the sitemap in the client brief (Návrh.pdf) minus "Domov" — the
 * wordmark already links home, so a Domov item would be a duplicate target.
 * "Podporte nás" is intentionally absent: it is the header CTA, not a nav item.
 *
 * @return array<string,string> path => label
 */
function primary_items(): array {
    return [
        'kandidati' => __('Kandidáti', 'nexdigital'),
        'program'   => __('Program', 'nexdigital'),
        'vysledky'  => __('Výsledky', 'nexdigital'),
        'novinky'   => __('Novinky', 'nexdigital'),
        'kontakt'   => __('Kontakt', 'nexdigital'),
    ];
}

/**
 * Render the fallback primary menu.
 *
 * Matches the markup wp_nav_menu() produces closely enough that the .site-nav
 * component styles (including the current-item marker) apply unchanged.
 */
function primary_fallback(): void {
    $current = trim((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/');

    echo '<ul class="site-nav-list flex flex-col gap-1 lg:flex-row lg:items-center lg:gap-7">';

    foreach (primary_items() as $slug => $label) {
        $classes = $current === $slug ? 'current-menu-item' : '';

        printf(
            '<li class="%s"><a href="%s">%s</a></li>',
            esc_attr($classes),
            esc_url(home_url('/' . $slug . '/')),
            esc_html($label)
        );
    }

    echo '</ul>';
}

/**
 * Render the primary menu, falling back to primary_fallback().
 */
function primary_menu(): void {
    wp_nav_menu([
        'theme_location' => 'primary',
        'container'      => false,
        'menu_class'     => 'site-nav-list flex flex-col gap-1 lg:flex-row lg:items-center lg:gap-7',
        'fallback_cb'    => __NAMESPACE__ . '\\primary_fallback',
        'depth'          => 1,
    ]);
}

/**
 * URL the header call-to-action points at, filterable so the donate target can
 * move without touching templates.
 */
function support_url(): string {
    return (string) apply_filters('nexdigital/support_url', home_url('/podpora/'));
}
