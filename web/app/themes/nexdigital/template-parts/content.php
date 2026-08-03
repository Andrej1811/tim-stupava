<?php
/**
 * Post card used in loops (Novinky index, archives, the related strip).
 *
 * The rubrika pill deliberately rhymes with the stage pill on project cards —
 * novinky mostly report on projects, and the two kinds of card should read as
 * one site. A post without a photograph keeps its card: the image gives way to
 * a petrol top rule instead of an empty grey box.
 *
 * @package NexDigital
 */

declare(strict_types=1);

$category = get_the_category()[0] ?? null;
$has_thumbnail = has_post_thumbnail();
?>
<article <?php post_class(
    'group flex flex-col overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:shadow-md'
    . ($has_thumbnail ? '' : ' border-t-4 border-t-brand-600')
); ?>>
    <?php if ($has_thumbnail) : ?>
        <?php // The title link below already announces the destination — the
              // image repeats it, so it leaves the tab order. ?>
        <a href="<?php the_permalink(); ?>" class="block overflow-hidden bg-sand-100" tabindex="-1" aria-hidden="true">
            <?php // object-top, not the default centre: a portrait forced into a
                  // 16:9 box loses more than half its height, and cropping from
                  // the centre takes the face with it. ?>
            <?php the_post_thumbnail('medium_large', [
                'class' => 'aspect-video w-full object-cover object-top transition duration-300 group-hover:scale-[1.03]',
            ]); ?>
        </a>
    <?php endif; ?>

    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <p class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
            <?php if ($category instanceof WP_Term) : ?>
                <a
                    href="<?php echo esc_url(get_category_link($category)); ?>"
                    class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-[0.625rem] font-bold uppercase tracking-[0.12em] text-brand-700 transition hover:bg-brand-100"
                >
                    <?php echo esc_html($category->name); ?>
                </a>
            <?php endif; ?>
            <time class="text-xs font-medium text-slate-500" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                <?php echo esc_html(get_the_date()); ?>
            </time>
        </p>

        <h2 class="mt-3 text-lg font-black leading-snug tracking-tight">
            <a href="<?php the_permalink(); ?>" class="text-ink transition hover:text-brand-600">
                <?php the_title(); ?>
            </a>
        </h2>

        <p class="mt-2.5 line-clamp-3 text-sm leading-relaxed text-slate-600">
            <?php echo esc_html(get_the_excerpt()); ?>
        </p>
    </div>
</article>
