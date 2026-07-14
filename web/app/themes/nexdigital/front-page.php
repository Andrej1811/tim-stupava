<?php
/**
 * Front page template.
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();
?>

<section class="py-16 text-center">
    <h1 class="mx-auto max-w-3xl text-5xl font-bold tracking-tight sm:text-6xl">
        <?php bloginfo('name'); ?>
    </h1>
    <p class="mx-auto mt-6 max-w-2xl text-lg text-neutral-600">
        <?php bloginfo('description'); ?>
    </p>
    <div class="mt-10 flex items-center justify-center gap-4">
        <a href="#" class="rounded-lg bg-brand-600 px-6 py-3 text-sm font-medium text-white transition hover:bg-brand-700">
            <?php esc_html_e('Get started', 'nexdigital'); ?>
        </a>
        <a href="#" class="text-sm font-medium text-neutral-700 hover:text-neutral-900">
            <?php esc_html_e('Learn more', 'nexdigital'); ?> &rarr;
        </a>
    </div>
</section>

<?php
// Front page also renders the static page content when one is assigned.
while (have_posts()) :
    the_post();
    if (trim(get_the_content()) !== '') :
        ?>
        <div class="prose mx-auto max-w-3xl">
            <?php the_content(); ?>
        </div>
        <?php
    endif;
endwhile;

get_footer();
