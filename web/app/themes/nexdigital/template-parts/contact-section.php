<?php
/**
 * Contact section — form beside the details.
 *
 * The form takes the wider column because writing is the action the page is
 * for; the details sit next to it for anyone who would rather phone. Every
 * detail comes from Nastavenia webu and an unfilled one removes its own row,
 * so the column never shows a heading over nothing.
 *
 * Expected args: eyebrow, title, text, form_title, submit, note, slug,
 * show_form bool, show_details bool.
 *
 * @package NexDigital
 */

declare(strict_types=1);

use function NexDigital\Theme\Fields\option;
use function NexDigital\Theme\Social\links as social_links;
use function NexDigital\Theme\Social\profiles as social_profiles;

$eyebrow = trim((string) ($args['eyebrow'] ?? ''));
$title = trim((string) ($args['title'] ?? ''));
$text = trim((string) ($args['text'] ?? ''));
$form_title = trim((string) ($args['form_title'] ?? ''));
$submit = trim((string) ($args['submit'] ?? ''));
$note = trim((string) ($args['note'] ?? ''));
$slug = trim((string) ($args['slug'] ?? ''));
$show_form = !empty($args['show_form']);
$show_details = !empty($args['show_details']);

// h1 when the page prints no title of its own, h2 otherwise — see the block's
// render callback.
$heading = ($args['heading'] ?? 'h2') === 'h1' ? 'h1' : 'h2';

$email = trim((string) (option('opt_email') ?? ''));
$phone = trim((string) (option('opt_telefon') ?? ''));
$address = trim((string) (option('opt_adresa') ?? ''));

/** Lucide paths, inline — the theme uses no icon font. */
$icons = [
    'mail'    => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
    'phone'   => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
    'map-pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
];

/** One detail row, or nothing when the option is empty. */
$detail = static function (string $icon, string $label, string $value, string $href = '') use ($icons): void {
    if ($value === '') {
        return;
    }
    ?>
    <li class="flex gap-4">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-white text-brand-600 ring-1 ring-slate-200">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <?php echo $icons[$icon]; // phpcs:ignore — literal SVG paths from the map above. ?>
            </svg>
        </span>

        <span class="min-w-0">
            <span class="block text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                <?php echo esc_html($label); ?>
            </span>

            <?php if ($href !== '') : ?>
                <a class="mt-1 block text-base font-bold leading-snug text-ink transition hover:text-brand-600" href="<?php echo esc_url($href); ?>">
                    <?php echo esc_html($value); ?>
                </a>
            <?php else : ?>
                <?php // leading-tight, not snug: the address is three short lines of
                      // one address, and looser spacing makes them read as three
                      // separate facts. ?>
                <span class="mt-1 block whitespace-pre-line text-base font-bold leading-tight text-ink">
                    <?php echo esc_html($value); ?>
                </span>
            <?php endif; ?>
        </span>
    </li>
    <?php
};

$has_details = $show_details && ($email !== '' || $phone !== '' || $address !== '');
?>

<section class="bg-sand-50 py-16 sm:py-20 lg:py-24">
    <div class="site-container">

        <?php if ($eyebrow !== '' || $title !== '' || $text !== '') : ?>
            <div class="max-w-2xl">
                <?php if ($eyebrow !== '') : ?>
                    <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                        <?php echo esc_html($eyebrow); ?>
                    </p>
                <?php endif; ?>

                <?php if ($title !== '') : ?>
                    <<?php echo $heading; ?> class="mt-4 text-3xl font-black leading-[1.05] tracking-tight text-ink sm:text-4xl lg:text-5xl">
                        <?php echo esc_html($title); ?>
                    </<?php echo $heading; ?>>
                <?php endif; ?>

                <?php if ($text !== '') : ?>
                    <p class="mt-5 text-base leading-relaxed text-slate-700">
                        <?php echo esc_html($text); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="mt-10 grid gap-10 lg:mt-14 lg:grid-cols-12 lg:gap-16">

            <?php if ($show_form) : ?>
                <div class="<?php echo $has_details ? 'lg:col-span-7' : 'lg:col-span-8'; ?>">
                    <div class="rounded-lg bg-white p-6 ring-1 ring-slate-200 sm:p-8">
                        <?php if ($form_title !== '') : ?>
                            <h3 class="mb-6 text-xl font-black leading-tight tracking-tight text-ink">
                                <?php echo esc_html($form_title); ?>
                            </h3>
                        <?php endif; ?>

                        <?php get_template_part('template-parts/form', null, [
                            'slug'   => $slug,
                            'submit' => $submit,
                        ]); ?>

                        <?php if ($note !== '') : ?>
                            <p class="mt-5 text-xs leading-relaxed text-slate-600">
                                <?php echo esc_html($note); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($has_details) : ?>
                <div class="<?php echo $show_form ? 'lg:col-span-5' : 'lg:col-span-8'; ?>">
                    <ul class="flex flex-col gap-6">
                        <?php
                        $detail('mail', __('E-mail', 'nexdigital'), $email, $email !== '' ? 'mailto:' . $email : '');
                        $detail('phone', __('Telefón', 'nexdigital'), $phone, $phone !== '' ? 'tel:' . preg_replace('/\s+/', '', $phone) : '');
                        $detail('map-pin', __('Adresa', 'nexdigital'), $address);
                        ?>
                    </ul>

                    <?php if (social_profiles() !== []) : ?>
                        <div class="mt-8 border-t border-slate-200 pt-6">
                            <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                                <?php esc_html_e('Sledujte nás', 'nexdigital'); ?>
                            </p>
                            <div class="mt-4 flex items-center gap-2">
                                <?php social_links('inline-flex h-11 w-11 items-center justify-center rounded-md bg-white text-brand-600 ring-1 ring-slate-200 transition hover:text-brand-700 hover:ring-slate-300'); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
