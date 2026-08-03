<?php
/**
 * Default fallback template (blog index / archives).
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();
?>

<div class="site-container py-10">
<?php if (have_posts()) : ?>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php
        while (have_posts()) :
            the_post();
            get_template_part('template-parts/content', get_post_type());
        endwhile;
        ?>
    </div>

    <div class="mt-12">
        <?php the_posts_pagination([
            'mid_size'  => 1,
            'prev_text' => __('Novšie', 'nexdigital'),
            'next_text' => __('Staršie', 'nexdigital'),
        ]); ?>
    </div>
<?php else : ?>
    <p class="text-slate-600"><?php esc_html_e('Nothing found.', 'nexdigital'); ?></p>
<?php endif; ?>
</div>

<?php get_footer(); ?>
