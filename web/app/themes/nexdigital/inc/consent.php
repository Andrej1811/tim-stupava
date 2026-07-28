<?php
/**
 * Cookie consent glue.
 *
 * The banner itself lives in resources/js/modules/cookie-consent.js. PHP only
 * hands it the values it cannot know at build time — currently the privacy
 * policy URL, which is a WordPress setting and can differ per environment.
 *
 * Passed as a JSON <script> block rather than wp_localize_script(): the Vite
 * bundle is an ES module, so wp_localize_script() would have to attach to a
 * handle that changes name between dev and prod builds.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Consent;

if (!defined('ABSPATH')) {
    exit;
}

/** Element id the JS module looks for. Keep in sync with cookie-consent.js. */
const CONFIG_ID = 'nexdigital-consent-config';

/**
 * URL of the privacy policy, as linked from the banner and the preferences
 * modal.
 *
 * Prefers the page assigned under Settings → Privacy. Falls back to a fixed
 * slug so the link is never empty on a fresh install — an unlinked policy is
 * a compliance gap, not a cosmetic one.
 */
function privacy_url(): string {
    $url = (string) get_privacy_policy_url();

    if ($url === '') {
        $url = home_url('/ochrana-osobnych-udajov/');
    }

    return (string) apply_filters('nexdigital/privacy_url', $url);
}

/**
 * Config consumed by the consent module.
 *
 * @return array<string,mixed>
 */
function config(): array {
    return (array) apply_filters('nexdigital/consent_config', [
        'privacyUrl' => privacy_url(),
    ]);
}

/**
 * Print the config block.
 *
 * Priority 5 puts it ahead of the footer-enqueued theme bundle; the module
 * reads it on DOMContentLoaded, so head-enqueued dev builds are fine too.
 */
function print_config(): void {
    printf(
        '<script type="application/json" id="%s">%s</script>' . "\n",
        esc_attr(CONFIG_ID),
        wp_json_encode(config())
    );
}
add_action('wp_footer', __NAMESPACE__ . '\\print_config', 5);

/**
 * Render the "reopen preferences" control.
 *
 * A button, not a link: it opens a dialog rather than navigating. The library
 * binds it by the data-cc attribute, so no per-element JS is needed.
 */
function preferences_button(string $classes = ''): void {
    printf(
        '<button type="button" data-cc="show-preferencesModal" class="%s">%s</button>',
        esc_attr($classes),
        esc_html__('Nastavenia cookies', 'nexdigital')
    );
}
