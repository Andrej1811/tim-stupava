<?php
/**
 * Archive template.
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();
?>

<header class="mb-8">
    <h1 class="text-3xl font-bold tracking-tight"><?php the_archive_title(); ?></h1>
    <?php the_archive_description('<div class="mt-2 text-neutral-600">', '</div>'); ?>
</header>

<?php if (have_posts()) : ?>
    <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
        <?php
        while (have_posts()) :
            the_post();
            get_template_part('template-parts/content', get_post_type());
        endwhile;
        ?>
    </div>

    <div class="mt-10">
        <?php the_posts_pagination(['mid_size' => 1]); ?>
    </div>
<?php else : ?>
    <p class="text-neutral-600"><?php esc_html_e('Nothing found.', 'nexdigital'); ?></p>
<?php endif; ?>

<?php get_footer(); ?>
