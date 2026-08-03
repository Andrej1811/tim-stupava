<?php
/**
 * Resident-ideas section — the prompt beside the napad-obyvatela form.
 *
 * White on purpose: on the front page it sits between the sand programme
 * section and the dark voting panel, and the alternation is what keeps five
 * stacked sections readable as five sections. The card is the inverse of the
 * contact page (sand card on white, where kontakt has a white card on sand),
 * so the two forms read as siblings without being copies.
 *
 * Expected args: heading, eyebrow, title, text, submit, note, slug.
 *
 * @package NexDigital
 */

declare(strict_types=1);

$eyebrow = trim((string) ($args['eyebrow'] ?? ''));
$title = trim((string) ($args['title'] ?? ''));
$text = trim((string) ($args['text'] ?? ''));
$submit = trim((string) ($args['submit'] ?? ''));
$note = trim((string) ($args['note'] ?? ''));
$slug = trim((string) ($args['slug'] ?? ''));

$heading = ($args['heading'] ?? 'h2') === 'h1' ? 'h1' : 'h2';

if ($slug === '') {
    return;
}
?>

<section class="bg-white py-16 sm:py-20 lg:py-24">
    <div class="site-container">
        <div class="grid gap-10 lg:grid-cols-12 lg:gap-16">

            <div class="lg:col-span-5 lg:self-center">
                <?php if ($eyebrow !== '') : ?>
                    <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                        <?php echo esc_html($eyebrow); ?>
                    </p>
                <?php endif; ?>

                <?php if ($title !== '') : ?>
                    <<?php echo $heading; ?> class="mt-4 text-3xl font-black leading-[1.05] tracking-tight text-ink sm:text-4xl lg:text-5xl">
                        <?php echo esc_html($title); ?>
                    </<?php echo $heading; ?>>
                <?php endif; ?>

                <?php if ($text !== '') : ?>
                    <p class="mt-5 text-base leading-relaxed text-slate-700">
                        <?php echo esc_html($text); ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-7">
                <div class="rounded-lg bg-sand-50 p-6 ring-1 ring-slate-200 sm:p-8">
                    <?php get_template_part('template-parts/form', null, [
                        'slug'   => $slug,
                        'submit' => $submit,
                    ]); ?>

                    <?php if ($note !== '') : ?>
                        <p class="mt-5 text-xs leading-relaxed text-slate-600">
                            <?php echo esc_html($note); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
