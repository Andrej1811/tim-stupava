<?php
/**
 * Header template.
 *
 * @package NexDigital
 */

declare(strict_types=1);

use function NexDigital\Theme\Nav\primary_menu;
use function NexDigital\Theme\Nav\support_url;
?>
<!doctype html>
<html <?php language_attributes(); ?> class="scroll-smooth">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('min-h-screen bg-white text-ink antialiased'); ?>>
<?php wp_body_open(); ?>

<a class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[60] focus:rounded focus:bg-brand-600 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white" href="#main">
    <?php esc_html_e('Preskočiť na obsah', 'nexdigital'); ?>
</a>

<header
    class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/80"
    data-site-header
>
    <div class="site-container flex h-header items-center gap-6">

        <a href="<?php echo esc_url(home_url('/')); ?>" class="flex shrink-0 flex-col leading-none">
            <span class="mb-1.5 text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-400">
                <?php esc_html_e('Komunálne voľby 2026', 'nexdigital'); ?>
            </span>
            <span class="text-xl font-black tracking-tight text-ink sm:text-[1.4rem]">
                <span class="text-brand-600"><?php esc_html_e('TÍM', 'nexdigital'); ?></span> <?php esc_html_e('STUPAVA', 'nexdigital'); ?>
            </span>
        </a>

        <nav class="site-nav ml-auto hidden lg:block" aria-label="<?php esc_attr_e('Hlavná navigácia', 'nexdigital'); ?>">
            <?php primary_menu(); ?>
        </nav>

        <a
            href="<?php echo esc_url(support_url()); ?>"
            class="ml-auto hidden shrink-0 rounded-md bg-accent-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-accent-700 sm:inline-flex lg:ml-0"
        >
            <?php esc_html_e('Podporte nás', 'nexdigital'); ?>
        </a>

        <button
            type="button"
            class="-mr-2 ml-auto inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md text-ink transition hover:bg-slate-100 sm:ml-0 lg:hidden"
            aria-controls="site-menu"
            aria-expanded="false"
            data-menu-toggle
        >
            <span class="sr-only"><?php esc_html_e('Otvoriť menu', 'nexdigital'); ?></span>
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" data-menu-icon-open>
                <path d="M4 7h16M4 12h16M4 17h16" />
            </svg>
            <svg class="hidden h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" data-menu-icon-close>
                <path d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>
    </div>

    <div class="hidden border-t border-slate-200 bg-white lg:hidden" id="site-menu" data-menu-panel>
        <nav class="site-container site-nav py-4" aria-label="<?php esc_attr_e('Hlavná navigácia — mobil', 'nexdigital'); ?>">
            <?php primary_menu(); ?>

            <a
                href="<?php echo esc_url(support_url()); ?>"
                class="mt-4 inline-flex w-full items-center justify-center rounded-md bg-accent-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-accent-700 sm:hidden"
            >
                <?php esc_html_e('Podporte nás', 'nexdigital'); ?>
            </a>
        </nav>
    </div>
</header>

<main id="main">
