<?php
/**
 * Single page template.
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();

while (have_posts()) :
    the_post();

    $hide_title = (bool) \NexDigital\Theme\Fields\field('hide_title');
    $subtitle   = (string) \NexDigital\Theme\Fields\field('subtitle');

    // A page built from the theme's section blocks lays itself out: each block
    // is a full-bleed <section> that brings its own background and gutter. Left
    // inside the narrow reading column below, those sections get clipped to
    // max-w-3xl and their backgrounds stop mid-page. Pages written as prose —
    // the privacy policy — still want that column, so the wrapper depends on
    // what the content actually is.
    $has_sections = has_block('acf/ts-kontakt')
        || has_block('acf/ts-kandidati')
        || has_block('acf/ts-projekty')
        || has_block('acf/ts-hero-carousel')
        || has_block('acf/ts-ako-volit');
    ?>

    <?php if ($has_sections) : ?>
        <article <?php post_class(); ?>>
            <?php if (!$hide_title) : ?>
                <header class="site-container pt-10">
                    <h1 class="text-3xl font-black leading-[1.05] tracking-tight text-ink sm:text-4xl lg:text-5xl">
                        <?php the_title(); ?>
                    </h1>
                    <?php if ($subtitle) : ?>
                        <p class="mt-4 max-w-2xl text-base leading-relaxed text-slate-700"><?php echo esc_html($subtitle); ?></p>
                    <?php endif; ?>
                </header>
            <?php endif; ?>

            <?php the_content(); ?>
        </article>
    <?php else : ?>
        <article <?php post_class('site-container max-w-3xl py-10'); ?>>
            <?php if (!$hide_title) : ?>
                <header class="mb-8">
                    <h1 class="text-3xl font-black leading-[1.05] tracking-tight text-ink sm:text-4xl">
                        <?php the_title(); ?>
                    </h1>
                    <?php if ($subtitle) : ?>
                        <p class="mt-4 text-lg text-slate-600"><?php echo esc_html($subtitle); ?></p>
                    <?php endif; ?>
                </header>
            <?php endif; ?>

            <div class="rich-text">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endif; ?>

    <?php
endwhile;

get_footer();
