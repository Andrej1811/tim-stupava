<?php
/**
 * Candidate profile hero.
 *
 * The same two-column shape as the featured card on the front page, so a
 * visitor who clicked that card lands on something that reads as the same
 * object made larger. The dark panel carries the name and the ballot number;
 * the studio portrait sits on sand and doubles as the video poster.
 *
 * Expected args:
 *   post_id int Candidate post.
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

$name = get_the_title($post_id);
$number = trim((string) (field('cislo', $post_id) ?? ''));
$role = trim((string) (field('pozicia', $post_id) ?? ''));
$bio = trim((string) (field('kratke_bio', $post_id) ?? ''));
$email = trim((string) (field('email', $post_id) ?? ''));
$facebook = trim((string) (field('facebook', $post_id) ?? ''));

$video = source($post_id);
?>

<section class="bg-sand-100 lg:grid lg:min-h-[34rem] lg:grid-cols-2">

    <?php // Panel first in the DOM so a screen reader reads the name before the
          // photograph; order-first moves the photograph above it on a phone,
          // where the face is what tells you you reached the right person. ?>
    <div class="flex flex-col justify-center gap-6 bg-brand-950 p-6 text-white sm:p-8 lg:p-12">
        <?php if ($number !== '' || $role !== '') : ?>
            <div class="flex items-center gap-4">
                <?php if ($number !== '') : ?>
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full border-2 border-teal-400 text-2xl font-black leading-none text-teal-400">
                        <?php echo esc_html($number); ?>
                    </span>
                <?php endif; ?>

                <?php if ($role !== '') : ?>
                    <p class="text-[0.5625rem] font-bold uppercase leading-tight tracking-[0.2em] text-teal-400">
                        <?php echo esc_html($role); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <h1 class="text-4xl font-black leading-[1.05] tracking-tight sm:text-5xl lg:text-6xl">
            <?php echo esc_html($name); ?>
        </h1>

        <?php if ($bio !== '') : ?>
            <p class="max-w-prose text-base leading-relaxed text-slate-200 sm:text-lg">
                <?php echo esc_html($bio); ?>
            </p>
        <?php endif; ?>

        <?php // Nothing filled in means no row at all, rather than a heading with
              // an empty space under it. ?>
        <?php if ($email !== '' || $facebook !== '') : ?>
            <div class="flex flex-wrap gap-3">
                <?php if ($email !== '') : ?>
                    <a
                        href="<?php echo esc_url('mailto:' . $email); ?>"
                        class="inline-flex items-center gap-2 rounded-md border border-white/40 px-4 py-2.5 text-sm font-bold transition hover:border-white hover:bg-white/10"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect width="20" height="16" x="2" y="4" rx="2" />
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                        </svg>
                        <?php echo esc_html($email); ?>
                    </a>
                <?php endif; ?>

                <?php if ($facebook !== '') : ?>
                    <a
                        href="<?php echo esc_url($facebook); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-md border border-white/40 px-4 py-2.5 text-sm font-bold transition hover:border-white hover:bg-white/10"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                        </svg>
                        <?php esc_html_e('Facebook', 'nexdigital'); ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php // aspect-4/5 on a phone rather than the photo's own 3:4, which at full
          // width would be 133vw tall and push the name off the screen. ?>
    <div
        class="relative order-first aspect-4/5 overflow-hidden lg:order-none lg:aspect-auto lg:h-full"
        <?php echo $video !== null ? 'data-video-facade' : ''; ?>
    >
        <?php if (has_post_thumbnail($post_id)) : ?>
            <?php echo get_the_post_thumbnail($post_id, 'large', [
                'class'    => 'absolute inset-0 h-full w-full object-cover object-top',
                'alt'      => $name,
                'decoding' => 'async',
            ]); ?>
        <?php else : ?>
            <?php // Not every candidate has photographs delivered — Vladislav Obadal
                  // has none at all — so the letter keeps the column from collapsing. ?>
            <span class="absolute inset-0 flex items-center justify-center bg-sand-100 text-8xl font-black text-sand-300" aria-hidden="true">
                <?php echo esc_html(mb_substr($name, 0, 1)); ?>
            </span>
        <?php endif; ?>

        <?php get_template_part('template-parts/video-facade', null, [
            'video' => $video,
            'title' => sprintf(__('Video-vizitka — %s', 'nexdigital'), $name),
            'size'  => 'lg',
        ]); ?>
    </div>
</section>
