<?php
/**
 * Footer template.
 *
 * @package NexDigital
 */

declare(strict_types=1);
?>
</main>

<footer class="mt-16 border-t border-neutral-200">
    <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 text-sm text-neutral-600 sm:flex-row sm:items-center sm:justify-between">
        <p>&copy; <?php echo esc_html((string) date('Y')); ?> <?php bloginfo('name'); ?></p>

        <?php
        wp_nav_menu([
            'theme_location' => 'footer',
            'container'      => false,
            'menu_class'     => 'flex items-center gap-6',
            'fallback_cb'    => false,
            'depth'          => 1,
        ]);
        ?>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
