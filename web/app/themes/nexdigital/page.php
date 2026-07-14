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
    $hide_title = get_post_meta(get_the_ID(), 'nexdigital_hide_title', true);
    $subtitle   = get_post_meta(get_the_ID(), 'nexdigital_subtitle', true);
    ?>
    <article <?php post_class('mx-auto max-w-3xl'); ?>>
        <?php if (!$hide_title) : ?>
            <header class="mb-8">
                <h1 class="text-4xl font-bold tracking-tight"><?php the_title(); ?></h1>
                <?php if ($subtitle) : ?>
                    <p class="mt-2 text-lg text-neutral-600"><?php echo esc_html($subtitle); ?></p>
                <?php endif; ?>
            </header>
        <?php endif; ?>

        <div class="prose max-w-none">
            <?php the_content(); ?>
        </div>
    </article>
    <?php
endwhile;

get_footer();
