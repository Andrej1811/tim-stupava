<?php
/**
 * Projects section — Program or Výsledky.
 *
 * The same records at two stages of the same pipeline, so one layout with a
 * mode rather than two templates that drift apart. Program leads with the big
 * themes and lists the rest; Výsledky leads with the numbers, because a record
 * is judged on totals first.
 *
 * Expected args: eyebrow, title, text, mode ('program'|'vysledky'),
 * features WP_Post[], rest WP_Post[], numbers array<int, array{value,label}>,
 * hidden int (how many were cut from the list), link array{url,title,target}|null.
 *
 * @package NexDigital
 */

declare(strict_types=1);

$eyebrow = trim((string) ($args['eyebrow'] ?? ''));
$title = trim((string) ($args['title'] ?? ''));
$text = trim((string) ($args['text'] ?? ''));
$done = ($args['mode'] ?? 'program') === 'vysledky';
$features = is_array($args['features'] ?? null) ? $args['features'] : [];
$rest = is_array($args['rest'] ?? null) ? $args['rest'] : [];
$numbers = is_array($args['numbers'] ?? null) ? $args['numbers'] : [];
$hidden = max(0, (int) ($args['hidden'] ?? 0));
$link = is_array($args['link'] ?? null) ? $args['link'] : null;

$link_url = trim((string) ($link['url'] ?? ''));
$link_label = trim((string) ($link['title'] ?? ''));
?>

<?php // Backgrounds alternate down the page: the candidates section above is
      // sand, so results land on white and the programme goes back to sand.
      // Class strings stay literal — Tailwind cannot see a name built at runtime. ?>
<section class="<?php echo $done ? 'bg-white' : 'bg-sand-50'; ?> py-16 sm:py-20 lg:py-24">
    <div class="site-container">

        <div class="max-w-2xl">
            <?php if ($eyebrow !== '') : ?>
                <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                    <?php echo esc_html($eyebrow); ?>
                </p>
            <?php endif; ?>

            <?php if ($title !== '') : ?>
                <h2 class="mt-4 text-3xl font-black leading-[1.05] tracking-tight text-ink sm:text-4xl lg:text-5xl">
                    <?php echo esc_html($title); ?>
                </h2>
            <?php endif; ?>

            <?php if ($text !== '') : ?>
                <p class="mt-5 text-base leading-relaxed text-slate-700">
                    <?php echo esc_html($text); ?>
                </p>
            <?php endif; ?>
        </div>

        <?php if ($numbers !== []) : ?>
            <?php // Numbers band: the summary of a record, above the individual items. ?>
            <?php
            $number_cols = match (count($numbers)) {
                1, 2    => 'sm:grid-cols-2',
                3       => 'sm:grid-cols-2 lg:grid-cols-3',
                default => 'sm:grid-cols-2 lg:grid-cols-4',
            };
            ?>
            <dl class="mt-10 grid gap-px overflow-hidden rounded-lg bg-slate-200 lg:mt-12 <?php echo esc_attr($number_cols); ?>">
                <?php foreach ($numbers as $number) : ?>
                    <div class="bg-sand-50 p-6">
                        <dt class="text-3xl font-black leading-none tracking-tight text-brand-700 sm:text-4xl">
                            <?php echo esc_html((string) ($number['value'] ?? '')); ?>
                        </dt>
                        <dd class="mt-3 text-sm leading-snug text-slate-700">
                            <?php echo esc_html((string) ($number['label'] ?? '')); ?>
                        </dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>

        <?php if ($features !== []) : ?>
            <div class="mt-10 grid gap-6 lg:mt-12 <?php echo count($features) === 1 ? '' : 'lg:grid-cols-2'; ?>">
                <?php foreach ($features as $project) : ?>
                    <?php get_template_part('template-parts/project-card', null, [
                        'post_id' => $project->ID,
                        'variant' => 'feature',
                        'done'    => $done,
                    ]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($rest !== []) : ?>
            <div class="mt-12 border-t-2 border-ink pt-2">
                <?php foreach ($rest as $project) : ?>
                    <?php get_template_part('template-parts/project-card', null, [
                        'post_id' => $project->ID,
                        'variant' => 'compact',
                        'done'    => $done,
                    ]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php // Say what was cut. A truncated list with no note reads as the whole
              // record, which undersells four years of work. ?>
        <?php if ($hidden > 0) : ?>
            <p class="mt-6 text-sm font-semibold text-slate-600">
                <?php
                printf(
                    esc_html(
                        /* translators: %s: number of further projects */
                        _n('… a ďalší %s projekt', '… a ďalších %s projektov', $hidden, 'nexdigital')
                    ),
                    esc_html(number_format_i18n($hidden))
                );
                ?>
            </p>
        <?php endif; ?>

        <?php if ($link_url !== '' && $link_label !== '') : ?>
            <div class="mt-10 flex justify-center">
                <a
                    href="<?php echo esc_url($link_url); ?>"
                    <?php echo ($link['target'] ?? '') !== '' ? 'target="' . esc_attr($link['target']) . '" rel="noopener"' : ''; ?>
                    class="inline-flex items-center justify-center rounded-md bg-brand-600 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-brand-700"
                >
                    <?php echo esc_html($link_label); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
