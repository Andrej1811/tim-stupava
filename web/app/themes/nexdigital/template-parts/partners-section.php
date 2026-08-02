<?php
/**
 * Partners and endorsements.
 *
 * Logos sit in fixed white boxes with object-contain — party marks arrive in
 * wildly different shapes, and matching their heights would make a wordmark
 * look twice the size of a stacked lockup. Same reasoning as the footer strip.
 *
 * Expected args: eyebrow, title, text,
 * partners array<int, array{name,logo,url}>,
 * quotes array<int, array{quote,name,role,photo}>.
 *
 * @package NexDigital
 */

declare(strict_types=1);

$eyebrow = trim((string) ($args['eyebrow'] ?? ''));
$title = trim((string) ($args['title'] ?? ''));
$text = trim((string) ($args['text'] ?? ''));
$partners = is_array($args['partners'] ?? null) ? $args['partners'] : [];
$quotes = is_array($args['quotes'] ?? null) ? $args['quotes'] : [];

if ($partners === [] && $quotes === [] && $title === '') {
    return;
}
?>

<section class="bg-white py-16 sm:py-20 lg:py-24">
    <div class="site-container">

        <?php if ($eyebrow !== '' || $title !== '' || $text !== '') : ?>
            <div class="max-w-2xl">
                <?php if ($eyebrow !== '') : ?>
                    <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                        <?php echo esc_html($eyebrow); ?>
                    </p>
                <?php endif; ?>

                <?php if ($title !== '') : ?>
                    <h2 class="mt-4 text-3xl font-black leading-[1.05] tracking-tight text-ink sm:text-4xl">
                        <?php echo esc_html($title); ?>
                    </h2>
                <?php endif; ?>

                <?php if ($text !== '') : ?>
                    <p class="mt-5 text-base leading-relaxed text-slate-700">
                        <?php echo esc_html($text); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($partners !== []) : ?>
            <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:mt-12 lg:grid-cols-3">
                <?php foreach ($partners as $partner) : ?>
                    <li>
                        <?php
                        $tag = $partner['url'] !== '' ? 'a' : 'div';
                        $attrs = $partner['url'] !== ''
                            ? sprintf(' href="%s" target="_blank" rel="noopener noreferrer"', esc_url($partner['url']))
                            : '';
                        ?>
                        <<?php echo $tag . $attrs; // phpcs:ignore — tag and attrs built above. ?>
                            class="flex h-full items-center gap-5 rounded-lg bg-sand-50 p-5 transition hover:bg-sand-100"
                        >
                            <?php if ($partner['logo'] > 0) : ?>
                                <?php // Fixed box, object-contain: logos arrive in wildly
                                      // different proportions and a shared height would
                                      // make one look twice the size of another. ?>
                                <span class="flex h-16 w-24 shrink-0 items-center justify-center rounded-md bg-white p-2 ring-1 ring-slate-200">
                                    <?php echo wp_get_attachment_image($partner['logo'], 'medium', false, [
                                        'class' => 'max-h-full w-auto object-contain',
                                        'alt'   => $partner['name'],
                                    ]); ?>
                                </span>
                            <?php endif; ?>

                            <?php if ($partner['name'] !== '') : ?>
                                <span class="text-sm font-bold leading-snug text-ink">
                                    <?php echo esc_html($partner['name']); ?>
                                </span>
                            <?php endif; ?>
                        </<?php echo $tag; ?>>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($quotes !== []) : ?>
            <ul class="mt-12 grid gap-6 lg:grid-cols-2">
                <?php foreach ($quotes as $quote) : ?>
                    <li>
                        <figure class="flex h-full flex-col gap-5 rounded-lg border border-slate-200 p-6 sm:p-8">
                            <blockquote class="text-base leading-relaxed text-slate-700">
                                <?php echo esc_html($quote['quote']); ?>
                            </blockquote>

                            <figcaption class="mt-auto flex items-center gap-4">
                                <?php if ($quote['photo'] > 0) : ?>
                                    <?php echo wp_get_attachment_image($quote['photo'], 'thumbnail', false, [
                                        'class' => 'h-12 w-12 shrink-0 rounded-full object-cover',
                                        'alt'   => $quote['name'],
                                    ]); ?>
                                <?php endif; ?>

                                <span>
                                    <span class="block text-sm font-black text-ink">
                                        <?php echo esc_html($quote['name']); ?>
                                    </span>
                                    <?php if ($quote['role'] !== '') : ?>
                                        <span class="mt-0.5 block text-xs leading-snug text-slate-600">
                                            <?php echo esc_html($quote['role']); ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </figcaption>
                        </figure>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
