<?php
/**
 * Donate section.
 *
 * The IBAN is printed in full and large rather than hidden behind a button: a
 * transparent account only earns trust if the number is visible without
 * clicking, and half the donors will type it into their banking app by hand
 * anyway. The QR sits beside it for the other half.
 *
 * Expected args: heading, eyebrow, title, text, note,
 * uses array<int, array{amount: string, text: string}>.
 *
 * @package NexDigital
 */

declare(strict_types=1);

use function NexDigital\Theme\Fields\option;

$heading = ($args['heading'] ?? 'h2') === 'h1' ? 'h1' : 'h2';
$eyebrow = trim((string) ($args['eyebrow'] ?? ''));
$title = trim((string) ($args['title'] ?? ''));
$text = trim((string) ($args['text'] ?? ''));
$note = trim((string) ($args['note'] ?? ''));
$uses = is_array($args['uses'] ?? null) ? $args['uses'] : [];

$iban = trim((string) (option('opt_iban') ?? ''));
$account_url = trim((string) (option('opt_ucet_url') ?? ''));
$qr = (int) (option('opt_qr') ?? 0);
?>

<section class="bg-sand-50 py-16 sm:py-20 lg:py-24">
    <div class="site-container">

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

        <?php if ($iban !== '') : ?>
            <div class="mt-10 grid gap-8 rounded-lg bg-white p-6 ring-1 ring-slate-200 sm:p-8 lg:mt-12 lg:grid-cols-12 lg:items-center lg:gap-12">

                <div class="<?php echo $qr > 0 ? 'lg:col-span-8' : 'lg:col-span-12'; ?>">
                    <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                        <?php esc_html_e('Číslo transparentného účtu', 'nexdigital'); ?>
                    </p>

                    <?php // Monospace and selectable: the number is meant to be copied. ?>
                    <p class="mt-3 select-all font-mono text-xl font-bold leading-tight tracking-tight text-ink sm:text-2xl lg:text-3xl">
                        <?php echo esc_html($iban); ?>
                    </p>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <?php // Copy, not "pay": there is no payment gateway behind a
                              // transparent account, and a button that promised one
                              // would be a lie. The one page where magenta is the right
                              // primary button — this is the ask itself, not a link to it. ?>
                        <button
                            type="button"
                            class="btn btn-accent"
                            data-copy="<?php echo esc_attr(str_replace(' ', '', $iban)); ?>"
                            data-copy-done="<?php esc_attr_e('Skopírované', 'nexdigital'); ?>"
                        >
                            <?php esc_html_e('Skopírovať IBAN', 'nexdigital'); ?>
                        </button>

                        <?php if ($account_url !== '') : ?>
                            <a
                                href="<?php echo esc_url($account_url); ?>"
                                class="link-arrow text-brand-600 hover:text-brand-700"
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

                <?php if ($qr > 0) : ?>
                    <div class="lg:col-span-4">
                        <figure class="mx-auto max-w-[13rem] rounded-md bg-white p-3 ring-1 ring-slate-200">
                            <?php echo wp_get_attachment_image($qr, 'medium', false, [
                                'class' => 'w-full',
                                'alt'   => __('QR kód na platbu na transparentný účet', 'nexdigital'),
                            ]); ?>
                            <figcaption class="mt-2 text-center text-xs text-slate-600">
                                <?php esc_html_e('Naskenujte v mobilnej banke', 'nexdigital'); ?>
                            </figcaption>
                        </figure>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($uses !== []) : ?>
            <?php // What the money buys. Concrete amounts beat an appeal — a donor
                  // can see what their contribution turns into. ?>
            <dl class="mt-8 grid gap-px overflow-hidden rounded-lg bg-slate-200 sm:grid-cols-2 lg:grid-cols-<?php echo min(4, count($uses)); ?>">
                <?php foreach ($uses as $use) : ?>
                    <div class="bg-white p-6">
                        <dt class="text-2xl font-black leading-none tracking-tight text-brand-700 sm:text-3xl">
                            <?php echo esc_html($use['amount']); ?>
                        </dt>
                        <dd class="mt-3 text-sm leading-snug text-slate-700">
                            <?php echo esc_html($use['text']); ?>
                        </dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>

        <?php if ($note !== '') : ?>
            <p class="mt-8 max-w-3xl text-xs leading-relaxed text-slate-600">
                <?php echo esc_html($note); ?>
            </p>
        <?php endif; ?>
    </div>
</section>
