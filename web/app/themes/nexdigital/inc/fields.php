<?php
/**
 * Secure Custom Fields bootstrap.
 *
 * SCF is the WordPress.org-maintained fork of ACF and keeps the `acf_*` API,
 * so field groups are registered in PHP rather than clicked together in the
 * admin. Registering in code means field definitions are reviewable, travel
 * with the repo, and cannot drift between environments.
 *
 * Field groups live one-per-subject in inc/fields/ so the client-facing admin
 * maps onto files a developer can find.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Fields;

if (!defined('ABSPATH')) {
    exit;
}

/** Meta key prefix. Keeps theme fields distinct from plugin meta. */
const PREFIX = 'ts_';

/** Prefix a field name. */
function key(string $name): string {
    return PREFIX . $name;
}

/**
 * Read a theme field, prefix included.
 *
 * Templates go through this rather than calling get_field() directly so that
 * deactivating SCF degrades the site to missing content instead of a fatal
 * error, and so the ts_ prefix lives in exactly one place.
 *
 * @param string                 $name    Field name without the prefix.
 * @param int|string|null        $post_id Post ID, or 'option' for options pages.
 */
function field(string $name, int|string|null $post_id = null): mixed {
    if (function_exists('get_field')) {
        return get_field(key($name), $post_id ?? false);
    }

    if ($post_id === 'option') {
        return get_option('options_' . key($name)) ?: null;
    }

    return get_post_meta((int) ($post_id ?? get_the_ID()), key($name), true) ?: null;
}

/** Read a field from the theme options pages. */
function option(string $name): mixed {
    return field($name, 'option');
}

/**
 * Build an image field with a mandatory size/format hint.
 *
 * Every image field on this site goes through here. Editors upload whatever
 * their phone produced unless the box tells them otherwise, and a campaign
 * site is edited under time pressure by non-designers — so the guidance has to
 * be on the field, not in a handover document nobody reopens.
 *
 * @param array<string,mixed> $field      Standard SCF field definition.
 * @param string              $dimensions Recommended pixel size, e.g. "1200 × 1600 px".
 * @param string              $ratio      Human aspect ratio, e.g. "3:4 (na výšku)".
 * @param string              $formats    Accepted formats.
 * @return array<string,mixed>
 */
function image_field(array $field, string $dimensions, string $ratio, string $formats = 'JPG alebo WebP'): array {
    $hint = sprintf(
        /* translators: 1: pixel dimensions, 2: aspect ratio, 3: file formats */
        __('Odporúčaný rozmer: %1$s — pomer strán %2$s. Formát: %3$s, ideálne do 500 kB. Menší obrázok bude rozmazaný, väčší zbytočne spomalí web.', 'nexdigital'),
        $dimensions,
        $ratio,
        $formats
    );

    $existing = $field['instructions'] ?? '';
    $field['instructions'] = $existing === '' ? $hint : $existing . ' ' . $hint;

    // Union assigns only keys the caller did not set.
    return $field + [
        'type'          => 'image',
        'return_format' => 'id',
        'preview_size'  => 'medium',
        'library'       => 'all',
        'mime_types'    => 'jpg,jpeg,png,webp',
    ];
}

/**
 * Register the theme options screen.
 *
 * One parent page with focused sub-pages beats a single screen with twenty
 * tabs — the client looks for "Kontakt", not for a tab inside "Nastavenia".
 */
function register_options_pages(): void {
    if (!function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => __('Nastavenia webu', 'nexdigital'),
        'menu_title' => __('Nastavenia webu', 'nexdigital'),
        'menu_slug'  => 'nastavenia-webu',
        'capability' => 'edit_theme_options',
        'position'   => 59,
        'icon_url'   => 'dashicons-admin-settings',
        'redirect'   => true,
    ]);

    // Identity first: the parent menu redirects to whichever sub-page leads.
    $children = [
        'identita'  => __('Logo a identita', 'nexdigital'),
        'kontakt'   => __('Kontakt', 'nexdigital'),
        'kandidati' => __('Kandidáti', 'nexdigital'),
        'socialne'  => __('Sociálne siete', 'nexdigital'),
        'podpora'   => __('Podpora a dary', 'nexdigital'),
        'partneri'  => __('Partneri', 'nexdigital'),
        'zdielanie' => __('Zdieľanie a SEO', 'nexdigital'),
        'meranie'   => __('Meranie', 'nexdigital'),
    ];

    foreach ($children as $slug => $title) {
        acf_add_options_sub_page([
            'page_title'  => $title,
            'menu_title'  => $title,
            'menu_slug'   => 'nastavenia-' . $slug,
            'parent_slug' => 'nastavenia-webu',
            'capability'  => 'edit_theme_options',
        ]);
    }
}
add_action('acf/init', __NAMESPACE__ . '\\register_options_pages');

/**
 * Load the individual field group definitions.
 *
 * `acf/include_fields` is the hook SCF/ACF 6 expects for local field groups.
 */
function load_groups(): void {
    foreach (['page', 'candidate', 'project', 'options'] as $file) {
        $path = __DIR__ . '/fields/' . $file . '.php';

        if (is_readable($path)) {
            require_once $path;
        }
    }
}
add_action('acf/include_fields', __NAMESPACE__ . '\\load_groups');

/**
 * Hide the SCF admin UI for everyone but administrators.
 *
 * The client should edit content, not field definitions — and since the groups
 * are defined in code, anything changed in the UI would be silently
 * overwritten on the next deploy.
 */
function restrict_field_ui(bool $show): bool {
    return current_user_can('manage_options') && (defined('WP_DEBUG') && WP_DEBUG) ? $show : false;
}
add_filter('acf/settings/show_admin', __NAMESPACE__ . '\\restrict_field_ui');
