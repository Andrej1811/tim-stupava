<?php
/**
 * CMB2 bootstrap + example meta box.
 *
 * CMB2 ships via Composer at web/app/plugins/cmb2. We load its init file
 * directly so the theme works whether or not the plugin is activated. If the
 * plugin IS active, WordPress loads it first and the guard below is a no-op.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Meta;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load the CMB2 library if it is not already available.
 */
function bootstrap(): void {
    if (defined('CMB2_LOADED')) {
        return;
    }

    $init = WP_CONTENT_DIR . '/plugins/cmb2/init.php';

    if (is_readable($init)) {
        require_once $init;
    }
}
add_action('init', __NAMESPACE__ . '\\bootstrap', 9);

/**
 * Example meta box. Delete or duplicate as a template for real fields.
 *
 * Docs: https://cmb2.io/docs/
 */
function register_example_fields(): void {
    if (!function_exists('new_cmb2_box')) {
        return;
    }

    $box = new_cmb2_box([
        'id'           => 'nexdigital_page_settings',
        'title'        => __('Page Settings', 'nexdigital'),
        'object_types' => ['page'],
        'context'      => 'side',
        'priority'     => 'low',
    ]);

    $box->add_field([
        'name' => __('Subtitle', 'nexdigital'),
        'id'   => 'nexdigital_subtitle',
        'type' => 'text',
    ]);

    $box->add_field([
        'name' => __('Hide page title', 'nexdigital'),
        'id'   => 'nexdigital_hide_title',
        'type' => 'checkbox',
    ]);
}
add_action('cmb2_admin_init', __NAMESPACE__ . '\\register_example_fields');
