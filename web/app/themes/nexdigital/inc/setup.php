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
