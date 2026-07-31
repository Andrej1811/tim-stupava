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
    <div class="<?php echo $done ? 'bg-white' : 'bg-sand-50'; ?> pb-16 sm:pb-20">
        <div class="site-container">
            <?php foreach ($groups as $slug => $group) : ?>
                <section class="pt-12 first:pt-4">
                    <h2 class="flex items-baseline gap-3 text-2xl font-black leading-tight tracking-tight text-ink sm:text-3xl">
                        <?php echo esc_html($group['name']); ?>
                        <span class="text-base font-bold text-slate-400">
                            <?php echo esc_html(number_format_i18n(count($group['posts']))); ?>
                        </span>
                    </h2>

                    <div class="mt-6 border-t-2 border-ink pt-2">
                        <?php foreach ($group['posts'] as $project) : ?>
                            <?php get_template_part('template-parts/project-card', null, [
                                'post_id' => $project->ID,
                                'variant' => 'compact',
                                'done'    => $done,
                            ]); ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
