<?php
/**
 * Candidate card.
 *
 * One component, two shapes. `compact` is the grid tile; `featured` is the wide
 * card the leader gets, with the video vizitka playable in place. The variant is
 * passed in rather than derived here, so an archive can force a shape the front
 * page would not choose.
 *
 * Expected args:
 *   post_id int    Candidate post.
 *   variant string 'compact' (default) or 'featured'.
 *
 * @package NexDigital
 */

declare(strict_types=1);

use function NexDigital\Theme\Fields\field;
use function NexDigital\Theme\Video\source;

$post_id = (int) ($args['post_id'] ?? 0);

if ($post_id <= 0) {
    return;
}

$featured = ($args['variant'] ?? 'compact') === 'featured';

$name = get_the_title($post_id);
$permalink = get_permalink($post_id);
$number = (string) (field('cislo', $post_id) ?? '');
$role = (string) (field('pozicia', $post_id) ?? '');
$bio = (string) (field('kratke_bio', $post_id) ?? '');

$video = source($post_id);

/** Portrait, or a lettered placeholder when a candidate has no photo yet. */
$portrait = static function (string $classes) use ($post_id, $name): void {
    if (has_post_thumbnail($post_id)) {
        echo get_the_post_thumbnail($post_id, 'large', [
            'class'    => $classes . ' h-full w-full object-cover object-top',
            'alt'      => $name,
            'loading'  => 'lazy',
            'decoding' => 'async',
        ]);

        return;
    }

    // Not every candidate has photographs delivered; an initial keeps the grid
    // rectangular instead of collapsing one tile.
    printf(
        '<span class="%s flex h-full w-full items-center justify-center bg-sand-100 text-4xl font-black text-sand-300" aria-hidden="true">%s</span>',
        esc_attr($classes),
        esc_html(mb_substr($name, 0, 1))
    );
};

/** The ballot number: the one thing a voter has to carry into the booth. */
$ballot = static function (string $classes) use ($number): void {
    if ($number === '') {
        return;
    }

    printf(
        '<span class="%s">%s</span>',
        esc_attr($classes),
        esc_html($number)
    );
};
?>

<?php if ($featured) : ?>
    <?php // A floor, not a fixed height: left to itself the portrait column stretches
          // to the photo's own 3:4 and the card grows taller than the grid of tiles
          // below it, which inverts the hierarchy — but a hard height would clip a
          // longer bio, and the editor cannot see that from the field. ?>
    <article class="overflow-hidden rounded-lg bg-brand-950 text-white md:grid md:min-h-[26rem] md:grid-cols-2 lg:min-h-[30rem]">
        <div class="relative aspect-4/5 md:aspect-auto md:h-full" <?php echo $video !== null ? 'data-video-facade' : ''; ?>>
            <?php // Absolute, so the photo's own 3:4 does not set the row height —
                  // the text panel and the card's min-height decide that. ?>
            <?php $portrait('absolute inset-0'); ?>

            <?php get_template_part('template-parts/video-facade', null, [
                'video' => $video,
                'title' => sprintf(__('Video-vizitka — %s', 'nexdigital'), $name),
                'size'  => 'sm',
            ]); ?>
        </div>

        <div class="flex flex-col justify-center gap-5 p-6 sm:p-8 lg:p-10">
            <div class="flex items-center gap-4">
                <?php $ballot('flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2 border-teal-400 text-xl font-black leading-none text-teal-400'); ?>
                <?php if ($role !== '') : ?>
                    <p class="text-[0.5625rem] font-bold uppercase leading-tight tracking-[0.2em] text-teal-400">
                        <?php echo esc_html($role); ?>
                    </p>
                <?php endif; ?>
            </div>

            <h3 class="text-3xl font-black leading-[1.05] tracking-tight sm:text-4xl">
                <?php echo esc_html($name); ?>
            </h3>

            <?php if ($bio !== '') : ?>
                <p class="text-sm leading-relaxed text-slate-200 sm:text-base">
                    <?php echo esc_html($bio); ?>
                </p>
            <?php endif; ?>

            <p>
                <a
                    href="<?php echo esc_url($permalink); ?>"
                    class="link-arrow text-white hover:text-teal-400"
                >
                    <?php esc_html_e('Profil kandidáta', 'nexdigital'); ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                </a>
            </p>
        </div>
    </article>
<?php else : ?>
    <article class="group relative flex flex-col overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:border-slate-300 hover:shadow-sm">
        <div class="relative aspect-3/4 overflow-hidden bg-sand-100">
            <?php $portrait('transition duration-300 group-hover:scale-[1.03]'); ?>
            <?php $ballot('absolute left-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white/95 text-sm font-black text-brand-700'); ?>
        </div>

        <div class="flex flex-1 flex-col p-4">
            <h3 class="text-base font-black leading-tight text-ink">
                <?php // The stretched link makes the whole tile clickable without
                      // wrapping a block-level anchor around the article. ?>
                <a href="<?php echo esc_url($permalink); ?>" class="after:absolute after:inset-0 after:content-['']">
                    <?php echo esc_html($name); ?>
                </a>
            </h3>

            <?php if ($role !== '') : ?>
                <p class="mt-1.5 text-[0.6875rem] font-semibold uppercase leading-tight tracking-[0.08em] text-slate-500">
                    <?php echo esc_html($role); ?>
                </p>
            <?php endif; ?>
        </div>
    </article>
<?php endif; ?>
