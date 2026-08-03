<?php
/**
 * Candidates block.
 *
 * Pulls the kandidat CPT, ordered by ballot number, and renders each record
 * through template-parts/candidate-card.php. Which candidate gets the wide card
 * is not a block setting: it is the "Kandidát na primátora" switch on the
 * candidate itself, so the two places that show a leader can never disagree.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Blocks\Candidates;

if (!defined('ABSPATH')) {
    exit;
}

const NAME = 'ts-kandidati';

acf_register_block_type([
    'name'            => NAME,
    'api_version'     => 2,
    'title'           => __('Kandidáti', 'nexdigital'),
    'description'     => __('Mriežka kandidátov z ich profilov. Líder dostane veľkú kartu s video-vizitkou.', 'nexdigital'),
    'category'        => \NexDigital\Theme\Blocks\CATEGORY,
    'icon'            => 'groups',
    'keywords'        => ['kandidati', 'tim', 'ludia'],
    'supports'        => [
        'align'  => false,
        'anchor' => true,
        'jsx'    => false,
    ],
    'render_callback' => __NAMESPACE__ . '\\render',
]);

/**
 * Candidates in ballot order.
 *
 * @return array<int, \WP_Post>
 */
function candidates(int $limit): array {
    $query = new \WP_Query([
        'post_type'      => 'kandidat',
        'post_status'    => 'publish',
        'posts_per_page' => $limit > 0 ? $limit : -1,
        'meta_key'       => 'ts_cislo',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    ]);

    return $query->posts;
}

function render(): void {
    $limit = (int) (get_field('pocet') ?: 0);
    $posts = candidates($limit);

    // Whether the grid is actually cut short. The "all candidates" button is
    // only worth showing when there is something the visitor cannot see here.
    $total = (int) wp_count_posts('kandidat')->publish;
    $truncated = $limit > 0 && $total > count($posts);

    if ($posts === []) {
        // Nothing published yet: an empty grid with a heading would read as a
        // broken page, so the section stays out of the document entirely.
        return;
    }

    $leaders = [];
    $rest = [];

    foreach ($posts as $post) {
        if (get_field('ts_je_lider', $post->ID)) {
            $leaders[] = $post;

            continue;
        }

        $rest[] = $post;
    }

    $link = get_field('tlacidlo');

    get_template_part('template-parts/candidates-section', null, [
        'eyebrow'   => (string) (get_field('eyebrow') ?: ''),
        'title'     => (string) (get_field('nadpis') ?: ''),
        'text'      => (string) (get_field('text') ?: ''),
        'leaders'   => $leaders,
        'rest'      => $rest,
        'link'      => is_array($link) ? $link : null,
        'truncated' => $truncated,
    ]);
}

acf_add_local_field_group([
    'key'      => 'group_ts_block_kandidati',
    'title'    => __('Kandidáti — nastavenia', 'nexdigital'),
    'active'   => true,
    'location' => [
        [
            ['param' => 'block', 'operator' => '==', 'value' => 'acf/' . NAME],
        ],
    ],
    'fields'   => [
        [
            'key'          => 'field_ts_kand_eyebrow',
            'label'        => __('Malý text nad nadpisom', 'nexdigital'),
            'name'         => 'eyebrow',
            'type'         => 'text',
            'placeholder'  => __('Kandidáti do mestského zastupiteľstva', 'nexdigital'),
            'wrapper'      => ['width' => '50'],
        ],
        [
            'key'          => 'field_ts_kand_nadpis',
            'label'        => __('Nadpis', 'nexdigital'),
            'name'         => 'nadpis',
            'type'         => 'text',
            'placeholder'  => __('Náš tím', 'nexdigital'),
            'wrapper'      => ['width' => '50'],
        ],
        [
            'key'          => 'field_ts_kand_text',
            'label'        => __('Text pod nadpisom', 'nexdigital'),
            'name'         => 'text',
            'type'         => 'textarea',
            'rows'         => 2,
        ],
        [
            'key'          => 'field_ts_kand_pocet',
            'label'        => __('Koľko kandidátov zobraziť', 'nexdigital'),
            'name'         => 'pocet',
            'type'         => 'number',
            'min'          => 0,
            'default_value' => 0,
            'wrapper'      => ['width' => '30'],
            'instructions' => __('0 = všetci. Kandidáti sa vždy radia podľa čísla na hlasovacom lístku.', 'nexdigital'),
        ],
        [
            'key'          => 'field_ts_kand_tlacidlo',
            'label'        => __('Tlačidlo pod mriežkou', 'nexdigital'),
            'name'         => 'tlacidlo',
            'type'         => 'link',
            'wrapper'      => ['width' => '70'],
            'instructions' => __('Napríklad odkaz na stránku so všetkými kandidátmi. Zobrazí sa len vtedy, keď je hore nastavený počet a niektorí kandidáti sa na úvodnú stránku nezmestia — inak by viedlo na tie isté tváre.', 'nexdigital'),
        ],
        [
            'key'      => 'field_ts_kand_info',
            'label'    => __('Odkiaľ sa berú údaje', 'nexdigital'),
            'name'     => '',
            'type'     => 'message',
            'esc_html' => 0,
            'message'  => __('<p>Karty sa ťahajú z profilov v menu <strong>Kandidáti</strong> — meno, fotka, pozícia, krátky popis aj číslo na hlasovacom lístku sa upravujú tam.</p><p>Kto dostane <strong>veľkú kartu s video-vizitkou</strong>, určuje prepínač <em>Kandidát na primátora</em> priamo na profile kandidáta. Video sa načíta z YouTube až po kliknutí na tlačidlo prehrávania.</p>', 'nexdigital'),
        ],
    ],
]);
