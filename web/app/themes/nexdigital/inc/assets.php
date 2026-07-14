<?php
/**
 * Front-end and editor asset loading via Vite.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Assets;

use NexDigital\Theme\Vite\Vite;

if (!defined('ABSPATH')) {
    exit;
}

/** Vite entry points: handle => source path (relative to theme root). */
function entries(): array {
    return [
        'app' => 'resources/js/app.js',
    ];
}

/**
 * Enqueue theme assets on the front end.
 */
function enqueue_frontend(): void {
    Vite::enqueue(entries());
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_frontend');
