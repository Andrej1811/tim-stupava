<?php
/**
 * Single post template.
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();

while (have_posts()) :
    the_post();
    ?>
    <article <?php post_class('mx-auto max-w-3xl'); ?>>
        <header class="mb-8">
            <h1 class="text-4xl font-bold tracking-tight"><?php the_title(); ?></h1>
            <p class="mt-2 text-sm text-neutral-500">
                <?php echo esc_html(get_the_date()); ?>
            </p>
        </header>

        <?php if (has_post_thumbnail()) : ?>
            <figure class="mb-8 overflow-hidden rounded-xl">
                <?php the_post_thumbnail('large', ['class' => 'w-full']); ?>
            </figure>
        <?php endif; ?>

        <div class="prose max-w-none">
            <?php the_content(); ?>
        </div>
    </article>
    <?php
endwhile;

get_footer();
