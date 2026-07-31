<?php
/**
 * Candidate archive.
 *
 * The generic archive card is built for posts — a 16:9 thumbnail, a date and an
 * excerpt. A candidate roster is none of those things: the photographs are 3:4
 * studio portraits that a 16:9 box cuts through the middle of, and a publish
 * date on a candidate means nothing to a voter. So this reuses the same card
 * component the front page uses, which is also what keeps the two grids looking
 * like one site.
 *
 * The main query is already ordered by ballot number and unpaginated — see
 * PostTypes\order_candidates_by_ballot() — so the loop only has to split the
 * leader out of the rest.
 *
 * @package NexDigital
 */

declare(strict_types=1);

use function NexDigital\Theme\Fields\field;

get_header();

$leaders = [];
$rest = [];

while (have_posts()) {
    the_post();

    $id = get_the_ID();

    if (field('je_lider', $id)) {
        $leaders[] = get_post($id);
    } else {
        $rest[] = get_post($id);
    }
}
?>

<?php // Top padding only: candidates-section brings its own generous py-16 and
      // the two stacked left a band of empty sand between the intro and the
      // first card. Same background on both, so the seam is invisible. ?>
<div class="bg-sand-50 pt-12 sm:pt-16">
    <div class="site-container">
        <h1 class="text-3xl font-black leading-[1.05] tracking-tight text-ink sm:text-4xl lg:text-5xl">
            <?php the_archive_title(); ?>
        </h1>

        <?php the_archive_description('<div class="mt-5 max-w-2xl text-base leading-relaxed text-slate-700">', '</div>'); ?>

        <p class="mt-5 max-w-2xl text-base leading-relaxed text-slate-700">
            <?php esc_html_e('Kandidáti sú zoradení podľa poradového čísla na hlasovacom lístku — v tom istom poradí, v akom ich nájdete na papieri vo volebnej miestnosti.', 'nexdigital'); ?>
        </p>
    </div>
</div>

<?php if ($leaders !== [] || $rest !== []) : ?>
    <?php get_template_part('template-parts/candidates-section', null, [
        'leaders' => $leaders,
        'rest'    => $rest,
    ]); ?>
<?php else : ?>
    <div class="site-container py-16">
        <p class="text-slate-600"><?php esc_html_e('Zatiaľ tu nie sú žiadni kandidáti.', 'nexdigital'); ?></p>
    </div>
<?php endif; ?>

<?php get_footer(); ?>
