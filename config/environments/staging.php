<?php

/**
 * Configuration overrides for WP_ENV === 'staging'
 */

use Roots\WPConfig\Config;
use function Env\env;

/**
 * You should try to keep staging as close to production as possible. However,
 * should you need to, you can always override production configuration values
 * with `Config::define`.
 *
 * Example: `Config::define('WP_DEBUG', true);`
 * Example: `Config::define('DISALLOW_FILE_MODS', false);`
 */

Config::define('DISALLOW_INDEXING', true);

/**
 * The theme keeps GTM off outside production. For a measurement test on
 * staging, set NEXDIGITAL_FORCE_GTM=true in .env, clear the Cache Enabler
 * cache (cached HTML predates the flip in both directions), test with Tag
 * Assistant, then remove the variable and clear the cache again.
 */
Config::define('NEXDIGITAL_FORCE_GTM', env('NEXDIGITAL_FORCE_GTM') ?: false);

/**
 * DISALLOW_FILE_MODS stays true here, inherited from application.php.
 *
 * Translation updates in wp-admin do NOT need it flipped: language packs are
 * opened per-context by the file_mod_allowed filter in
 * web/app/mu-plugins/allow-translation-updates.php, on every environment.
 * Flipping the constant would also re-open plugin and theme installs from the
 * admin, and anything installed that way is wiped by the next Composer deploy.
 */
