<?php
/**
 * Archive template — a rubrika or date slice of the Novinky.
 *
 * Same header band and grid as home.php, minus the lead story: an archive is
 * a filter, not a front page, so every post gets the same card.
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();
?>

<header class="bg-sand-50 py-12 sm:py-16">
    <div class="site-container">
        <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
            <?php echo esc_html(is_category() ? __('Rubrika noviniek', 'nexdigital') : __('Archív noviniek', 'nexdigital')); ?>
        </p>
        <h1 class="mt-3 text-3xl font-black leading-[1.05] tracking-tight text-ink sm:text-4xl lg:text-5xl">
            <?php the_archive_title(); ?>
        </h1>
        <?php the_archive_description('<div class="mt-5 max-w-2xl text-base leading-relaxed text-slate-600">', '</div>'); ?>
    </div>
</header>

<div class="site-container py-12 sm:py-16">
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
    <p class="text-slate-600"><?php esc_html_e('V tomto výbere zatiaľ nič nie je.', 'nexdigital'); ?></p>
<?php endif; ?>
</div>

<?php get_footer(); ?>
