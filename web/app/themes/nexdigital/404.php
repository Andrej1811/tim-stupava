<?php
/**
 * 404 template.
 *
 * Same grammar as every header band on the site — sand surface, eyebrow,
 * font-black heading — so a dead link still lands somewhere that is
 * unmistakably this site. The two buttons cover the two likely intents:
 * start over, or see what is actually here.
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();

$novinky_id = (int) get_option('page_for_posts');
$novinky_url = $novinky_id > 0 ? (string) get_permalink($novinky_id) : '';
?>

<section class="flex min-h-[60vh] items-center bg-sand-50">
    <div class="site-container max-w-2xl py-20 text-center sm:py-28">
        <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
            <?php esc_html_e('Chyba 404', 'nexdigital'); ?>
        </p>

        <h1 class="mt-4 text-3xl font-black leading-[1.05] tracking-tight text-ink sm:text-4xl lg:text-5xl">
            <?php esc_html_e('Táto stránka sa nenašla', 'nexdigital'); ?>
        </h1>

        <p class="mx-auto mt-5 max-w-md text-base leading-relaxed text-slate-600">
            <?php esc_html_e('Stránka, ktorú hľadáte, neexistuje alebo bola presunutá. Skúste to z úvodnej stránky.', 'nexdigital'); ?>
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">
                <?php esc_html_e('Späť na úvod', 'nexdigital'); ?>
            </a>

            <?php if ($novinky_url !== '') : ?>
                <a href="<?php echo esc_url($novinky_url); ?>" class="btn btn-outline">
                    <?php esc_html_e('Prejsť na novinky', 'nexdigital'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
