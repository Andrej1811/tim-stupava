<?php
/**
 * Výsledky — the full record.
 *
 * The home page shows a handful of finished projects and links here; this is
 * where all of them live. The page's own editor content renders above the list,
 * so the client can write an intro without touching a template.
 *
 * Program and Výsledky are the same records either side of ts_stav = dokoncene,
 * so both pages share one template part and differ only in the flag they pass.
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();

while (have_posts()) {
    the_post();
    get_template_part('template-parts/projects-archive', null, ['done' => true]);
}

get_footer();
