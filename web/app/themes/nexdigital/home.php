<?php
/**
 * Novinky — the posts index (page_for_posts).
 *
 * Front-page-of-a-newspaper logic: the newest post is the lead story, pulled
 * up out of the sand header band the same way the project detail pulls its
 * photograph out of its header; the rest sit in a quiet grid. Deeper pages
 * drop the lead — page three has no "latest" story to tell.
 *
 * The intro under the heading is the Novinky page's own content, so the client
 * edits it like any other text rather than asking for a template change.
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();

$novinky_page = get_post((int) get_option('page_for_posts'));
$intro = $novinky_page instanceof WP_Post ? trim(wp_strip_all_tags($novinky_page->post_content)) : '';

$lead = have_posts() && !is_paged();
?>

<header class="bg-sand-50 pt-12 sm:pt-16 <?php echo $lead ? 'pb-24 sm:pb-28' : 'pb-12 sm:pb-16'; ?>">
    <div class="site-container">
        <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
            <?php esc_html_e('Aktuality z mesta a kampane', 'nexdigital'); ?>
        </p>
        <h1 class="mt-3 text-3xl font-black leading-[1.05] tracking-tight text-ink sm:text-4xl lg:text-5xl">
            <?php single_post_title(); ?>
        </h1>
        <?php if ($intro !== '') : ?>
            <p class="mt-5 max-w-2xl text-base leading-relaxed text-slate-600">
                <?php echo esc_html($intro); ?>
            </p>
        <?php endif; ?>
    </div>
</header>

<?php if ($lead) : the_post(); ?>
    <?php
    $lead_category = get_the_category()[0] ?? null;
    $lead_has_thumbnail = has_post_thumbnail();
    ?>
    <div class="site-container -mt-14 sm:-mt-16">
        <article <?php post_class('overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm'); ?>>
            <div class="grid lg:grid-cols-2">
                <?php if ($lead_has_thumbnail) : ?>
                    <a href="<?php the_permalink(); ?>" class="group block overflow-hidden bg-sand-100" tabindex="-1" aria-hidden="true">
                        <?php the_post_thumbnail('large', [
                            'class' => 'aspect-video h-full w-full object-cover object-top transition duration-300 group-hover:scale-[1.03]',
                        ]); ?>
                    </a>
                <?php endif; ?>

                <div class="flex flex-col justify-center p-6 sm:p-10">
                    <p class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                        <?php if ($lead_category instanceof WP_Term) : ?>
                            <a
                                href="<?php echo esc_url(get_category_link($lead_category)); ?>"
                                class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-[0.625rem] font-bold uppercase tracking-[0.12em] text-brand-700 transition hover:bg-brand-100"
                            >
                                <?php echo esc_html($lead_category->name); ?>
                            </a>
                        <?php endif; ?>
                        <time class="text-xs font-medium text-slate-500" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                            <?php echo esc_html(get_the_date()); ?>
                        </time>
                    </p>

                    <h2 class="mt-4 text-2xl font-black leading-tight tracking-tight sm:text-3xl">
                        <a href="<?php the_permalink(); ?>" class="text-ink transition hover:text-brand-600">
                            <?php the_title(); ?>
                        </a>
                    </h2>

                    <p class="mt-4 line-clamp-4 text-base leading-relaxed text-slate-600">
                        <?php echo esc_html(get_the_excerpt()); ?>
                    </p>

                    <p class="mt-6">
                        <a href="<?php the_permalink(); ?>" class="link-arrow text-brand-600 hover:text-brand-700">
                            <?php esc_html_e('Prečítať článok', 'nexdigital'); ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </a>
                    </p>
                </div>
            </div>
        </article>
    </div>
<?php endif; ?>

<div class="site-container py-12 sm:py-16">
    <?php if (have_posts()) : ?>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php
            while (have_posts()) :
                the_post();
                get_template_part('template-parts/content', get_post_type());
            endwhile;
            ?>
        </div>

        <div class="mt-12">
            <?php the_posts_pagination([
                'mid_size'  => 1,
                'prev_text' => __('Novšie', 'nexdigital'),
                'next_text' => __('Staršie', 'nexdigital'),
            ]); ?>
        </div>
    <?php elseif (!$lead) : ?>
        <p class="text-slate-600"><?php esc_html_e('Zatiaľ tu nie sú žiadne novinky.', 'nexdigital'); ?></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
