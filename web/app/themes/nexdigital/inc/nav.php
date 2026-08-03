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
 *
 * Order follows the campaign's argument: who we are (Kandidáti), what we
 * delivered (Výsledky), what comes next (Program), then the softer pages.
 * Výsledky before Program is deliberate for an incumbent — the record earns
 * the right to make promises.
 *
 * "Podpora" and the "Podporte nás" CTA are different things and both exist:
 * Podpora is who backs us (parties, endorsements), the CTA is money. Keeping
 * one label for both would make the donate ask disappear into an about page.
 *
 * @return array<string,string> path => label
 */
function primary_items(): array {
    return [
        'kandidati' => __('Kandidáti', 'nexdigital'),
        'vysledky'  => __('Výsledky', 'nexdigital'),
        'program'   => __('Program', 'nexdigital'),
        'novinky'   => __('Novinky', 'nexdigital'),
        'podpora'   => __('Podpora', 'nexdigital'),
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
 * Keep current_page_parent honest on the Novinky menu item.
 *
 * WordPress hangs current_page_parent on the page_for_posts item for every
 * view that is not a page — including the kandidat archive and the project
 * detail — which would light Novinky up while Kandidáti is the open page.
 * The class stays only where Novinky really is the parent: posts and their
 * archives.
 *
 * @param array<int,string> $classes
 */
function trim_blog_parent_class(array $classes, object $item): array {
    $is_blog_view = is_home() || is_singular('post') || is_category() || is_tag() || is_date();

    if (!$is_blog_view && (int) ($item->object_id ?? 0) === (int) get_option('page_for_posts')) {
        $classes = array_values(array_diff($classes, ['current_page_parent']));
    }

    return $classes;
}
add_filter('nav_menu_css_class', __NAMESPACE__ . '\\trim_blog_parent_class', 10, 2);

/**
 * URL the header call-to-action points at, filterable so the donate target can
 * move without touching templates.
 */
function support_url(): string {
    return (string) apply_filters('nexdigital/support_url', home_url('/podpora/'));
}
