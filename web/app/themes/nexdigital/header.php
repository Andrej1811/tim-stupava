<?php
/**
 * Header template.
 *
 * @package NexDigital
 */

declare(strict_types=1);
?>
<!doctype html>
<html <?php language_attributes(); ?> class="scroll-smooth">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('min-h-screen bg-white text-neutral-900 antialiased'); ?>>
<?php wp_body_open(); ?>

<a class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:rounded focus:bg-brand-600 focus:px-4 focus:py-2 focus:text-white" href="#main">
    <?php esc_html_e('Skip to content', 'nexdigital'); ?>
</a>

<header class="border-b border-neutral-200">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="text-lg font-semibold tracking-tight">
            <?php bloginfo('name'); ?>
        </a>

        <nav aria-label="<?php esc_attr_e('Primary', 'nexdigital'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'flex items-center gap-6 text-sm font-medium',
                'fallback_cb'    => false,
                'depth'          => 1,
            ]);
            ?>
        </nav>
    </div>
</header>

<main id="main" class="mx-auto max-w-7xl px-4 py-10">
