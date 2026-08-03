<?php
/**
 * Plugin Name: Allow translation updates
 * Description: Lets WordPress download and update language packs while DISALLOW_FILE_MODS keeps plugin and theme files locked.
 * Version: 1.0.0
 * Author: NexDigital
 *
 * The site is sk_SK and WP_LANG_DIR (web/app/languages) is gitignored on
 * purpose — translations are data WordPress fetches from api.wordpress.org,
 * not code that belongs in the repository. But DISALLOW_FILE_MODS, which
 * Bedrock sets in config/application.php to keep the Composer workflow the
 * only way code reaches the server, blocks language packs along with
 * everything else: a fresh deploy would leave wp-admin, Yoast and Secure
 * Custom Fields in English with no way to fix it from the admin.
 *
 * wp_is_file_mod_allowed() passes a context string to the file_mod_allowed
 * filter, so the two language-pack contexts can be opened without touching
 * the rest. Every other context — install_plugins, capability_update_core,
 * capability_edit_themes, automatic_updater — keeps the constant's answer.
 *
 * This is a filter rather than a constant because constants are all-or-
 * nothing: config/environments/staging.php can only flip DISALLOW_FILE_MODS
 * wholesale, which would also re-open plugin and theme installs from the
 * admin — installs that the next Composer deploy silently wipes.
 *
 * An mu-plugin rather than theme code: it is site policy, it has to survive a
 * theme switch, and it must be in place before capabilities are mapped.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Mu\Translations;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Contexts wp_is_file_mod_allowed() uses for language packs.
 *
 * `can_install_language_pack` gates the install_languages / update_languages
 * capabilities (wp-includes/capabilities.php) and wp_can_install_language_pack();
 * `download_language_pack` gates the download itself
 * (wp-admin/includes/translation-install.php).
 */
const LANGUAGE_CONTEXTS = [
    'can_install_language_pack',
    'download_language_pack',
];

/**
 * Allow file modifications for language packs only.
 *
 * @param bool   $allowed Whether file modifications are allowed.
 * @param string $context The usage context.
 */
function allow_language_packs(bool $allowed, string $context): bool {
    return in_array($context, LANGUAGE_CONTEXTS, true) ? true : $allowed;
}
add_filter('file_mod_allowed', __NAMESPACE__ . '\\allow_language_packs', 10, 2);
