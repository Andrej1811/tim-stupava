<?php
/**
 * Site identity: the header logo.
 *
 * Editable from Nastavenia webu → Logo a identita rather than the Customizer.
 * The theme has no other Customizer panels, and sending the client to a screen
 * they use for exactly one setting is how settings get lost — everything they
 * can change lives under one menu.
 *
 * The bundled file stays as a fallback so a fresh install, or a media item the
 * client deletes by accident, still renders a header instead of a gap.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Branding;

use function NexDigital\Theme\Fields\option;

if (!defined('ABSPATH')) {
    exit;
}

/** Bundled fallback, relative to the theme root. */
const FALLBACK_LOGO = '/resources/img/stupava.png';

/** Intrinsic size of the bundled fallback, so it reserves layout space too. */
const FALLBACK_WIDTH = 182;
const FALLBACK_HEIGHT = 200;

/** The agency's own site, filterable so the name and target travel together. */
const AGENCY_NAME = 'NexDigital';
const AGENCY_URL = 'https://nexdigital.sk/';

/**
 * Escape a line of client-entered text and turn our own name into a link.
 *
 * The zadávateľ line is a legally required free-text field, so it is escaped
 * first and the anchor injected afterwards — the only markup that can end up in
 * the output is the one built here.
 */
function agency_link(string $text): string {
    $safe = esc_html($text);
    $name = (string) apply_filters('nexdigital/agency_name', AGENCY_NAME);
    $url = (string) apply_filters('nexdigital/agency_url', AGENCY_URL);

    if ($name === '' || $url === '' || !str_contains($safe, $name)) {
        return $safe;
    }

    return str_replace(
        $name,
        sprintf(
            '<a href="%s" class="underline decoration-white/30 underline-offset-2 transition hover:text-teal-400 hover:decoration-teal-400" target="_blank" rel="noopener">%s</a>',
            esc_url($url),
            esc_html($name)
        ),
        $safe
    );
}

/** Attachment id of the uploaded logo, or 0 when unset. */
function logo_id(): int {
    $id = (int) apply_filters('nexdigital/logo_id', (int) option('opt_logo'));

    return $id > 0 && wp_attachment_is_image($id) ? $id : 0;
}

/**
 * Print the logo image.
 *
 * alt is empty on purpose: the wordmark next to it already names the link, so
 * an alt text here would make screen readers announce the site twice.
 *
 * @param string $classes Utility classes controlling the rendered size.
 */
function logo(string $classes): void {
    $attr = [
        'class'         => $classes,
        'alt'           => '',
        'decoding'      => 'async',
        'fetchpriority' => 'high',
    ];

    $id = logo_id();

    if ($id > 0) {
        echo wp_get_attachment_image($id, 'full', false, $attr);

        return;
    }

    printf(
        '<img src="%s" width="%d" height="%d" class="%s" alt="" decoding="async" fetchpriority="high">',
        esc_url(get_theme_file_uri(FALLBACK_LOGO)),
        FALLBACK_WIDTH,
        FALLBACK_HEIGHT,
        esc_attr($classes)
    );
}
