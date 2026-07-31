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
use function NexDigital\Theme\Fields\option;

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

<?php // The heading and intro go through candidates-section rather than sitting in
      // a block of their own above it. Two stacked blocks meant two sets of
      // vertical padding and a band of empty sand between the text and the
      // first card. ?>
<?php if ($leaders !== [] || $rest !== []) : ?>
    <?php get_template_part('template-parts/candidates-section', null, [
        'heading' => 'h1',
        'title'   => trim((string) (option('opt_kandidati_nadpis') ?: '')) ?: get_the_archive_title(),
        'text'    => trim((string) (option('opt_kandidati_uvod') ?: '')),
        'leaders' => $leaders,
        'rest'    => $rest,
    ]); ?>
<?php else : ?>
    <div class="site-container py-16">
        <p class="text-slate-600"><?php esc_html_e('Zatiaľ tu nie sú žiadni kandidáti.', 'nexdigital'); ?></p>
    </div>
<?php endif; ?>

<?php get_footer(); ?>
