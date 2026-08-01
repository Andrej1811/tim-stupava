<?php
/**
 * Project card.
 *
 * `feature` is the wide card a hlavná téma gets, `compact` the row a smaller
 * city-wide item gets. Both lead with the permitting stage, because that is the
 * difference between a plan and a promise and it is what the whole content
 * model is built around.
 *
 * Expected args:
 *   post_id int
 *   variant string 'feature' | 'compact'
 *   done    bool   Rendered in the results context, where the stage badge would
 *                  only ever say "Dokončené" and adds nothing.
 *
 * @package NexDigital
 */

declare(strict_types=1);

use function NexDigital\Theme\Fields\field;
use function NexDigital\Theme\PostTypes\stage_label;

$post_id = (int) ($args['post_id'] ?? 0);

if ($post_id <= 0) {
    return;
}

$feature = ($args['variant'] ?? 'compact') === 'feature';
$done = !empty($args['done']);

$title = get_the_title($post_id);
$permalink = get_permalink($post_id);
$excerpt = trim((string) get_the_excerpt($post_id));
$stage = (string) (field('stav', $post_id) ?? '');
$stage_name = $stage !== '' ? stage_label($stage) : '';
$cost = trim((string) (field('cena', $post_id) ?? ''));
$term = trim((string) (field('termin', $post_id) ?? ''));
$source = trim((string) (field('zdroj', $post_id) ?? ''));

/** Facts under the text: only the ones actually filled in. */
$facts = array_filter([
    __('Rozpočet', 'nexdigital')   => $cost,
    __('Termín', 'nexdigital')     => $term,
    __('Financovanie', 'nexdigital') => $source,
]);
?>

<?php if ($feature) : ?>
    <article class="group relative flex flex-col overflow-hidden rounded-lg border border-slate-200 bg-white">
        <?php if (has_post_thumbnail($post_id)) : ?>
            <div class="aspect-video overflow-hidden bg-sand-100">
                <?php echo get_the_post_thumbnail($post_id, 'large', [
                    'class'    => 'h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]',
                    'alt'      => sprintf(__('Vizualizácia — %s', 'nexdigital'), $title),
                    'loading'  => 'lazy',
                    'decoding' => 'async',
                ]); ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-1 flex-col p-6 sm:p-7">
            <?php if (!$done && $stage_name !== '') : ?>
                <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-brand-600">
                    <?php echo esc_html($stage_name); ?>
                </p>
            <?php endif; ?>

            <h3 class="mt-3 text-xl font-black leading-tight tracking-tight text-ink sm:text-2xl">
                <a href="<?php echo esc_url($permalink); ?>" class="after:absolute after:inset-0 after:content-['']">
                    <?php echo esc_html($title); ?>
                </a>
            </h3>

            <?php if ($excerpt !== '') : ?>
                <p class="mt-3 text-sm leading-relaxed text-slate-700">
                    <?php echo esc_html($excerpt); ?>
                </p>
            <?php endif; ?>

            <?php if ($facts !== []) : ?>
                <dl class="mt-5 space-y-2 border-t border-slate-200 pt-4 text-xs">
                    <?php foreach ($facts as $label => $value) : ?>
                        <div class="flex gap-3">
                            <?php // w-32: "Financovanie" in uppercase with tracking is the
                                  // longest label and ran into its own value at w-24. ?>
                            <dt class="w-32 shrink-0 font-bold uppercase tracking-[0.08em] text-slate-500">
                                <?php echo esc_html($label); ?>
                            </dt>
                            <dd class="font-semibold text-ink"><?php echo esc_html($value); ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            <?php endif; ?>
        </div>
    </article>
<?php else : ?>
    <article class="group relative flex items-baseline gap-4 border-b border-slate-200 py-4">
        <?php if (!$done && $stage_name !== '') : ?>
            <?php // The stage sits in its own column so the eye can scan how far
                  // along the whole list is without reading every title. ?>
            <p class="hidden w-44 shrink-0 text-[0.5625rem] font-bold uppercase leading-tight tracking-[0.15em] text-brand-600 sm:block">
                <?php echo esc_html($stage_name); ?>
            </p>
        <?php endif; ?>

        <div class="min-w-0">
            <h3 class="text-base font-bold leading-snug text-ink transition group-hover:text-brand-700">
                <a href="<?php echo esc_url($permalink); ?>" class="after:absolute after:inset-0 after:content-['']">
                    <?php echo esc_html($title); ?>
                </a>
            </h3>

            <?php if (!$done && $stage_name !== '') : ?>
                <p class="mt-1 text-[0.5625rem] font-bold uppercase tracking-[0.15em] text-brand-600 sm:hidden">
                    <?php echo esc_html($stage_name); ?>
                </p>
            <?php endif; ?>

            <?php if ($excerpt !== '') : ?>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-600">
                    <?php echo esc_html($excerpt); ?>
                </p>
            <?php endif; ?>
        </div>
    </article>
<?php endif; ?>
