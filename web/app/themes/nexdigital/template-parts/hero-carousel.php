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
                <?php // justify-start on phones, centred only from lg. Slides in a
                      // snap track stretch to the tallest one, and centring split
                      // that slack above and below the text — 70px of empty petrol
                      // over the headline on the shortest slide, which read as a
                      // broken top margin rather than as breathing room. Anchored
                      // to the top, the slack falls to the photo instead. ?>
                class="hero-slide relative flex w-full shrink-0 snap-start flex-col justify-start lg:justify-center <?php echo $is_banner ? 'isolate bg-brand-950 text-white' : 'bg-sand-50 text-ink'; ?>"
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

                        // On phones every mode is one fixed band at the top of the
                        // slide — see the reordering note on the wrapper below. A
                        // stated height is also what lets the photo inside use
                        // h-full: a percentage cannot resolve against a parent whose
                        // own height came from that same image.
                        // 18rem is the shallowest band that still holds a head and
                        // shoulders: the thesis slide crops a headshot into the same
                        // band, and at 14rem it cut the crown. Both slides use it, so
                        // they stay the same height and the track has no slack to
                        // spill as empty petrol.
                        $phone_band = 'h-72 lg:h-auto';

                        $column = match (true) {
                            $full       => $phone_band . ' lg:absolute lg:inset-0 lg:w-full shrink-0',
                            $background => $phone_band . ' lg:absolute lg:inset-y-0 lg:right-0 lg:w-[80%] shrink-0',
                            $whole      => $phone_band . ' lg:absolute lg:inset-y-0 lg:left-1/2 lg:w-1/2 shrink-0',
                            default     => $phone_band . ' lg:absolute lg:inset-y-0 lg:left-1/2 lg:w-1/2 shrink-0 lg:min-h-0',
                        };

                        // Phones stack: a headline sitting on top of a face loses both.
                        // Only the cropped variant fills its band there; the other two
                        // keep the frame intact at the bottom of the slide.
                        // 35% down the frame on phones: in every delivered banner
                        // photograph the faces sit just above the middle, and a band
                        // this shallow crops sky off the top and grass off the bottom.
                        $image_class = match (true) {
                            $full       => 'h-full object-cover object-[50%_35%] lg:object-[50%_28%]',
                            $background => 'h-full object-cover object-[50%_35%]',
                            // Centred, not bottom-anchored: a contained photo cannot fill the
                            // column, and hanging it off the bottom edge reads as a mistake,
                            // while equal air above and below reads as a frame.
                            $whole      => 'h-full object-cover object-[50%_35%] lg:object-contain lg:object-center',
                            default     => 'h-full object-cover object-[50%_35%] lg:object-center',
                        };
                        ?>
                        <?php // No order-last any more: on a phone the photograph opens
                              // the slide. It is the fastest thing to read on a small
                              // screen — a face says who this is before a headline can —
                              // and pulling it up is what lets the headline, the copy
                              // and both buttons finish above the fold instead of the
                              // photograph sitting alone below it. Desktop is unchanged;
                              // there the column is absolutely positioned and order
                              // never applied. ?>
                        <div class="relative w-full <?php echo $column; ?>">
                            <img
                                <?php $image_src($image_attrs($slide['image'], $background ? '(min-width: 64rem) 80vw, 100vw' : '(min-width: 64rem) 50vw, 100vw')); ?>
                                alt=""
                                class="w-full <?php echo $image_class; ?>"
                                <?php echo $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
                                decoding="async"
                            >
                            <?php // Phone-only: the photograph dissolves into the petrol
                                  // panel instead of ending on a ruled edge. Desktop
                                  // already blends its photo into the panel with a
                                  // horizontal gradient — this is the same move turned
                                  // 90°, so the two read as one design rather than two. ?>
                            <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-b from-transparent to-brand-950 lg:hidden" aria-hidden="true"></div>

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
                    <?php // Asymmetric on phones: the sticky header already sits right
                          // above, so a full 3.5rem over the eyebrow is space the
                          // headline does not need.
                          //
                          // grow is where the carousel's slack goes on a phone. Slides
                          // stretch to the tallest one, and that leftover has to land
                          // somewhere: inside this petrol block it reads as room under
                          // the buttons, while below the photo it read as a broken gap
                          // at the end of the section. ?>
                    <?php // pb leaves the dot pill a lane of its own at the foot of the
                          // section; without it the buttons and the pill share space. ?>
                    <div class="site-container relative z-10 grow pb-16 pt-6 sm:py-16 lg:grow-0 lg:py-24">
                        <?php // flex column so the buttons can be sent to the end on a
                              // phone with `order`. Ordinary block flow ignores order,
                              // and the children keep their own mt-* either way, so
                              // desktop spacing is untouched. ?>
                        <div class="flex max-w-2xl flex-col">
                            <?php if (!empty($slide['eyebrow'])) : ?>
                                <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-teal-400">
                                    <?php echo esc_html($slide['eyebrow']); ?>
                                </p>
                            <?php endif; ?>

                            <h1 class="mt-4 text-[2.25rem] font-black uppercase leading-[0.95] tracking-[-0.01em] sm:mt-5 sm:text-5xl lg:text-6xl">
                                <?php echo esc_html($slide['title']); ?>
                                <?php if (!empty($slide['title_accent'])) : ?>
                                    <span class="block text-teal-400"><?php echo esc_html($slide['title_accent']); ?></span>
                                <?php endif; ?>
                            </h1>

                            <?php if (!empty($slide['text'])) : ?>
                                <p class="mt-4 max-w-xl text-base leading-relaxed text-slate-100 sm:mt-6 sm:text-lg sm:leading-relaxed">
                                    <?php echo esc_html($slide['text']); ?>
                                </p>
                            <?php endif; ?>

                            <?php // order-last on phones: the ask belongs at the end of the
                                  // argument, after the endorsements that back it up, and
                                  // it is also where a thumb rests. Desktop keeps the DOM
                                  // order, where the buttons sit above the party marks. ?>
                            <?php if (!empty($slide['ctas'])) : ?>
                                <div class="order-last mt-7 flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4 lg:order-none lg:mt-9">
                                    <?php foreach ($slide['ctas'] as $item) {
                                        $cta($item, $context);
                                    } ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($slide['logos'])) : ?>
                                <?php // Party marks keep their own colours, so they sit on a white
                                      // chip instead of fighting the petrol background. Tighter on
                                      // a phone: here it is a credential under the copy, not a
                                      // banner, and every pixel it takes is one the buttons need
                                      // to stay above the fold. ?>
                                <div class="mt-6 inline-flex flex-wrap items-center gap-x-6 gap-y-3 self-start rounded-md bg-white px-5 py-3 sm:gap-x-8 sm:gap-y-4 sm:px-6 sm:py-4 lg:mt-10">
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
                                            class="<?php echo esc_attr($logo['size'] ?? 'h-7 sm:h-9'); ?> w-auto"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else : ?>
                    <?php // Tighter on phones than a banner slide, on purpose: the track
                          // is as tall as its tallest slide, and every pixel this one
                          // spends becomes empty colour on the other. ?>
                    <div class="site-container grid w-full items-center gap-4 pb-10 pt-4 sm:gap-12 sm:py-14 lg:grid-cols-12 lg:gap-14 lg:py-20">
                        <div class="lg:col-span-7">
                            <?php if (!empty($slide['eyebrow'])) : ?>
                                <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                                    <?php echo esc_html($slide['eyebrow']); ?>
                                </p>
                            <?php endif; ?>

                            <h1 class="mt-4 text-[2rem] font-black leading-[0.95] tracking-[-0.02em] sm:mt-5 sm:text-6xl lg:text-[4.25rem]">
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
                                <div class="mt-7 flex flex-col gap-3 sm:mt-9 sm:flex-row sm:items-center sm:gap-4">
                                    <?php foreach ($slide['ctas'] as $item) {
                                        $cta($item, $context);
                                    } ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php // order-first mirrors the banner slide: on a phone both slides
                              // open with a face, so swiping between them does not
                              // reshuffle the page. ?>
                        <?php if (!empty($slide['image'])) : ?>
                            <div class="order-first lg:order-none lg:col-span-5">
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
                                        <?php // The phone cap is measured, not chosen: at 26rem this
                                              // slide stood 158px taller than the banner one, and the
                                              // track handed that difference to the banner slide as a
                                              // gap between its logos and its photograph. object-cover
                                              // with object-top means the cap crops the body, never the
                                              // face. Retune it if this slide's copy changes length. ?>
                                        class="aspect-[3/4] max-h-[16rem] w-full rounded-lg border border-slate-200 bg-white object-cover object-[50%_15%] sm:max-h-[26rem] sm:object-top lg:aspect-auto lg:max-h-[42rem] lg:h-[calc(100svh-var(--spacing-header)-10rem)]"
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
        <?php // Dots only, centred on the foot of the section. The arrows are gone:
              // the track is a real scroll container, so a phone swipes and a
              // keyboard arrows through it natively, and the dots already say how
              // many slides there are and which one is showing — which an arrow
              // never did. The pill stays neutral because it sits over petrol on
              // one slide and sand on the other. ?>
        <div class="pointer-events-none absolute inset-x-0 bottom-4 sm:bottom-6">
            <div class="site-container flex justify-center">
                <div class="pointer-events-auto flex items-center gap-2 rounded-full border border-slate-200 bg-white/90 px-3 py-2 backdrop-blur" data-hero-dots>
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
            </div>
        </div>
    <?php endif; ?>
</section>
