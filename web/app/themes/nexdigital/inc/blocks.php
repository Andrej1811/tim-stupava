<?php
/**
 * Block bootstrap.
 *
 * Section blocks are registered through Secure Custom Fields: one file per
 * block in inc/blocks/, each holding the registration, its field group and the
 * render callback that maps fields onto an existing template part. Keeping the
 * three together is deliberate — a block's fields are meaningless without the
 * block, unlike the post and options groups in inc/fields/.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Blocks;

if (!defined('ABSPATH')) {
    exit;
}

/** Block category slug shared by every section block in this theme. */
const CATEGORY = 'tim-stupava';

/**
 * Put the theme's sections in their own category, above the core ones, so the
 * client is not hunting for "Hero" among fifty WordPress blocks.
 *
 * @param array<int, array<string, mixed>> $categories
 * @return array<int, array<string, mixed>>
 */
function register_category(array $categories): array {
    return array_merge(
        [
            [
                'slug'  => CATEGORY,
                'title' => __('Pre Stupavu — sekcie', 'nexdigital'),
            ],
        ],
        $categories
    );
}
add_filter('block_categories_all', __NAMESPACE__ . '\\register_category');

/** Load every block definition in inc/blocks/. */
function load_blocks(): void {
    if (!function_exists('acf_register_block_type')) {
        return;
    }

    $files = glob(__DIR__ . '/blocks/*.php');

    if (!is_array($files)) {
        return;
    }

    foreach ($files as $file) {
        require_once $file;
    }
}
add_action('acf/init', __NAMESPACE__ . '\\load_blocks');

/**
 * Section blocks always show their field form, never a live preview.
 *
 * These sections are full-bleed and often 600 px tall; inside the editor canvas
 * they render as an unreadable squeeze, and the client ends up editing fields
 * in a narrow sidebar. Forcing edit mode and removing the preview toggle keeps
 * the form where the content is.
 *
 * @param array<string, mixed> $block
 * @return array<string, mixed>
 */
function force_edit_mode(array $block): array {
    if (($block['category'] ?? '') !== CATEGORY) {
        return $block;
    }

    $block['mode'] = 'edit';
    $block['supports'] = array_merge(
        is_array($block['supports'] ?? null) ? $block['supports'] : [],
        ['mode' => false]
    );

    return $block;
}
add_filter('acf/register_block_type_args', __NAMESPACE__ . '\\force_edit_mode');
