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
 * Floating "reopen cookie preferences" control, bottom left.
 *
 * A button, not a link: it opens a dialog rather than navigating. The library
 * binds it by the data-cc attribute, so no per-element JS is needed.
 *
 * Printed with `hidden` and revealed by the consent module once a decision
 * exists. Two reasons: it shares the bottom-left corner with the consent
 * banner and would collide with it, and a control that cannot work without the
 * bundle should not be visible when the bundle failed to load.
 *
 * Bottom left rather than the usual bottom right — the right corner is where
 * chat widgets and back-to-top buttons end up, and the donate CTA is the thing
 * that deserves that side of the screen.
 */
function preferences_button(): void {
    ?>
<button
    type="button"
    data-cc="show-preferencesModal"
    aria-haspopup="dialog"
    class="hidden fixed bottom-4 left-4 z-40 h-12 w-12 items-center justify-center rounded-full bg-brand-600 text-white shadow-lg shadow-brand-950/30 transition hover:bg-brand-700 hover:scale-105 sm:bottom-6 sm:left-6 sm:h-14 sm:w-14"
>
    <span class="sr-only"><?php esc_html_e('Nastavenia cookies', 'nexdigital'); ?></span>
    <?php // lucide "cookie" ?>
    <svg class="h-6 w-6 sm:h-7 sm:w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5" />
        <path d="M8.5 8.5v.01" />
        <path d="M16 15.5v.01" />
        <path d="M12 12v.01" />
        <path d="M11 17v.01" />
        <path d="M7 14v.01" />
    </svg>
</button>
    <?php
}
add_action('wp_footer', __NAMESPACE__ . '\\preferences_button', 20);
