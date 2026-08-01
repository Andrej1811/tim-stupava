<?php
/**
 * Candidates section.
 *
 * Layout only: the leader cards on top, the rest in a grid, both rendered by
 * template-parts/candidate-card.php. Keeping the section separate from the
 * block means an archive template can reuse the same arrangement.
 *
 * Expected args: eyebrow, title, text, leaders WP_Post[], rest WP_Post[],
 * link array{url,title,target}|null, heading ('h1' on the archive, 'h2' inside
 * the front page where the page already owns its h1).
 *
 * @package NexDigital
 */

declare(strict_types=1);

$eyebrow = trim((string) ($args['eyebrow'] ?? ''));
$title = trim((string) ($args['title'] ?? ''));
$text = trim((string) ($args['text'] ?? ''));
$leaders = is_array($args['leaders'] ?? null) ? $args['leaders'] : [];
$rest = is_array($args['rest'] ?? null) ? $args['rest'] : [];
$link = is_array($args['link'] ?? null) ? $args['link'] : null;
$truncated = !empty($args['truncated']);

$link_url = trim((string) ($link['url'] ?? ''));
$link_label = trim((string) ($link['title'] ?? ''));

// The archive is the page's own subject, so its heading is the h1. Inside the
// front page the same section is one of several, and the page owns the h1.
$heading = ($args['heading'] ?? 'h2') === 'h1' ? 'h1' : 'h2';
?>

<section class="bg-sand-50 py-16 sm:py-20 lg:py-24">
    <div class="site-container">

        <?php if ($eyebrow !== '' || $title !== '' || $text !== '') : ?>
            <div class="max-w-2xl">
                <?php if ($eyebrow !== '') : ?>
                    <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                        <?php echo esc_html($eyebrow); ?>
                    </p>
                <?php endif; ?>

                <?php if ($title !== '') : ?>
                    <<?php echo $heading; ?> class="mt-4 text-3xl font-black leading-[1.05] tracking-tight text-ink sm:text-4xl lg:text-5xl">
                        <?php echo esc_html($title); ?>
                    </<?php echo $heading; ?>>
                <?php endif; ?>

                <?php if ($text !== '') : ?>
                    <p class="mt-5 text-base leading-relaxed text-slate-700">
                        <?php echo esc_html($text); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($leaders !== []) : ?>
            <div class="mt-10 grid gap-6 lg:mt-12 <?php echo count($leaders) > 1 ? 'lg:grid-cols-2' : ''; ?>">
                <?php foreach ($leaders as $leader) : ?>
                    <?php get_template_part('template-parts/candidate-card', null, [
                        'post_id' => $leader->ID,
                        'variant' => 'featured',
                    ]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($rest !== []) : ?>
            <?php // Five across matches the ballot's own rhythm on a wide screen and
                  // keeps the tiles large enough to recognise a face. ?>
            <ul class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:mt-8 lg:grid-cols-5 lg:gap-6">
                <?php foreach ($rest as $candidate) : ?>
                    <li>
                        <?php get_template_part('template-parts/candidate-card', null, [
                            'post_id' => $candidate->ID,
                            'variant' => 'compact',
                        ]); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php // The button only appears when the grid is actually cut short. With
              // every candidate already on the page it would send people to a page
              // showing the same twelve faces. ?>
        <?php if ($truncated && $link_url !== '' && $link_label !== '') : ?>
            <div class="mt-10 flex justify-center">
                <a
                    href="<?php echo esc_url($link_url); ?>"
                    <?php echo ($link['target'] ?? '') !== '' ? 'target="' . esc_attr($link['target']) . '" rel="noopener"' : ''; ?>
                    class="btn btn-outline"
                >
                    <?php echo esc_html($link_label); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
