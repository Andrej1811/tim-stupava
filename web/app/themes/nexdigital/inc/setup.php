<?php
/**
 * Theme setup: supports, menus, image sizes, i18n.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Setup;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register theme supports.
 */
function after_setup_theme(): void {
    load_theme_textdomain('nexdigital', NEXDIGITAL_DIR . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_theme_support('align-wide');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    register_nav_menus([
        'primary' => __('Primary Menu', 'nexdigital'),
        'footer'  => __('Footer Menu', 'nexdigital'),
    ]);
}
add_action('after_setup_theme', __NAMESPACE__ . '\\after_setup_theme');

/**
 * Register content width.
 */
function content_width(): void {
    $GLOBALS['content_width'] = 1200;
}
add_action('after_setup_theme', __NAMESPACE__ . '\\content_width', 0);

/**
 * Drop the "Archívy:" / "Kategória:" prefix from archive headings.
 *
 * WordPress prefixes the heading with the kind of archive you are on, which
 * reads as an admin label rather than a page title — "Archívy: Kandidáti"
 * where the page is simply the candidates. Filtering the prefix rather than
 * rewriting the title covers every archive type at once and leaves the title
 * itself, and its translation, alone.
 */
function archive_title_prefix(): string {
    return '';
}
add_filter('get_the_archive_title_prefix', __NAMESPACE__ . '\\archive_title_prefix');

/**
 * End a trimmed excerpt with an ellipsis, not " [&hellip;]".
 *
 * The bracketed form is an editorial convention for "text was cut from a
 * quotation"; on a card it just reads as debris.
 */
function excerpt_more(): string {
    return '…';
}
add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');
