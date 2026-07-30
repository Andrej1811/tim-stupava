<?php
/**
 * Candidate profile.
 *
 * Orchestration only — the hero, the CV and the ballot call to action are
 * template parts. The back link is the single way out of a profile; there is
 * deliberately no roster strip and no previous/next pager, which would repeat
 * the grid the visitor just came from.
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();

while (have_posts()) :
    the_post();

    $post_id = get_the_ID();
    $archive = get_post_type_archive_link('kandidat');
    ?>

    <?php if (is_string($archive) && $archive !== '') : ?>
        <nav class="bg-sand-50" aria-label="<?php esc_attr_e('Omrvinky', 'nexdigital'); ?>">
            <div class="site-container py-4">
                <a
                    href="<?php echo esc_url($archive); ?>"
                    class="group inline-flex items-center gap-2 text-sm font-bold text-slate-600 transition hover:text-brand-600"
                >
                    <svg class="h-4 w-4 transition group-hover:-translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 12H5M11 18l-6-6 6-6" />
                    </svg>
                    <?php esc_html_e('Späť na kandidátov', 'nexdigital'); ?>
                </a>
            </div>
        </nav>
    <?php endif; ?>

    <article <?php post_class(); ?>>
        <?php get_template_part('template-parts/candidate-hero', null, ['post_id' => $post_id]); ?>

        <?php // An empty section with the heading "O kandidátovi" over nothing is
              // worse than no section, and eleven of the twelve profiles start
              // life with no CV written. ?>
        <?php if (trim(get_the_content()) !== '') : ?>
            <div class="site-container max-w-3xl py-14 sm:py-16">
                <h2 class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                    <?php esc_html_e('O kandidátovi', 'nexdigital'); ?>
                </h2>

                <div class="rich-text mt-6">
                    <?php the_content(); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php get_template_part('template-parts/candidate-cta', null, ['post_id' => $post_id]); ?>
    </article>

    <?php
endwhile;

get_footer();
