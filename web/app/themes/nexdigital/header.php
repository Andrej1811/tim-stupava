<?php
/**
 * Header template.
 *
 * @package NexDigital
 */

declare(strict_types=1);

use function NexDigital\Theme\Branding\logo;
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

        <a href="<?php echo esc_url(home_url('/')); ?>" class="flex shrink-0 items-center gap-2 sm:gap-2.5">
            <?php logo('h-11 w-auto sm:h-[3.25rem]'); ?>
            <span class="flex flex-col leading-none">
                <?php // mb-2 with leading-none on the wordmark reads tighter than the old mb-1.5
                      // did: text-lg carries its own 1.556 ratio, which survived the parent's
                      // leading-none (Tailwind registers --tw-leading as non-inheriting) and
                      // padded 10px of phantom leading between the two lines. ?>
                <span class="mb-2 text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-400">
                    <?php esc_html_e('Komunálne voľby 2026', 'nexdigital'); ?>
                </span>
                <span class="text-lg font-black leading-none tracking-tight text-ink sm:text-[1.4rem]">
                    <span class="text-brand-600"><?php esc_html_e('PRE', 'nexdigital'); ?></span> <?php esc_html_e('STUPAVU', 'nexdigital'); ?>
                </span>
            </span>
        </a>

        <nav class="site-nav ml-auto hidden lg:block" aria-label="<?php esc_attr_e('Hlavná navigácia', 'nexdigital'); ?>">
            <?php primary_menu(); ?>
        </nav>

        <a
            href="<?php echo esc_url(support_url()); ?>"
            class="btn btn-accent btn-sm ml-auto hidden shrink-0 sm:inline-flex lg:ml-0"
        >
            <?php esc_html_e('Podporte nás', 'nexdigital'); ?>
        </a>

        <button
            type="button"
            class="btn-icon -mr-2 ml-auto sm:ml-0 lg:hidden"
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

            <?php // Sized like the header's own CTA rather than stretched across the
                  // panel: btn-block made the one magenta element on the page into
                  // its largest block, which reads as an alert rather than an ask.
                  // Without it .btn's inline-flex shrinks the anchor to its label. ?>
            <a
                href="<?php echo esc_url(support_url()); ?>"
                class="btn btn-accent btn-sm mt-5 sm:hidden"
            >
                <?php esc_html_e('Podporte nás', 'nexdigital'); ?>
            </a>
        </nav>
    </div>
</header>

<main id="main">
