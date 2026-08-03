<?php
/**
 * Ballot call to action.
 *
 * The number is the one thing a voter has to carry into the booth, so it gets
 * the largest type on the page after the name. The location photograph sits
 * behind it under a heavy overlay — without one the band is simply flat, and
 * the layout does not change.
 *
 * The copy is hard-coded rather than fielded: this is chrome repeated across
 * twelve profiles, not content the client arranges — the same reasoning as the
 * footer.
 *
 * Expected args:
 *   post_id int Candidate post.
 *
 * @package NexDigital
 */

declare(strict_types=1);

use function NexDigital\Theme\Fields\field;

$post_id = (int) ($args['post_id'] ?? 0);

if ($post_id <= 0) {
    return;
}

$number = trim((string) (field('cislo', $post_id) ?? ''));
$photo = (int) (field('foto_portret', $post_id) ?? 0);

$archive = get_post_type_archive_link('kandidat');

// get_permalink(null) silently falls back to the global post, which inside the
// loop is this very candidate — so the page has to be checked before asking.
$programme_page = get_page_by_path('program');
$programme = $programme_page instanceof WP_Post ? get_permalink($programme_page) : '';
?>

<section class="relative isolate overflow-hidden bg-brand-950 py-16 text-white sm:py-20">
    <?php if ($photo > 0) : ?>
        <?php // The location photos are 3:2 landscape and this band is far wider
              // than that, so cover crops vertically — from the centre it took
              // the head off everyone standing in frame. Biasing the window to
              // the upper quarter keeps faces in and pushes the crop down into
              // the ground, the same trick the hero uses on its own photos. ?>
        <?php echo wp_get_attachment_image($photo, 'large', false, [
            'class'    => 'absolute inset-0 -z-10 h-full w-full object-cover object-[50%_25%]',
            'alt'      => '',
            'loading'  => 'lazy',
            'decoding' => 'async',
        ]); ?>
        <?php // Heavy enough that teal on top keeps its contrast whatever the
              // photograph underneath happens to be. ?>
        <span class="absolute inset-0 -z-10 bg-brand-950/85" aria-hidden="true"></span>
    <?php endif; ?>

    <div class="site-container">
        <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-teal-400">
            <?php esc_html_e('Voľby do orgánov samosprávy obcí', 'nexdigital'); ?>
        </p>

        <?php if ($number !== '') : ?>
            <p class="mt-6 flex items-center gap-5">
                <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-teal-400 text-4xl font-black leading-none text-brand-950 sm:h-24 sm:w-24 sm:text-5xl">
                    <?php echo esc_html($number); ?>
                </span>
                <span class="text-3xl font-black leading-[1.05] tracking-tight sm:text-4xl">
                    <?php esc_html_e('Zakrúžkujte toto číslo', 'nexdigital'); ?>
                </span>
            </p>
        <?php endif; ?>

        <p class="mt-6 max-w-xl text-sm leading-relaxed text-slate-300 sm:text-base">
            <?php esc_html_e('Na hlasovacom lístku do mestského zastupiteľstva krúžkujete poradové čísla kandidátov. Voliť môžete len v obci svojho trvalého pobytu — hlasovací preukaz ani voľba poštou pri komunálnych voľbách neexistujú.', 'nexdigital'); ?>
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
            <?php if (is_string($archive) && $archive !== '') : ?>
                <a
                    href="<?php echo esc_url($archive); ?>"
                    class="btn btn-light"
                >
                    <?php esc_html_e('Všetci kandidáti', 'nexdigital'); ?>
                </a>
            <?php endif; ?>

            <?php if (is_string($programme) && $programme !== '') : ?>
                <a
                    href="<?php echo esc_url($programme); ?>"
                    class="btn btn-ghost"
                >
                    <?php esc_html_e('Náš program', 'nexdigital'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
