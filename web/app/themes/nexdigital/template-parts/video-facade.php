<?php
/**
 * Video facade — the play button, not the player.
 *
 * The player URL sits in a data attribute and nothing is fetched until the
 * visitor clicks; resources/js/modules/video-facade.js then replaces the
 * contents of the nearest [data-video-facade] ancestor. That ancestor and the
 * poster behind it belong to the caller, because the card and the profile use
 * different images at different aspect ratios.
 *
 * Expected args:
 *   video array{url: string, file: bool}  From Video\source().
 *   title string                          Accessible name of the video.
 *   size  string                          'sm' (card) or 'lg' (profile).
 *
 * @package NexDigital
 */

declare(strict_types=1);

$video = is_array($args['video'] ?? null) ? $args['video'] : null;

if ($video === null || trim((string) ($video['url'] ?? '')) === '') {
    return;
}

$title = (string) ($args['title'] ?? '');
$large = ($args['size'] ?? 'sm') === 'lg';

// An uploaded file plays in a native <video>; a link needs an iframe. The JS
// picks the tag by which attribute it finds, so the name carries the meaning.
$attribute = ($video['file'] ?? false) ? 'data-video-file' : 'data-video-play';

$button = $large ? 'h-20 w-20' : 'h-16 w-16';
$icon = $large ? 'h-9 w-9' : 'h-7 w-7';
?>

<button
    type="button"
    class="group absolute inset-0 flex items-center justify-center bg-brand-950/25 transition hover:bg-brand-950/10"
    <?php echo esc_attr($attribute); ?>="<?php echo esc_url($video['url']); ?>"
    data-video-title="<?php echo esc_attr($title); ?>"
>
    <span class="sr-only"><?php esc_html_e('Prehrať video-vizitku', 'nexdigital'); ?></span>
    <span class="<?php echo esc_attr($button); ?> flex items-center justify-center rounded-full bg-white text-brand-700 shadow-lg transition group-hover:scale-105">
        <svg class="<?php echo esc_attr($icon); ?> ml-1" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M8 5.14v13.72a1 1 0 0 0 1.54.84l10.3-6.86a1 1 0 0 0 0-1.68L9.54 4.3A1 1 0 0 0 8 5.14Z" />
        </svg>
    </span>
</button>

<span class="pointer-events-none absolute bottom-4 left-4 rounded bg-brand-950/80 px-3 py-1.5 text-[0.6875rem] font-bold uppercase tracking-[0.12em] text-teal-400 backdrop-blur">
    <?php esc_html_e('Video-vizitka', 'nexdigital'); ?>
</span>
