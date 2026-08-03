<?php
/**
 * Full project listing for the Program and Výsledky pages.
 *
 * The home page teases; this lists everything, grouped by oblasť. Grouping is
 * what makes twenty-nine entries readable — an ungrouped list of that length
 * reads as a wall, and the reader cannot tell whether the town did anything in
 * the area they actually care about.
 *
 * Expected args:
 *   done bool  true → finished projects (Výsledky), false → Program.
 *
 * @package NexDigital
 */

declare(strict_types=1);

use function NexDigital\Theme\PostTypes\projects;

$done = !empty($args['done']);
$query = projects($done);

/** @var array<string, array{name: string, posts: array<int, WP_Post>}> */
$groups = [];
$ungrouped = [];

foreach ($query->posts as $project) {
    $terms = get_the_terms($project->ID, 'oblast');

    if (!is_array($terms) || $terms === []) {
        $ungrouped[] = $project;

        continue;
    }

    // A project sits in one group even when tagged with several areas —
    // repeating it down the page would inflate the count the reader sees.
    $term = $terms[0];

    $groups[$term->slug] ??= ['name' => $term->name, 'posts' => []];
    $groups[$term->slug]['posts'][] = $project;
}

if ($ungrouped !== []) {
    $groups['_ostatne'] = ['name' => __('Ostatné', 'nexdigital'), 'posts' => $ungrouped];
}

// Largest group first: it is the strongest evidence in whichever direction the
// page argues, and it stops a two-item area from opening the page.
uasort($groups, static fn (array $a, array $b): int => count($b['posts']) <=> count($a['posts']));

$total = count($query->posts);

// Stage counts, in the order project_stages() defines — that order is the real
// sequence of a permit, so a filter bar sorted any other way would misinform.
// Only stages actually present get a chip: an empty filter is a dead end.
$stage_counts = [];

foreach ($query->posts as $project) {
    $key = (string) (get_post_meta($project->ID, 'ts_stav', true) ?: '');

    if ($key !== '') {
        $stage_counts[$key] = ($stage_counts[$key] ?? 0) + 1;
    }
}

$stages = [];

foreach (\NexDigital\Theme\PostTypes\project_stages() as $key => $label) {
    if (isset($stage_counts[$key])) {
        $stages[$key] = ['label' => $label, 'count' => $stage_counts[$key]];
    }
}

// On Výsledky every project is "Dokončené", so a stage filter would be one
// chip that changes nothing.
$show_stage_filter = !$done && count($stages) > 1;

/** One filter chip. Buttons, not links: this filters in place, it does not navigate. */
$chip = static function (string $group, string $value, string $label, int $count, bool $active = false): void {
    ?>
    <button
        type="button"
        class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-bold transition <?php echo $active
            ? 'border-brand-600 bg-brand-600 text-white'
            : 'border-slate-300 bg-white text-ink hover:border-slate-400 hover:bg-sand-100'; ?>"
        data-filter-group="<?php echo esc_attr($group); ?>"
        data-filter-value="<?php echo esc_attr($value); ?>"
        aria-pressed="<?php echo $active ? 'true' : 'false'; ?>"
    >
        <?php echo esc_html($label); ?>
        <span class="text-xs font-semibold opacity-60"><?php echo esc_html(number_format_i18n($count)); ?></span>
    </button>
    <?php
};
?>

<div class="bg-sand-50 py-12 sm:py-16">
    <div class="site-container">
        <h1 class="text-3xl font-black leading-[1.05] tracking-tight text-ink sm:text-4xl lg:text-5xl">
            <?php the_title(); ?>
        </h1>

        <?php if (trim(get_the_content()) !== '') : ?>
            <div class="rich-text mt-5 max-w-2xl">
                <?php the_content(); ?>
            </div>
        <?php endif; ?>

        <?php if ($total > 0) : ?>
            <p class="mt-5 text-sm font-semibold text-slate-600">
                <?php
                printf(
                    esc_html(
                        $done
                            /* translators: %s: number of finished projects */
                            ? _n('%s dokončený projekt', '%s dokončených projektov', $total, 'nexdigital')
                            /* translators: %s: number of projects in progress */
                            : _n('%s pripravovaný projekt', '%s pripravovaných projektov', $total, 'nexdigital')
                    ),
                    esc_html(number_format_i18n($total))
                );
                ?>
            </p>
        <?php endif; ?>
    </div>
</div>

<?php if ($total === 0) : ?>
    <div class="site-container py-16">
        <p class="text-slate-600">
            <?php
            echo $done
                ? esc_html__('Zatiaľ tu nie je žiadny dokončený projekt.', 'nexdigital')
                : esc_html__('Zatiaľ tu nie je žiadny pripravovaný projekt.', 'nexdigital');
            ?>
        </p>
    </div>
<?php else : ?>
    <div class="<?php echo $done ? 'bg-white' : 'bg-sand-50'; ?> pb-16 sm:pb-20" data-project-filter>
        <div class="site-container">

            <?php // hidden until the module claims it: without JavaScript the whole
                  // list is on the page anyway, and a filter bar that does nothing
                  // is worse than no filter bar. ?>
            <div class="border-b border-slate-200 pb-10 pt-10" data-filter-bar hidden>
                <div class="flex flex-col gap-6">
                    <div>
                        <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                            <?php esc_html_e('Oblasť', 'nexdigital'); ?>
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <?php $chip('oblast', '', __('Všetko', 'nexdigital'), $total, true); ?>
                            <?php foreach ($groups as $slug => $group) : ?>
                                <?php $chip('oblast', (string) $slug, $group['name'], count($group['posts'])); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if ($show_stage_filter) : ?>
                        <div>
                            <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                                <?php esc_html_e('Fáza', 'nexdigital'); ?>
                            </p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <?php $chip('stage', '', __('Všetky fázy', 'nexdigital'), $total, true); ?>
                                <?php foreach ($stages as $key => $stage) : ?>
                                    <?php $chip('stage', (string) $key, $stage['label'], $stage['count']); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php // aria-live so a screen reader hears the result of a filter it
                      // cannot see change. ?>
                <p class="mt-6 text-sm font-semibold text-slate-600 empty:mt-0" data-filter-status role="status" aria-live="polite"></p>
            </div>

            <?php foreach ($groups as $slug => $group) : ?>
                <section class="pt-12" data-filter-section data-oblast="<?php echo esc_attr((string) $slug); ?>">
                    <h2 class="flex items-baseline gap-3 text-2xl font-black leading-tight tracking-tight text-ink sm:text-3xl">
                        <?php echo esc_html($group['name']); ?>
                        <span class="text-base font-bold text-slate-400" data-filter-count>
                            <?php echo esc_html(number_format_i18n(count($group['posts']))); ?>
                        </span>
                    </h2>

                    <div class="mt-6 border-t-2 border-ink pt-2">
                        <?php foreach ($group['posts'] as $project) : ?>
                            <?php get_template_part('template-parts/project-card', null, [
                                'post_id' => $project->ID,
                                'variant' => 'compact',
                                'done'    => $done,
                                'oblast'  => (string) $slug,
                            ]); ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>

            <p class="hidden pt-12 text-slate-600" data-filter-empty>
                <?php esc_html_e('Tomuto výberu nezodpovedá žiadny projekt. Skúste inú oblasť alebo fázu.', 'nexdigital'); ?>
            </p>
        </div>
    </div>
<?php endif; ?>
