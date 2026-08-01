<?php
/**
 * Hero carousel.
 *
 * Renders one or more hero slides. With a single slide the controls are not
 * printed at all — no arrows, no dots, no JS behaviour — so the same partial
 * serves a rotating campaign banner and a plain static hero.
 *
 * The track is a scroll-snap row rather than a transform slider: swiping,
 * keyboard scrolling and "no JS" all work before a single line of script runs,
 * and the script only adds the controls' behaviour on top.
 *
 * Expected args:
 *   slides array<int, array{
 *     type: 'banner'|'thesis',
 *     eyebrow?: string,
 *     title: string,
 *     title_accent?: string,   // second part of the heading, tinted
 *     text?: string,
 *     image?: string,          // theme-relative path
 *     image_alt?: string,
 *     ctas?: array<int, array{label:string, url:string, style?:'primary'|'ghost'}>,
 *     logos?: array<int, array{src:string, alt:string, width:int, height:int}>,
 *     caption?: array{number?:string, name:string, role:string},
 *   }
 *   label string  Accessible name of the carousel region.
 *
 * @package NexDigital
 */

declare(strict_types=1);

$slides = isset($args['slides']) && is_array($args['slides']) ? array_values($args['slides']) : [];
$label  = $args['label'] ?? __('Hlavné oznámenia kampane', 'nexdigital');
$count  = count($slides);

if ($count === 0) {
    return;
}

/**
 * Resolve an image to markup attributes.
 *
 * Slides arrive from two places: the block, which stores media library
 * attachment ids, and hard-coded template arrays, which carry a theme-relative
 * path. Attachments also get a srcset, which a bundled file cannot have.
 *
 * @return array{src:string, srcset:string, sizes:string}
 */
$image_attrs = static function (mixed $image, string $sizes): array {
    if (is_numeric($image)) {
        $id = (int) $image;

        return [
            'src'    => (string) wp_get_attachment_image_url($id, 'full'),
            'srcset' => (string) wp_get_attachment_image_srcset($id, 'full'),
            'sizes'  => $sizes,
        ];
    }

    return [
        'src'    => get_theme_file_uri((string) $image),
        'srcset' => '',
        'sizes'  => '',
    ];
};

/** Print src/srcset/sizes, skipping the attributes that have no value. */
$image_src = static function (array $attrs): void {
    printf('src="%s"', esc_url($attrs['src']));

    if ($attrs['srcset'] !== '') {
        printf(' srcset="%s" sizes="%s"', esc_attr($attrs['srcset']), esc_attr($attrs['sizes']));
    }
};

/** Render one call-to-action button. */
$cta = static function (array $item, string $context): void {
    $style = $item['style'] ?? 'primary';

    $variant = match (true) {
        $style === 'primary' => 'btn-primary',
        $context === 'dark'  => 'btn-ghost',
        default              => 'btn-outline',
    };

    $target = trim((string) ($item['target'] ?? ''));

    printf(
        '<a href="%s"%s class="btn %s">%s</a>',
        esc_url($item['url']),
        $target === '' ? '' : sprintf(' target="%s" rel="noopener"', esc_attr($target)),
        esc_attr($variant),
        esc_html($item['label'])
    );
};
?>

<section
    class="relative isolate"
    <?php if ($count > 1) : ?>
        aria-roledescription="carousel"
        aria-label="<?php echo esc_attr($label); ?>"
        data-hero-carousel
    <?php endif; ?>
