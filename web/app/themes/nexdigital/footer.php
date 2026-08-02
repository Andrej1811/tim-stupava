<?php
/**
 * Footer template.
 *
 * Three bands: the donate ask, the site's own map, and the legal line. The
 * donate band is the one place besides the header button where magenta is
 * spent — everything else here stays petrol and slate, so the ask keeps its
 * privilege.
 *
 * Everything except the navigation comes from Nastavenia webu, so a band with
 * nothing filled in simply does not render.
 *
 * @package NexDigital
 */

declare(strict_types=1);

use function NexDigital\Theme\Branding\agency_link;
use function NexDigital\Theme\Branding\logo;
use function NexDigital\Theme\Fields\option;
use function NexDigital\Theme\Nav\primary_items;
use function NexDigital\Theme\Nav\support_url;
use function NexDigital\Theme\Social\links as social_links;
use function NexDigital\Theme\Social\profiles as social_profiles;

$email = trim((string) (option('opt_email') ?? ''));
$phone = trim((string) (option('opt_telefon') ?? ''));
$address = trim((string) (option('opt_adresa') ?? ''));
$sponsor = trim((string) (option('opt_zadavatel') ?? ''));

$support_text = trim((string) (option('opt_podpora_text') ?? ''));
$iban = trim((string) (option('opt_iban') ?? ''));
$account_url = trim((string) (option('opt_ucet_url') ?? ''));

$partners = option('opt_partneri');
$partners = is_array($partners) ? $partners : [];
?>
</main>

