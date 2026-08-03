<?php
/**
 * Front page template.
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();
?>

<?php

while (have_posts()) :
    the_post();
    the_content();
endwhile;

get_footer();
