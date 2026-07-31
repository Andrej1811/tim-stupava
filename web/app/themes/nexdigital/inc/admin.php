<?php
/**
 * Admin list tables.
 *
 * Program and Výsledky are the same `projekt` records either side of
 * ts_stav = dokoncene, which is the right content model but a poor admin
 * screen: the list showed fifty rows with no way to tell which side a project
 * was on. This adds the stage as a column, a filter for it, and two submenu
 * entries so "Výsledky" is findable by the name the client uses for it.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Admin;

use function NexDigital\Theme\PostTypes\project_stages;
use function NexDigital\Theme\PostTypes\stage_label;
use const NexDigital\Theme\PostTypes\STAGE_DONE;

if (!defined('ABSPATH')) {
    exit;
}

/** Pseudo-stage for the filter: everything the Program page would show. */
const FILTER_UNFINISHED = '_program';

/**
 * Add the stage and photo columns.
 *
 * @param array<string, string> $columns
 * @return array<string, string>
 */
function project_columns(array $columns): array {
    $insert = [
        'ts_stav'  => __('Stav', 'nexdigital'),
        'ts_fotky' => __('Fotky', 'nexdigital'),
    ];

    // Before the date column, which is the least useful thing on this screen.
    $position = array_search('date', array_keys($columns), true);

    if ($position === false) {
        return $columns + $insert;
    }

    return array_slice($columns, 0, $position, true)
        + $insert
        + array_slice($columns, $position, null, true);
}
add_filter('manage_projekt_posts_columns', __NAMESPACE__ . '\\project_columns');

/** Render the added columns. */
function project_column(string $column, int $post_id): void {
    if ($column === 'ts_stav') {
        $stage = (string) get_post_meta($post_id, 'ts_stav', true);

        if ($stage === '') {
            echo '—';

            return;
        }

        printf(
            '<span style="display:inline-block;padding:2px 8px;border-radius:9px;font-size:11px;font-weight:600;%s">%s</span>',
            $stage === STAGE_DONE
                ? 'background:#096165;color:#fff'
                : 'background:#f0f0f1;color:#3c434a',
            esc_html(stage_label($stage))
        );

        return;
    }

    if ($column === 'ts_fotky') {
        $gallery = get_post_meta($post_id, 'ts_galeria', true);
        $count = is_array($gallery) ? count($gallery) : 0;
        $has_thumb = has_post_thumbnail($post_id);

        if (!$has_thumb && $count === 0) {
            // The one thing still missing across most of the record, so it is
            // worth saying plainly rather than leaving the cell blank.
            printf('<span style="color:#b32d2e">%s</span>', esc_html__('chýbajú', 'nexdigital'));

            return;
        }

        printf(
            '%s%s',
            $has_thumb ? esc_html__('hlavná', 'nexdigital') : esc_html__('bez hlavnej', 'nexdigital'),
            $count > 0 ? esc_html(sprintf(__(' + %d v galérii', 'nexdigital'), $count)) : ''
        );
    }
}
add_action('manage_projekt_posts_custom_column', __NAMESPACE__ . '\\project_column', 10, 2);

/** Make the stage column sortable. */
function project_sortable(array $columns): array {
    $columns['ts_stav'] = 'ts_stav';

    return $columns;
}
add_filter('manage_edit-projekt_sortable_columns', __NAMESPACE__ . '\\project_sortable');

/** A dropdown above the list, so "show me the finished ones" is one click. */
function project_filter(string $post_type): void {
    if ($post_type !== 'projekt') {
        return;
    }

    $current = isset($_GET['ts_stav']) ? sanitize_text_field(wp_unslash((string) $_GET['ts_stav'])) : '';

    echo '<select name="ts_stav">';
    printf('<option value="">%s</option>', esc_html__('Všetky stavy', 'nexdigital'));

    // "Program" is not a stage — it is every stage except the finished one.
    printf(
        '<option value="%s"%s>%s</option>',
        esc_attr(FILTER_UNFINISHED),
        selected($current, FILTER_UNFINISHED, false),
        esc_html__('— Program (všetko okrem dokončených) —', 'nexdigital')
    );

    foreach (project_stages() as $value => $label) {
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($value),
            selected($current, $value, false),
            esc_html($label)
        );
    }

    echo '</select>';
}
add_action('restrict_manage_posts', __NAMESPACE__ . '\\project_filter');

/** Apply the dropdown, and the sort, to the admin query. */
function project_query(\WP_Query $query): void {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if (!$screen || $screen->id !== 'edit-projekt') {
        return;
    }

    $stage = isset($_GET['ts_stav']) ? sanitize_text_field(wp_unslash((string) $_GET['ts_stav'])) : '';

    if ($stage === FILTER_UNFINISHED) {
        $query->set('meta_query', [[
            'key'     => 'ts_stav',
            'value'   => STAGE_DONE,
            'compare' => '!=',
        ]]);
    } elseif ($stage !== '' && array_key_exists($stage, project_stages())) {
        $query->set('meta_key', 'ts_stav');
        $query->set('meta_value', $stage);
    }

    if ($query->get('orderby') === 'ts_stav') {
        $query->set('meta_key', 'ts_stav');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', __NAMESPACE__ . '\\project_query');

/**
 * Submenu shortcuts.
 *
 * The client looks for "Výsledky", the name used on the site and in the menu —
 * not for a filter inside Projekty. These are the same screen with the filter
 * pre-applied.
 */
function project_submenus(): void {
    add_submenu_page(
        'edit.php?post_type=projekt',
        __('Výsledky', 'nexdigital'),
        __('Výsledky', 'nexdigital'),
        'edit_posts',
        'edit.php?post_type=projekt&ts_stav=' . STAGE_DONE
    );

    add_submenu_page(
        'edit.php?post_type=projekt',
        __('Program', 'nexdigital'),
        __('Program', 'nexdigital'),
        'edit_posts',
        'edit.php?post_type=projekt&ts_stav=' . FILTER_UNFINISHED
    );
}
add_action('admin_menu', __NAMESPACE__ . '\\project_submenus');