<footer class="bg-brand-950 text-white">

    <?php if ($support_text !== '' || $iban !== '') : ?>
        <div class="border-b border-white/10">
            <div class="site-container grid gap-8 py-12 lg:grid-cols-12 lg:items-center lg:gap-12 lg:py-14">
                <div class="lg:col-span-7">
                    <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-teal-400">
                        <?php esc_html_e('Transparentný účet', 'nexdigital'); ?>
                    </p>
                    <h2 class="mt-3 text-2xl font-black leading-tight tracking-tight sm:text-3xl">
                        <?php esc_html_e('Kampaň platia ľudia zo Stupavy', 'nexdigital'); ?>
                    </h2>

                    <?php if ($support_text !== '') : ?>
                        <p class="mt-4 max-w-xl text-sm leading-relaxed text-slate-300">
                            <?php echo esc_html($support_text); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="lg:col-span-5">
                    <?php if ($iban !== '') : ?>
                        <?php // The IBAN is printed rather than hidden behind a button:
                              // a transparent account only builds trust if the number is
                              // visible without clicking anything. ?>
                        <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-400">
                            <?php esc_html_e('IBAN', 'nexdigital'); ?>
                        </p>
                        <p class="mt-2 font-mono text-base font-semibold tracking-tight text-white sm:text-lg">
                            <?php echo esc_html($iban); ?>
                        </p>
                    <?php endif; ?>

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <a
                            href="<?php echo esc_url(support_url()); ?>"
                            class="btn btn-accent"
                        >
                            <?php esc_html_e('Podporte nás', 'nexdigital'); ?>
                        </a>

                        <?php if ($account_url !== '') : ?>
                            <a
                                href="<?php echo esc_url($account_url); ?>"
                                class="link-arrow text-teal-400 hover:text-white"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <?php esc_html_e('Pozrieť pohyby na účte', 'nexdigital'); ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14M13 6l6 6-6 6" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="site-container grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-12 lg:gap-12">

        <div class="lg:col-span-4">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-2.5">
                <?php logo('h-12 w-auto'); ?>
                <span class="flex flex-col leading-none">
                    <span class="mb-2 text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-400">
                        <?php esc_html_e('Komunálne voľby 2026', 'nexdigital'); ?>
                    </span>
                    <span class="text-lg font-black leading-none tracking-tight text-white">
                        <span class="text-teal-400"><?php esc_html_e('PRE', 'nexdigital'); ?></span> <?php esc_html_e('STUPAVU', 'nexdigital'); ?>
                    </span>
                </span>
            </a>

            <p class="mt-5 max-w-xs text-sm leading-relaxed text-slate-300">
                <?php esc_html_e('Kandidáti na primátora a do mestského zastupiteľstva v Stupave.', 'nexdigital'); ?>
            </p>

            <?php if (social_profiles() !== []) : ?>
                <div class="mt-6 flex items-center gap-2">
                    <?php social_links('inline-flex h-10 w-10 items-center justify-center rounded-md border border-white/15 text-white transition hover:border-teal-400 hover:text-teal-400'); ?>
                </div>
            <?php endif; ?>
        </div>

        <nav class="lg:col-span-3" aria-label="<?php esc_attr_e('Pätička — navigácia', 'nexdigital'); ?>">
            <h2 class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-400">
                <?php esc_html_e('Web', 'nexdigital'); ?>
            </h2>
            <ul class="mt-5 space-y-3 text-sm font-semibold">
                <?php foreach (primary_items() as $slug => $label) : ?>
                    <li>
                        <a class="text-slate-200 transition hover:text-teal-400" href="<?php echo esc_url(home_url('/' . $slug . '/')); ?>">
                            <?php echo esc_html($label); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="lg:col-span-3">
            <h2 class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-400">
                <?php esc_html_e('Kontakt', 'nexdigital'); ?>
            </h2>
            <ul class="mt-5 space-y-3 text-sm text-slate-200">
                <?php if ($email !== '') : ?>
                    <li>
                        <a class="font-semibold transition hover:text-teal-400" href="mailto:<?php echo esc_attr($email); ?>">
                            <?php echo esc_html($email); ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($phone !== '') : ?>
                    <li>
                        <a class="font-semibold transition hover:text-teal-400" href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $phone)); ?>">
                            <?php echo esc_html($phone); ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($address !== '') : ?>
                    <li class="leading-relaxed text-slate-300">
                        <?php // Each line of the address field is its own line here. ?>
                        <?php echo nl2br(esc_html($address)); ?>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <?php if ($partners !== []) : ?>
            <div class="lg:col-span-2">
                <h2 class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-400">
                    <?php esc_html_e('Podporujú nás', 'nexdigital'); ?>
                </h2>

                <?php // Party marks keep their own colours, so they sit on white. ?>
                <ul class="mt-5 space-y-3">
                    <?php foreach ($partners as $partner) :
                        $logo_id = (int) ($partner['logo'] ?? 0);

                        if ($logo_id <= 0) {
                            continue;
                        }

                        $name = trim((string) ($partner['nazov'] ?? ''));
                        $url = trim((string) ($partner['url'] ?? ''));
                        // A fixed box with object-contain, because party logos arrive in
                        // wildly different shapes — a horizontal wordmark and a stacked
                        // lockup at the same height read as different sizes.
                        $image = wp_get_attachment_image($logo_id, 'medium', false, [
                            'class'    => 'max-h-full max-w-full object-contain',
                            'alt'      => $name,
                            'loading'  => 'lazy',
                            'decoding' => 'async',
                        ]);

                        $box = 'flex h-14 w-36 items-center justify-center rounded-md bg-white p-3';
                        ?>
                        <li>
                            <?php if ($url !== '') : ?>
                                <a class="<?php echo esc_attr($box); ?> transition hover:bg-sand-100" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo $image; // Escaped by wp_get_attachment_image(). ?>
                                </a>
                            <?php else : ?>
                                <span class="<?php echo esc_attr($box); ?>">
                                    <?php echo $image; ?>
                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <div class="border-t border-white/10">
        <div class="site-container py-6 text-xs text-slate-400">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; <?php echo esc_html((string) date('Y')); ?> <?php bloginfo('name'); ?></p>

                <?php
                // Cookie settings are not in this menu — inc/consent.php prints a
                // floating control on every page instead.
                wp_nav_menu([
                    'theme_location' => 'footer',
                    'container'      => false,
                    'menu_class'     => 'flex flex-wrap items-center gap-x-6 gap-y-2 [&_a]:transition [&_a:hover]:text-teal-400',
                    'fallback_cb'    => false,
                    'depth'          => 1,
                ]);
                ?>
            </div>

            <?php if ($sponsor !== '') : ?>
                <?php // Required by the campaign finance act — it has to be on the site,
                      // not only on printed material. It gets the full width of its own
                      // row: squeezed next to the menu it broke across three lines and
                      // read as a footnote rather than as a disclosure. ?>
                <p class="mt-4 leading-relaxed">
                    <?php echo agency_link($sponsor); // Escaped inside the helper. ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
