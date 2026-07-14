<?php
/**
 * Default fallback template (blog index / archives).
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();
?>

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
