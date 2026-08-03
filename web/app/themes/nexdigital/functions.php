<?php
/**
 * NexDigital theme bootstrap.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme;

if (!defined('ABSPATH')) {
    exit;
}

/** Absolute path to the theme directory, no trailing slash. */
define('NEXDIGITAL_DIR', get_theme_file_path());

/** Public URI of the theme directory, no trailing slash. */
define('NEXDIGITAL_URI', get_theme_file_uri());

/** Theme version — bump to bust asset caches in production. */
define('NEXDIGITAL_VERSION', '1.0.0');

/**
 * Load a theme include from the /inc directory.
 */
function require_inc(string $relative): void {
    $path = NEXDIGITAL_DIR . '/inc/' . ltrim($relative, '/');

    if (is_readable($path)) {
        require_once $path;
    }
}

// Order matters: Vite helper first, then consumers.
require_inc('vite.php');
require_inc('setup.php');
require_inc('security.php');
require_inc('assets.php');
require_inc('nav.php');
require_inc('consent.php');
// post-types before fields: the project field group reads project_stages().
require_inc('post-types.php');
require_inc('fields.php');
// analytics and branding after fields: both read theme options.
require_inc('analytics.php');
require_inc('branding.php');
require_inc('video.php');
require_inc('social.php');
// admin after post-types: the project list table reads project_stages().
require_inc('admin.php');
// forms after fields: the form definitions read the contact e-mail option.
require_inc('forms.php');
// blocks after fields: block field groups use the Fields helpers.
require_inc('blocks.php');