>
    <div
        class="hero-track flex snap-x snap-mandatory overflow-x-auto overscroll-x-contain"
        <?php if ($count > 1) : ?>
            tabindex="0"
            aria-live="polite"
            data-hero-track
        <?php endif; ?>
    >
        <?php foreach ($slides as $i => $slide) :
            $is_banner = ($slide['type'] ?? 'thesis') === 'banner';
            $context = $is_banner ? 'dark' : 'light';
            ?>
            <article
                class="hero-slide relative flex w-full shrink-0 snap-start flex-col justify-center <?php echo $is_banner ? 'isolate bg-brand-950 text-white' : 'bg-sand-50 text-ink'; ?>"
                <?php if ($count > 1) : ?>
                    role="group"
                    aria-roledescription="<?php esc_attr_e('snímka', 'nexdigital'); ?>"
                    aria-label="<?php
                        printf(
                            /* translators: 1: slide number, 2: total slides */
                            esc_attr__('%1$d z %2$d', 'nexdigital'),
                            $i + 1,
                            $count
                        );
                    ?>"
                <?php endif; ?>
            >
                <?php if ($is_banner) : ?>
                    <?php if (!empty($slide['image'])) :
                        $fit = $slide['image_fit'] ?? 'cover';

                        // Three ways to place a banner photo, each solving a different
                        // problem. A half-width column can either fill (cropping the
                        // sides) or show the whole frame (leaving empty petrol above);
                        // for a group photo both are bad, because the people are spread
                        // across the full width of the frame. "background" is the way
                        // out: the photo runs nearly the full width at full height, so
                        // the crop lands on the sky and the grass instead of on the
                        // people at the ends of the row, and the text keeps its contrast
                        // from a gradient rather than from an empty panel.
                        // "full" goes one step further: at the full width of the slide
                        // the frame is wider than the photo's own ratio, so cover crops
                        // the sky and the grass and never a person — the whole row
                        // survives. The price is that the text sits over the photo, so
                        // the wash covers the entire frame instead of fading out.
                        $full = $fit === 'full';
                        $background = $fit === 'background';
                        $whole = $fit === 'whole';

                        $column = match (true) {
                            $full       => 'lg:absolute lg:inset-0 lg:w-full shrink-0',
                            $background => 'lg:absolute lg:inset-y-0 lg:right-0 lg:w-[80%] shrink-0',
                            $whole      => 'lg:absolute lg:inset-y-0 lg:left-1/2 lg:w-1/2 shrink-0',
                            default     => 'lg:absolute lg:inset-y-0 lg:left-1/2 lg:w-1/2 min-h-56 grow sm:min-h-72 lg:min-h-0 lg:grow-0',
                        };

                        // Phones stack: a headline sitting on top of a face loses both.
                        // Only the cropped variant fills its band there; the other two
                        // keep the frame intact at the bottom of the slide.
                        $image_class = match (true) {
                            $full       => 'lg:h-full lg:object-cover lg:object-[50%_28%]',
                            $background => 'lg:h-full lg:object-cover lg:object-[50%_35%]',
                            // Centred, not bottom-anchored: a contained photo cannot fill the
                            // column, and hanging it off the bottom edge reads as a mistake,
                            // while equal air above and below reads as a frame.
                            $whole      => 'lg:h-full lg:object-contain lg:object-center',
                            default     => 'h-full object-cover object-[50%_18%] lg:object-center',
                        };
                        ?>
                        <div class="relative order-last w-full <?php echo $column; ?>">
                            <img
                                <?php $image_src($image_attrs($slide['image'], $background ? '(min-width: 64rem) 80vw, 100vw' : '(min-width: 64rem) 50vw, 100vw')); ?>
                                alt=""
                                class="w-full <?php echo $image_class; ?>"
                                <?php echo $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
                                decoding="async"
                            >
                            <?php if ($full) : ?>
                                <?php // Two layers rather than one gradient. An even wash keeps the
                                      // whole row equally visible — the point of this mode — while a
                                      // softer left boost buys the headline its contrast. One
                                      // gradient doing both jobs ends up 90% opaque on the left,
                                      // which hides the person standing there. ?>
                                <div class="absolute inset-0 hidden bg-brand-950/50 lg:block" aria-hidden="true"></div>
                                <div class="absolute inset-0 hidden bg-gradient-to-r from-brand-950/50 via-brand-950/15 via-40% to-transparent to-65% lg:block" aria-hidden="true"></div>
                            <?php elseif ($background) : ?>
                                <?php // The wash is what makes white text on a sunlit park
                                      // readable. It fades out before the middle of the frame so
                                      // the group is never behind a filter. ?>
                                <div class="absolute inset-0 hidden bg-gradient-to-r from-brand-950 from-12% via-brand-950/75 via-38% to-transparent to-66% lg:block" aria-hidden="true"></div>
                            <?php elseif (!$whole) : ?>
                                <?php // Seam: the photo's left edge would otherwise cut the petrol
                                      // panel with a hard vertical line. A contained photo does not
                                      // reach that edge, so the gradient would only dim the group. ?>
                                <div class="absolute inset-y-0 left-0 hidden w-32 bg-gradient-to-r from-brand-950 to-transparent lg:block" aria-hidden="true"></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php // z-10: with a background photo the image column overlaps this one. ?>
                    <div class="site-container relative z-10 py-14 sm:py-16 lg:py-24">
                        <div class="max-w-2xl">
                            <?php if (!empty($slide['eyebrow'])) : ?>
                                <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-teal-400">
                                    <?php echo esc_html($slide['eyebrow']); ?>
                                </p>
                            <?php endif; ?>

                            <h1 class="mt-5 text-[2.25rem] font-black uppercase leading-[0.95] tracking-[-0.01em] sm:text-5xl lg:text-6xl">
                                <?php echo esc_html($slide['title']); ?>
                                <?php if (!empty($slide['title_accent'])) : ?>
                                    <span class="block text-teal-400"><?php echo esc_html($slide['title_accent']); ?></span>
                                <?php endif; ?>
                            </h1>

                            <?php if (!empty($slide['text'])) : ?>
                                <p class="mt-6 max-w-xl text-base leading-relaxed text-slate-100 sm:text-lg sm:leading-relaxed">
                                    <?php echo esc_html($slide['text']); ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($slide['ctas'])) : ?>
                                <div class="mt-9 flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                                    <?php foreach ($slide['ctas'] as $item) {
                                        $cta($item, $context);
                                    } ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($slide['logos'])) : ?>
                                <?php // Party marks keep their own colours, so they sit on a white
                                      // chip instead of fighting the petrol background. ?>
                                <div class="mt-10 inline-flex flex-wrap items-center gap-x-8 gap-y-4 rounded-md bg-white px-6 py-4">
                                    <?php foreach ($slide['logos'] as $logo) : ?>
                                        <img
                                            <?php $image_src($image_attrs($logo['src'], '12rem')); ?>
                                            alt="<?php echo esc_attr($logo['alt']); ?>"
                                            <?php // Intrinsic size only for bundled files; an attachment
                                                  // carries its own dimensions through srcset. ?>
                                            <?php if (!empty($logo['width']) && !empty($logo['height'])) : ?>
                                                width="<?php echo (int) $logo['width']; ?>"
                                                height="<?php echo (int) $logo['height']; ?>"
                                            <?php endif; ?>
                                            <?php // Height is per logo: a stacked lockup needs more
                                                  // room than a horizontal wordmark to read at the
                                                  // same optical size. ?>
                                            class="<?php echo esc_attr($logo['size'] ?? 'h-8 sm:h-9'); ?> w-auto"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="site-container grid w-full items-center gap-12 py-14 lg:grid-cols-12 lg:gap-14 lg:py-20">
                        <div class="lg:col-span-7">
                            <?php if (!empty($slide['eyebrow'])) : ?>
                                <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                                    <?php echo esc_html($slide['eyebrow']); ?>
                                </p>
                            <?php endif; ?>

                            <h1 class="mt-5 text-[2.5rem] font-black leading-[0.95] tracking-[-0.02em] sm:text-6xl lg:text-[4.25rem]">
                                <?php echo esc_html($slide['title']); ?>
                                <?php if (!empty($slide['title_accent'])) : ?>
                                    <span class="block text-brand-600"><?php echo esc_html($slide['title_accent']); ?></span>
                                <?php endif; ?>
                            </h1>

                            <?php if (!empty($slide['text'])) : ?>
                                <p class="mt-7 max-w-xl text-base leading-relaxed text-slate-700 sm:text-lg sm:leading-relaxed">
                                    <?php echo esc_html($slide['text']); ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($slide['ctas'])) : ?>
                                <div class="mt-9 flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                                    <?php foreach ($slide['ctas'] as $item) {
                                        $cta($item, $context);
                                    } ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($slide['image'])) : ?>
                            <div class="lg:col-span-5">
                                <figure class="relative mx-auto max-w-sm lg:max-w-none">
                                    <img
                                        <?php $image_src($image_attrs($slide['image'], '(min-width: 64rem) 26rem, 24rem')); ?>
                                        <?php // The ratio is enforced here rather than trusted from the
                                              // upload: a portrait that arrives 4:3 would otherwise
                                              // reshape the whole slide. On desktop the slide has a
                                              // fixed height, so there the portrait is measured off the
                                              // viewport instead — 10rem is the section's own padding. ?>
                                        <?php // The phone cap keeps this slide close in height to a
                                              // banner slide; the track is as tall as its tallest
                                              // slide, so an unbounded portrait would open a band of
                                              // empty colour above every other slide's headline. ?>
                                        class="aspect-[3/4] max-h-[26rem] w-full rounded-lg border border-slate-200 bg-white object-cover object-top lg:aspect-auto lg:max-h-[42rem] lg:h-[calc(100svh-var(--spacing-header)-10rem)]"
                                        alt="<?php echo esc_attr($slide['image_alt'] ?? ''); ?>"
                                        <?php echo $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
                                        decoding="async"
                                    >

                                    <?php if (!empty($slide['caption'])) : ?>
                                        <figcaption class="absolute inset-x-3 bottom-3 flex items-center gap-3 rounded-md border border-slate-200 bg-white/95 p-3 backdrop-blur sm:inset-x-4 sm:bottom-4 sm:gap-4 sm:p-4">
                                            <?php if (!empty($slide['caption']['number'])) : ?>
                                                <?php // The ballot number is not decoration: it is what a voter circles on paper. ?>
                                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-2 border-brand-600 text-lg font-black leading-none text-brand-600 sm:h-12 sm:w-12 sm:text-xl">
                                                    <?php echo esc_html($slide['caption']['number']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <span class="min-w-0">
                                                <span class="block text-sm font-black leading-tight text-ink sm:text-base">
                                                    <?php echo esc_html($slide['caption']['name']); ?>
                                                </span>
                                                <span class="mt-1 block text-[0.6875rem] font-semibold uppercase leading-tight tracking-[0.08em] text-slate-500">
                                                    <?php echo esc_html($slide['caption']['role']); ?>
                                                </span>
                                            </span>
                                        </figcaption>
                                    <?php endif; ?>
                                </figure>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($count > 1) : ?>
        <?php // Controls are printed only for real carousels, so a one-slide hero has
              // nothing to hide and nothing to flash before the script loads. ?>
        <div class="pointer-events-none absolute inset-x-0 bottom-4 sm:bottom-6">
            <div class="site-container flex justify-end">
                <div class="pointer-events-auto flex items-center gap-1 rounded-md border border-slate-200 bg-white/90 p-1.5 backdrop-blur">
                    <button
                        type="button"
                        class="btn-icon btn-icon-sm"
                        data-hero-prev
                    >
                        <span class="sr-only"><?php esc_html_e('Predchádzajúca snímka', 'nexdigital'); ?></span>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 12H5M11 18l-6-6 6-6" />
                        </svg>
                    </button>

                    <div class="flex items-center gap-1.5 px-2" data-hero-dots>
                        <?php foreach ($slides as $i => $slide) : ?>
                            <button
                                type="button"
                                class="h-2 w-2 rounded-full bg-slate-300 transition data-[active=true]:w-6 data-[active=true]:bg-brand-600"
                                data-hero-dot="<?php echo (int) $i; ?>"
                                <?php echo $i === 0 ? 'data-active="true" aria-current="true"' : ''; ?>
                            >
                                <span class="sr-only">
                                    <?php
                                    printf(
                                        /* translators: %d: slide number */
                                        esc_html__('Snímka %d', 'nexdigital'),
                                        $i + 1
                                    );
                                    ?>
                                </span>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <button
                        type="button"
                        class="btn-icon btn-icon-sm"
                        data-hero-next
                    >
                        <span class="sr-only"><?php esc_html_e('Ďalšia snímka', 'nexdigital'); ?></span>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>
