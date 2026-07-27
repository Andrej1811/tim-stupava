<?php
/**
 * 404 template.
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();
?>

<section class="site-container max-w-2xl py-20 text-center">
    <p class="text-sm font-semibold uppercase tracking-wider text-brand-600">404</p>
    <h1 class="mt-2 text-4xl font-bold tracking-tight"><?php esc_html_e('Page not found', 'nexdigital'); ?></h1>
    <p class="mt-4 text-neutral-600"><?php esc_html_e('The page you are looking for does not exist.', 'nexdigital'); ?></p>
    <a href="<?php echo esc_url(home_url('/')); ?>" class="mt-8 inline-block rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-700">
        <?php esc_html_e('Back home', 'nexdigital'); ?>
    </a>
</section>

<?php get_footer(); ?>
