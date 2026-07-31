<?php
/**
 * Project detail.
 *
 * The cards across the site all link here, and until now it fell through to
 * single.php — a post layout that shows a publish date and drops the gallery on
 * the floor. The stage leads, because the difference between a plan and a
 * promise is the whole point of the content model.
 *
 * @package NexDigital
 */

declare(strict_types=1);

use function NexDigital\Theme\Fields\field;
use function NexDigital\Theme\PostTypes\stage_label;
use const NexDigital\Theme\PostTypes\STAGE_DONE;

get_header();

while (have_posts()) :
    the_post();

    $post_id = get_the_ID();
    $title = get_the_title();

    $stage = (string) (field('stav', $post_id) ?? '');
    $stage_name = $stage !== '' ? stage_label($stage) : '';
    $done = $stage === STAGE_DONE;

    $facts = array_filter([
        __('Rozpočet', 'nexdigital')     => trim((string) (field('cena', $post_id) ?? '')),
        __('Termín', 'nexdigital')       => trim((string) (field('termin', $post_id) ?? '')),
        __('Financovanie', 'nexdigital') => trim((string) (field('zdroj', $post_id) ?? '')),
    ]);

    $gallery = (array) (field('galeria', $post_id) ?? []);

    // Back to whichever listing this project belongs to, not to a generic
    // archive — a finished project sits under Výsledky, the rest under Program.
    $parent = get_page_by_path($done ? 'vysledky' : 'program');
    $parent_url = $parent instanceof WP_Post ? get_permalink($parent) : '';
    ?>

    <?php if ($parent_url !== '') : ?>
        <nav class="bg-sand-50" aria-label="<?php esc_attr_e('Omrvinky', 'nexdigital'); ?>">
            <div class="site-container py-4">
                <a
                    href="<?php echo esc_url($parent_url); ?>"
                    class="group inline-flex items-center gap-2 text-sm font-bold text-slate-600 transition hover:text-brand-600"
                >
                    <svg class="h-4 w-4 transition group-hover:-translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 12H5M11 18l-6-6 6-6" />
                    </svg>
                    <?php echo esc_html($done ? __('Späť na výsledky', 'nexdigital') : __('Späť na program', 'nexdigital')); ?>
                </a>
            </div>
        </nav>
    <?php endif; ?>

    <article <?php post_class(); ?>>
        <header class="bg-sand-50 pb-12 pt-8 sm:pb-16">
            <div class="site-container max-w-3xl">
                <?php if ($stage_name !== '') : ?>
                    <p>
                        <span class="inline-flex items-center rounded-full <?php echo $done ? 'bg-brand-600 text-white' : 'bg-white text-brand-700 ring-1 ring-slate-200'; ?> px-3 py-1.5 text-[0.6875rem] font-bold uppercase tracking-[0.12em]">
                            <?php echo esc_html($stage_name); ?>
                        </span>
                    </p>
                <?php endif; ?>

                <h1 class="mt-5 text-3xl font-black leading-[1.05] tracking-tight text-ink sm:text-4xl lg:text-5xl">
                    <?php echo esc_html($title); ?>
                </h1>

                <?php if ($facts !== []) : ?>
                    <dl class="mt-8 grid gap-px overflow-hidden rounded-lg bg-slate-200 sm:grid-cols-<?php echo count($facts); ?>">
                        <?php foreach ($facts as $label => $value) : ?>
                            <div class="bg-white p-5">
                                <dt class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                                    <?php echo esc_html($label); ?>
                                </dt>
                                <dd class="mt-2 text-base font-bold leading-snug text-ink">
                                    <?php echo esc_html($value); ?>
                                </dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>
            </div>
        </header>

        <?php if (has_post_thumbnail()) : ?>
            <div class="site-container max-w-4xl -mt-6 sm:-mt-10">
                <figure class="overflow-hidden rounded-lg bg-sand-100">
                    <?php the_post_thumbnail('large', [
                        'class' => 'aspect-video w-full object-cover',
                        'alt'   => $title,
                    ]); ?>
                </figure>
            </div>
        <?php endif; ?>

        <?php if (trim(get_the_content()) !== '') : ?>
            <div class="site-container max-w-3xl py-12 sm:py-16">
                <div class="rich-text">
                    <?php the_content(); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($gallery !== []) : ?>
            <div class="bg-sand-50 py-12 sm:py-16">
                <div class="site-container">
                    <h2 class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                        <?php echo esc_html($done ? __('Fotografie', 'nexdigital') : __('Vizualizácie', 'nexdigital')); ?>
                    </h2>

                    <?php // Real links to the full-size file: without JavaScript the
                          // gallery is still a gallery, it just opens the photograph
                          // instead of a dialog. ?>
                    <ul class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-lightbox>
                        <?php foreach ($gallery as $index => $image_id) : ?>
                            <?php
                            $image_id = (int) $image_id;
                            $full = wp_get_attachment_image_url($image_id, 'full');

                            if ($full === false) {
                                continue;
                            }

                            $caption = trim((string) wp_get_attachment_caption($image_id));
                            ?>
                            <li>
                                <a
                                    href="<?php echo esc_url($full); ?>"
                                    class="group block overflow-hidden rounded-lg bg-white"
                                    data-lightbox-item
                                    data-lightbox-caption="<?php echo esc_attr($caption); ?>"
                                >
                                    <span class="sr-only">
                                        <?php printf(
                                            esc_html__('Zväčšiť fotografiu %d', 'nexdigital'),
                                            (int) $index + 1
                                        ); ?>
                                    </span>
                                    <?php echo wp_get_attachment_image($image_id, 'large', false, [
                                        'class'    => 'aspect-video w-full object-cover transition duration-300 group-hover:scale-[1.03]',
                                        'alt'      => $caption !== '' ? $caption : $title,
                                        'loading'  => 'lazy',
                                        'decoding' => 'async',
                                    ]); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </article>

    <?php
endwhile;

get_footer();
