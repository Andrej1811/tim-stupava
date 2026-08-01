<?php
/**
 * "How to vote" section.
 *
 * Practical information, not persuasion: where to go, what to bring, how the
 * ballot works. For an incumbent whose base already agrees, turnout is the
 * whole game, and this is the only section that helps somebody actually vote.
 *
 * Expected args: eyebrow, title, date, time, note, items array<int,
 * array{icon,title,text}>, link array{url,title,target}|null.
 *
 * @package NexDigital
 */

declare(strict_types=1);

$eyebrow = trim((string) ($args['eyebrow'] ?? ''));
$title = trim((string) ($args['title'] ?? ''));
$date = trim((string) ($args['date'] ?? ''));
$time = trim((string) ($args['time'] ?? ''));
$note = trim((string) ($args['note'] ?? ''));
$items = is_array($args['items'] ?? null) ? $args['items'] : [];
$link = is_array($args['link'] ?? null) ? $args['link'] : null;

$link_url = trim((string) ($link['url'] ?? ''));
$link_label = trim((string) ($link['title'] ?? ''));

/** Lucide paths, inline — the theme uses no icon font. */
$icons = [
    'calendar' => '<rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
    'clock'    => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
    'map-pin'  => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
    'id-card'  => '<rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20M7 15h3"/><circle cx="15.5" cy="12.5" r="1.5"/>',
    'pencil'   => '<path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/>',
    'info'     => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
];
?>

<section class="bg-brand-950 py-16 text-white sm:py-20 lg:py-24">
    <div class="site-container grid gap-12 lg:grid-cols-12 lg:gap-16">

        <div class="lg:col-span-5">
            <?php if ($eyebrow !== '') : ?>
                <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-teal-400">
                    <?php echo esc_html($eyebrow); ?>
                </p>
            <?php endif; ?>

            <?php if ($title !== '') : ?>
                <h2 class="mt-4 text-3xl font-black leading-[1.05] tracking-tight sm:text-4xl">
                    <?php echo esc_html($title); ?>
                </h2>
            <?php endif; ?>

            <?php if ($date !== '' || $time !== '') : ?>
                <?php // The date is the one fact somebody comes here for, so it gets
                      // the largest type on a dark surface instead of a line of body copy. ?>
                <div class="mt-8 border-l-2 border-teal-400 pl-5">
                    <?php if ($date !== '') : ?>
                        <p class="text-2xl font-black leading-tight tracking-tight sm:text-3xl">
                            <?php echo esc_html($date); ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($time !== '') : ?>
                        <p class="mt-2 text-base font-semibold text-teal-400">
                            <?php echo esc_html($time); ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($note !== '') : ?>
                <p class="mt-8 max-w-md text-sm leading-relaxed text-slate-300">
                    <?php echo esc_html($note); ?>
                </p>
            <?php endif; ?>

            <?php if ($link_url !== '' && $link_label !== '') : ?>
                <p class="mt-8">
                    <a
                        href="<?php echo esc_url($link_url); ?>"
                        <?php echo ($link['target'] ?? '') !== '' ? 'target="' . esc_attr($link['target']) . '" rel="noopener"' : ''; ?>
                        class="btn btn-ghost"
                    >
                        <?php echo esc_html($link_label); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>

        <?php if ($items !== []) : ?>
            <ul class="grid gap-px self-start overflow-hidden rounded-lg bg-white/10 sm:grid-cols-2 lg:col-span-7">
                <?php foreach ($items as $item) :
                    $icon = $icons[$item['icon'] ?? 'info'] ?? $icons['info'];
                    ?>
                    <li class="bg-brand-950 p-6">
                        <svg class="h-6 w-6 text-teal-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <?php echo $icon; // phpcs:ignore — literal SVG paths from the map above. ?>
                        </svg>

                        <h3 class="mt-4 text-base font-black leading-tight">
                            <?php echo esc_html((string) ($item['title'] ?? '')); ?>
                        </h3>

                        <p class="mt-2 text-sm leading-relaxed text-slate-300">
                            <?php echo esc_html((string) ($item['text'] ?? '')); ?>
                        </p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
