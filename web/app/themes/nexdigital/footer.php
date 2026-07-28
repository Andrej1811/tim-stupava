<?php
/**
 * Footer template.
 *
 * @package NexDigital
 */

declare(strict_types=1);

use function NexDigital\Theme\Consent\preferences_button;
?>
</main>

<footer class="mt-16 border-t border-slate-200">
    <div class="site-container flex flex-col gap-4 py-8 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
        <p>&copy; <?php echo esc_html((string) date('Y')); ?> <?php bloginfo('name'); ?></p>

        <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
            <?php
            wp_nav_menu([
                'theme_location' => 'footer',
                'container'      => false,
                'menu_class'     => 'flex flex-wrap items-center gap-x-6 gap-y-2',
                'fallback_cb'    => false,
                'depth'          => 1,
            ]);

            // Withdrawing consent has to be as easy as giving it, so this sits
            // in the footer of every page rather than inside the policy page.
            preferences_button('underline underline-offset-4 transition hover:text-ink');
            ?>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
