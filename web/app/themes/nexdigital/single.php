<?php
/**
 * Single post — a novinka.
 *
 * Same skeleton as the project detail: sand header, photograph pulled up out
 * of it, a reading column. The date leads the meta line because a news item
 * that hides when it was written is not news; the closing strip keeps the
 * reader inside the record instead of dead-ending the page.
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();

while (have_posts()) :
    the_post();

    $post_id = get_the_ID();
    $title = get_the_title();
    $category = get_the_category()[0] ?? null;

    $novinky_id = (int) get_option('page_for_posts');
    $novinky_url = $novinky_id > 0 ? (string) get_permalink($novinky_id) : '';
    ?>

    <?php if ($novinky_url !== '') : ?>
        <nav class="bg-sand-50" aria-label="<?php esc_attr_e('Omrvinky', 'nexdigital'); ?>">
            <div class="site-container py-4">
                <a
                    href="<?php echo esc_url($novinky_url); ?>"
                    class="link-arrow link-arrow-back text-slate-600 hover:text-brand-600"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 12H5M11 18l-6-6 6-6" />
                    </svg>
                    <?php esc_html_e('Späť na novinky', 'nexdigital'); ?>
                </a>
            </div>
        </nav>
    <?php endif; ?>

    <article <?php post_class(); ?>>
        <header class="bg-sand-50 pb-12 pt-8 sm:pb-16">
            <div class="site-container max-w-3xl">
                <p class="flex flex-wrap items-center gap-x-3 gap-y-1.5 text-[0.6875rem] font-bold uppercase tracking-[0.15em] text-slate-500">
                    <?php if ($category instanceof WP_Term) : ?>
                        <a
                            href="<?php echo esc_url(get_category_link($category)); ?>"
                            class="text-brand-700 transition hover:text-brand-600"
                        >
                            <?php echo esc_html($category->name); ?>
                        </a>
                        <span aria-hidden="true">·</span>
                    <?php endif; ?>
                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                        <?php echo esc_html(get_the_date()); ?>
                    </time>
                </p>

                <h1 class="mt-5 text-3xl font-black leading-[1.05] tracking-tight text-ink sm:text-4xl lg:text-5xl">
                    <?php echo esc_html($title); ?>
                </h1>
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

        <div class="site-container max-w-3xl py-12 sm:py-16">
            <div class="rich-text">
                <?php the_content(); ?>
            </div>
        </div>
    </article>

    <?php
    $others = get_posts([
        'numberposts'  => 3,
        'post__not_in' => [$post_id],
    ]);
    ?>

    <?php if ($others !== []) : ?>
        <aside class="bg-sand-50 py-12 sm:py-16" aria-label="<?php esc_attr_e('Ďalšie novinky', 'nexdigital'); ?>">
            <div class="site-container">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <h2 class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                        <?php esc_html_e('Ďalšie novinky', 'nexdigital'); ?>
                    </h2>

                    <?php if ($novinky_url !== '') : ?>
                        <a href="<?php echo esc_url($novinky_url); ?>" class="link-arrow text-brand-600 hover:text-brand-700">
                            <?php esc_html_e('Všetky novinky', 'nexdigital'); ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <?php
                    global $post;

                    foreach ($others as $post) {
                        setup_postdata($post);
                        get_template_part('template-parts/content', get_post_type());
                    }

                    wp_reset_postdata();
                    ?>
                </div>
            </div>
        </aside>
    <?php endif; ?>

    <?php
endwhile;

get_footer();
