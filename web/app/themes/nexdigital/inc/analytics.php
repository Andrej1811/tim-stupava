<?php
/**
 * Google Tag Manager, gated by Google Consent Mode v2.
 *
 * The container id comes from Nastavenia webu → Meranie, so the client can
 * point the site at their own GTM without a deploy, and staging can simply
 * leave the field empty.
 *
 * The container itself always loads — GTM sets no cookies on its own. What is
 * gated is storage: consent defaults to denied for everything that can
 * identify a visitor, and the cookie banner lifts that per category. This is
 * the arrangement Google documents for the EEA, and it keeps conversion
 * modelling working where a hard "don't load GTM at all" gate would not.
 *
 * The defaults are printed unconditionally rather than derived from the
 * consent cookie, so the markup stays identical for every visitor and is safe
 * to put behind a full-page cache later.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Analytics;

use function NexDigital\Theme\Fields\option;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shape of a GTM container id. Google issues 7 alphanumerics today; the range
 * is loose enough to survive that changing, tight enough that nothing can be
 * smuggled into the inline script through the settings field.
 */
const CONTAINER_PATTERN = '/^GTM-[A-Z0-9]{4,12}$/';

/**
 * Validated container id, or an empty string when unset or malformed.
 *
 * The filter runs before validation on purpose: a filtered value gets the same
 * scrutiny as one typed into wp-admin.
 */
function container_id(): string {
    $id = (string) apply_filters('nexdigital/gtm_id', (string) option('opt_gtm_id'));
    $id = strtoupper(trim($id));

    return preg_match(CONTAINER_PATTERN, $id) === 1 ? $id : '';
}

/**
 * Should the container be printed for this request?
 *
 * Two exclusions, both inside the filter's default so a test override can
 * still force the container on:
 *
 * - Non-production environments. The container id lives in the database, so a
 *   copy of the production DB on localhost or staging would otherwise feed
 *   anonymous local traffic straight into the client's real GA4 property.
 *   WP_ENV in .env is what decides — see config/application.php.
 * - Logged-in editors — on a town-sized campaign site the team's own visits
 *   are a large share of traffic and would distort every report.
 *
 * Verify measurement in a private window on production, or locally with
 * add_filter('nexdigital/gtm_enabled', '__return_true').
 */
function is_enabled(): bool {
    if (container_id() === '') {
        return false;
    }

    // NEXDIGITAL_FORCE_GTM=true in .env lets staging run a full measurement
    // test (Tag Assistant needs the container in the page). Editors stay
    // excluded even then — the point is testing as a visitor.
    $environment_ok = wp_get_environment_type() === 'production'
        || (defined('NEXDIGITAL_FORCE_GTM') && NEXDIGITAL_FORCE_GTM);

    $default = $environment_ok && !current_user_can('edit_posts');

    return (bool) apply_filters('nexdigital/gtm_enabled', $default);
}

/**
 * Consent defaults plus the container loader, as high in <head> as WordPress
 * allows.
 *
 * `wait_for_update` holds tags for 500 ms so the banner's stored decision can
 * arrive before anything fires; the theme bundle pushes that update as soon as
 * it executes, which is before DOMContentLoaded.
 */
function print_head(): void {
    if (!is_enabled()) {
        return;
    }

    $id = container_id();
    ?>
<!-- Google Consent Mode v2 -->
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('consent', 'default', {
    ad_storage: 'denied',
    ad_user_data: 'denied',
    ad_personalization: 'denied',
    analytics_storage: 'denied',
    personalization_storage: 'denied',
    functionality_storage: 'granted',
    security_storage: 'granted',
    wait_for_update: 500
});
</script>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo esc_js($id); ?>');</script>
    <?php
}
add_action('wp_head', __NAMESPACE__ . '\\print_head', 1);

/**
 * The <noscript> fallback iframe, which Google requires directly after <body>.
 */
function print_body(): void {
    if (!is_enabled()) {
        return;
    }

    printf(
        '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n",
        esc_attr(container_id())
    );
}
add_action('wp_body_open', __NAMESPACE__ . '\\print_body');
