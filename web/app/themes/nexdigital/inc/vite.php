<?php
/**
 * Minimal Vite integration for classic WordPress themes.
 *
 * Dev mode: assets are served from the Vite dev server (HMR). Detected by the
 * presence of a `hot` file that the Vite config writes on `npm run dev`.
 *
 * Prod mode: assets are read from the build manifest produced by `npm run build`.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Vite;

if (!defined('ABSPATH')) {
    exit;
}

final class Vite {
    /** Build output directory, relative to the theme root. */
    private const DIST = 'public/build';

    /** Hot file written by the Vite dev server, relative to the theme root. */
    private const HOT = 'public/hot';

    /** Script handles that must be rendered as ES modules. */
    private static array $modules = [];

    /** Register the module-tag filter once. */
    public static function boot(): void {
        add_filter('script_loader_tag', [self::class, 'filterModuleTag'], 10, 3);
    }

    /** Are we running against the Vite dev server? */
    public static function isDev(): bool {
        return is_readable(get_theme_file_path('/' . self::HOT));
    }

    /** Dev server base URL, e.g. http://localhost:5173 (no trailing slash). */
    public static function devUrl(): string {
        $url = (string) file_get_contents(get_theme_file_path('/' . self::HOT));

        return rtrim(trim($url), '/');
    }

    /**
     * Enqueue one or more Vite entries.
     *
     * @param array<string,string> $entries handle => entry path (e.g. ['app' => 'resources/js/app.js'])
     */
    public static function enqueue(array $entries): void {
        self::isDev()
            ? self::enqueueDev($entries)
            : self::enqueueProd($entries);
    }

    private static function enqueueDev(array $entries): void {
        $base = self::devUrl();

        self::registerModule('vite-client');
        wp_enqueue_script('vite-client', $base . '/@vite/client', [], null, false);

        foreach ($entries as $handle => $entry) {
            $tag = 'nexdigital-' . $handle;
            self::registerModule($tag);
            wp_enqueue_script($tag, $base . '/' . ltrim($entry, '/'), [], null, false);
        }
    }

    private static function enqueueProd(array $entries): void {
        $manifest = self::manifest();

        if ($manifest === null) {
            return;
        }

        foreach ($entries as $handle => $entry) {
            $entry = ltrim($entry, '/');

            if (!isset($manifest[$entry])) {
                continue;
            }

            $chunk = $manifest[$entry];

            // CSS imported by the JS entry.
            foreach (($chunk['css'] ?? []) as $i => $css) {
                wp_enqueue_style(
                    'nexdigital-' . $handle . '-css-' . $i,
                    self::assetUrl($css),
                    [],
                    null
                );
            }

            $file = $chunk['file'] ?? null;

            if ($file === null) {
                continue;
            }

            if (str_ends_with($file, '.css')) {
                wp_enqueue_style('nexdigital-' . $handle, self::assetUrl($file), [], null);
                continue;
            }

            $tag = 'nexdigital-' . $handle;
            self::registerModule($tag);
            wp_enqueue_script($tag, self::assetUrl($file), [], null, true);
        }
    }

    /** Decode the build manifest, or null when it is missing. */
    private static function manifest(): ?array {
        $path = get_theme_file_path('/' . self::DIST . '/.vite/manifest.json');

        if (!is_readable($path)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : null;
    }

    /** Public URL for a built asset path from the manifest. */
    private static function assetUrl(string $file): string {
        return get_theme_file_uri('/' . self::DIST . '/' . ltrim($file, '/'));
    }

    private static function registerModule(string $handle): void {
        self::$modules[$handle] = true;
    }

    /** Force `type="module"` on Vite-managed handles. */
    public static function filterModuleTag(string $tag, string $handle, string $src): string {
        if (!isset(self::$modules[$handle])) {
            return $tag;
        }

        return sprintf(
            '<script type="module" src="%s" id="%s-js"></script>' . "\n",
            esc_url($src),
            esc_attr($handle)
        );
    }
}

Vite::boot();
