<?php
/**
 * Post card used in loops (index, archive).
 *
 * @package NexDigital
 */

declare(strict_types=1);
?>
<article <?php post_class('group flex flex-col overflow-hidden rounded-xl border border-neutral-200 transition hover:shadow-md'); ?>>
    <?php if (has_post_thumbnail()) : ?>
        <a href="<?php the_permalink(); ?>" class="block overflow-hidden">
            <?php the_post_thumbnail('medium_large', [
                'class' => 'aspect-video w-full object-cover transition duration-300 group-hover:scale-105',
            ]); ?>
        </a>
    <?php endif; ?>

    <div class="flex flex-1 flex-col p-5">
        <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">
            <?php echo esc_html(get_the_date()); ?>
        </p>
        <h2 class="mt-2 text-lg font-semibold leading-snug">
            <a href="<?php the_permalink(); ?>" class="hover:text-brand-600"><?php the_title(); ?></a>
        </h2>
        <p class="mt-2 line-clamp-3 text-sm text-neutral-600">
            <?php echo esc_html(get_the_excerpt()); ?>
        </p>
    </div>
</article>
