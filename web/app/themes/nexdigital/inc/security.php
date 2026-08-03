<?php
/**
 * Hardening: close the doors this site does not use.
 *
 * Nothing here is a substitute for a strong password — it removes the free
 * reconnaissance and the bulk-guessing endpoints, so an attacker is left with
 * one login form and no username.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Security;

if (!defined('ABSPATH')) {
    exit;
}

/*
 * XML-RPC. Nothing on this site talks to it — no Jetpack, no remote
 * publishing app — and its system.multicall method lets a single request
 * carry hundreds of password guesses, sidestepping any per-request login
 * throttle. Returning an empty method table kills pingbacks too, which
 * xmlrpc_enabled alone would leave open.
 */
add_filter('xmlrpc_enabled', '__return_false');
add_filter('xmlrpc_methods', '__return_empty_array');

/**
 * The X-Pingback header advertises the endpoint on every response; with the
 * endpoint dead the advertisement is pure reconnaissance.
 *
 * @param array<string, string> $headers
 * @return array<string, string>
 */
function drop_pingback_header(array $headers): array {
    unset($headers['X-Pingback']);

    return $headers;
}
add_filter('wp_headers', __NAMESPACE__ . '\\drop_pingback_header');

/**
 * REST user listing. /wp-json/wp/v2/users hands the login name of every
 * account to anyone who asks, which is half of a login attempt. The routes
 * stay available to logged-in users — the block editor's author dropdown
 * needs them.
 *
 * @param array<string, mixed> $endpoints
 * @return array<string, mixed>
 */
function hide_user_routes(array $endpoints): array {
    if (!is_user_logged_in()) {
        unset($endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }

    return $endpoints;
}
add_filter('rest_endpoints', __NAMESPACE__ . '\\hide_user_routes');

/**
 * Author archives. A one-author campaign site has no use for them, and
 * /?author=1 canonically redirects to /author/<login>/ — the same username
 * leak over plain HTTP. Priority 0 so the 404 lands before
 * redirect_canonical() (priority 10 on the same hook) issues that redirect.
 */
function no_author_archives(): void {
    if (!is_author()) {
        return;
    }

    global $wp_query;

    $wp_query->set_404();
    status_header(404);
    nocache_headers();
}
add_action('template_redirect', __NAMESPACE__ . '\\no_author_archives', 0);
