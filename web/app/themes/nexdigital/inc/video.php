<?php
/**
 * Video embeds.
 *
 * The site never lets YouTube or Vimeo load on page view. A candidate's video
 * card renders a poster image with a play button, and the player URL built here
 * is only fetched after the visitor clicks — same reasoning as the self-hosted
 * fonts: no third-country request happens without an action that asks for it.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Video;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Turn a YouTube or Vimeo page URL into a player URL that starts on load.
 *
 * Returns null for anything unrecognised, which is the signal to render no
 * play button at all — a button that opens nothing is worse than no button.
 */
function embed_url(string $url): ?string {
    $url = trim($url);

    if ($url === '') {
        return null;
    }

    // youtu.be/ID, youtube.com/watch?v=ID, /embed/ID, /shorts/ID
    if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $m) === 1) {
        // nocookie is not consent on its own, but it keeps the player from
        // writing tracking cookies for a visitor who only watched a video.
        return sprintf(
            'https://www.youtube-nocookie.com/embed/%s?autoplay=1&rel=0&modestbranding=1',
            rawurlencode($m[1])
        );
    }

    if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m) === 1) {
        return sprintf('https://player.vimeo.com/video/%s?autoplay=1', rawurlencode($m[1]));
    }

    return null;
}

/**
 * Pick the video a candidate actually has.
 *
 * An uploaded file beats a link: it needs no third-party player at all. The
 * link goes through embed_url(), which returns null for anything it cannot turn
 * into a player URL — and null here is the signal to render no play button,
 * because a button that opens nothing is worse than no button.
 *
 * Kept separate from source() so the decision can be tested without a
 * WordPress bootstrap.
 *
 * @param mixed  $file SCF file field value; an array carrying a url when set.
 * @param string $link Raw oembed field value — the page URL, not the iframe.
 * @return array{url: string, file: bool}|null
 */
function resolve(mixed $file, string $link): ?array {
    $uploaded = is_array($file) ? trim((string) ($file['url'] ?? '')) : '';

    if ($uploaded !== '') {
        return ['url' => $uploaded, 'file' => true];
    }

    $embed = embed_url($link);

    return $embed === null ? null : ['url' => $embed, 'file' => false];
}

/**
 * The same decision, for a candidate post.
 *
 * The oembed field's rendered value is an iframe this site never prints, so the
 * raw value is requested with get_field()'s third argument set to false. That
 * is the one bare get_field() call in the theme, hence the guard.
 *
 * @return array{url: string, file: bool}|null
 */
function source(int $post_id): ?array {
    $link = function_exists('get_field')
        ? (string) (get_field(\NexDigital\Theme\Fields\key('video'), $post_id, false) ?? '')
        : '';

    return resolve(\NexDigital\Theme\Fields\field('video_subor', $post_id), $link);
}
