<?php
/**
 * Program — everything still in flight.
 *
 * The mirror of page-vysledky.php: same records, the other side of
 * ts_stav = dokoncene. See template-parts/projects-archive.php.
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();

while (have_posts()) {
    the_post();
    get_template_part('template-parts/projects-archive', null, ['done' => false]);
}

get_footer();
