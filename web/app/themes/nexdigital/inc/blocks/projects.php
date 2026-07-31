<?php
/**
 * Projects block — Program or Výsledky.
 *
 * One block with a mode rather than two, because Program and Výsledky are the
 * same projekt records at different stages of the same permitting pipeline. The
 * mode only decides which side of ts_stav = dokoncene the query looks at.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Blocks\Projects;

use function NexDigital\Theme\PostTypes\projects;

if (!defined('ABSPATH')) {
    exit;
}

const NAME = 'ts-projekty';

acf_register_block_type([
    'name'            => NAME,
    'api_version'     => 2,
    'title'           => __('Projekty — program alebo výsledky', 'nexdigital'),
    'description'     => __('Projekty z ich vlastných profilov. Režim rozhoduje, či sa zobrazia rozpracované (program) alebo dokončené (výsledky).', 'nexdigital'),
    'category'        => \NexDigital\Theme\Blocks\CATEGORY,
    'icon'            => 'hammer',
    'keywords'        => ['program', 'vysledky', 'projekty'],
    'supports'        => [
        'align'  => false,
        'anchor' => true,
        'jsx'    => false,
    ],
    'render_callback' => __NAMESPACE__ . '\\render',
]);

function render(): void {
    $done = (get_field('rezim') ?: 'program') === 'vysledky';
    $limit = (int) (get_field('pocet_hlavnych') ?: 0);
    $rest_limit = (int) (get_field('pocet_ostatnych') ?: 0);

    $query = projects($done);
    $features = [];
    $rest = [];

    foreach ($query->posts as $project) {
        if (get_field('ts_je_hlavna_tema', $project->ID) && ($limit === 0 || count($features) < $limit)) {
            $features[] = $project;

            continue;
        }

        $rest[] = $project;
    }

    // A four-year record runs to dozens of entries. Listing all of them on the
    // home page buries the big ones under the automobiles; the full list lives
    // on /vysledky/ and /program/, which the button below points at.
    $hidden = 0;

    if ($rest_limit > 0 && count($rest) > $rest_limit) {
        $hidden = count($rest) - $rest_limit;
        $rest = array_slice($rest, 0, $rest_limit);
    }

    $numbers = [];

    foreach ((array) (get_field('cisla') ?: []) as $row) {
        $value = trim((string) ($row['hodnota'] ?? ''));

        if ($value === '') {
            continue;
        }

        $numbers[] = ['value' => $value, 'label' => trim((string) ($row['popis'] ?? ''))];
    }

    if ($features === [] && $rest === [] && $numbers === []) {
        // Výsledky before the client has marked anything as done: render
        // nothing rather than a heading over an empty page.
        return;
    }

    $link = get_field('tlacidlo');

    get_template_part('template-parts/projects-section', null, [
        'eyebrow'  => (string) (get_field('eyebrow') ?: ''),
        'title'    => (string) (get_field('nadpis') ?: ''),
        'text'     => (string) (get_field('text') ?: ''),
        'mode'     => $done ? 'vysledky' : 'program',
        'features' => $features,
        'rest'     => $rest,
        'numbers'  => $numbers,
        'hidden'   => $hidden,
        'link'     => is_array($link) ? $link : null,
    ]);
}

acf_add_local_field_group([
    'key'      => 'group_ts_block_projekty',
    'title'    => __('Projekty — nastavenia', 'nexdigital'),
    'active'   => true,
    'location' => [
        [
            ['param' => 'block', 'operator' => '==', 'value' => 'acf/' . NAME],
        ],
    ],
    'fields'   => [
        [
            'key'           => 'field_ts_proj_rezim',
            'label'         => __('Čo sa má zobraziť', 'nexdigital'),
            'name'          => 'rezim',
            'type'          => 'select',
            'default_value' => 'program',
            'choices'       => [
                'program'  => __('Program — projekty, ktoré ešte bežia', 'nexdigital'),
                'vysledky' => __('Výsledky — projekty označené ako dokončené', 'nexdigital'),
            ],
            'wrapper'       => ['width' => '50'],
            'instructions'  => __('Rozdeľuje ich pole „Stav projektu“ na profile projektu. Nič sa nikde nezadáva dvakrát.', 'nexdigital'),
        ],
        [
            'key'           => 'field_ts_proj_pocet',
            'label'         => __('Koľko veľkých kariet', 'nexdigital'),
            'name'          => 'pocet_hlavnych',
            'type'          => 'number',
            'min'           => 0,
            'default_value' => 0,
            'wrapper'       => ['width' => '50'],
            'instructions'  => __('Veľkú kartu s obrázkom dostanú projekty označené ako „Hlavná téma“. 0 = všetky hlavné témy; ostatné sa vypíšu v zozname pod nimi.', 'nexdigital'),
        ],
        [
            'key'           => 'field_ts_proj_pocet_ostatnych',
            'label'         => __('Koľko položiek v zozname', 'nexdigital'),
            'name'          => 'pocet_ostatnych',
            'type'          => 'number',
            'min'           => 0,
            'default_value' => 0,
            'wrapper'       => ['width' => '50'],
            'instructions'  => __('Koľko ďalších projektov sa vypíše pod veľkými kartami. 0 = všetky. Na úvodnej stránke sa oplatí obmedziť — štvorročný odpočet má vyše dvadsať položiek a tie veľké by sa v ňom stratili. Pod skráteným zoznamom sa objaví poznámka, koľko projektov ešte je, takže sa nezdá, že je to všetko.', 'nexdigital'),
        ],
        [
            'key'         => 'field_ts_proj_eyebrow',
            'label'       => __('Malý text nad nadpisom', 'nexdigital'),
            'name'        => 'eyebrow',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('Náš program pre roky 2026 – 2030', 'nexdigital'),
        ],
        [
            'key'         => 'field_ts_proj_nadpis',
            'label'       => __('Nadpis', 'nexdigital'),
            'name'        => 'nadpis',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('Čo chceme dokončiť', 'nexdigital'),
        ],
        [
            'key'   => 'field_ts_proj_text',
            'label' => __('Text pod nadpisom', 'nexdigital'),
            'name'  => 'text',
            'type'  => 'textarea',
            'rows'  => 2,
        ],
        [
            'key'          => 'field_ts_proj_cisla',
            'label'        => __('Čísla nad projektmi', 'nexdigital'),
            'name'         => 'cisla',
            'type'         => 'repeater',
            'layout'       => 'table',
            'max'          => 4,
            'button_label' => __('Pridať číslo', 'nexdigital'),
            'instructions' => __('Zhrnutie v číslach — hodí sa najmä k výsledkom. Napríklad „5,1 mil. €“ a „získaných z fondov EÚ“. Prázdny zoznam sa nezobrazí.', 'nexdigital'),
            'sub_fields'   => [
                [
                    'key'         => 'field_ts_proj_cislo_hodnota',
                    'label'       => __('Číslo', 'nexdigital'),
                    'name'        => 'hodnota',
                    'type'        => 'text',
                    'wrapper'     => ['width' => '30'],
                    'placeholder' => '5,1 mil. €',
                ],
                [
                    'key'         => 'field_ts_proj_cislo_popis',
                    'label'       => __('Čo to znamená', 'nexdigital'),
                    'name'        => 'popis',
                    'type'        => 'text',
                    'wrapper'     => ['width' => '70'],
                    'placeholder' => __('získaných z fondov EÚ a štátneho rozpočtu', 'nexdigital'),
                ],
            ],
        ],
        [
            'key'          => 'field_ts_proj_tlacidlo',
            'label'        => __('Tlačidlo pod projektmi', 'nexdigital'),
            'name'         => 'tlacidlo',
            'type'         => 'link',
            'instructions' => __('Napríklad odkaz na celý program alebo na PDF na stiahnutie.', 'nexdigital'),
        ],
        [
            'key'      => 'field_ts_proj_info',
            'label'    => __('Odkiaľ sa berú údaje', 'nexdigital'),
            'name'     => '',
            'type'     => 'message',
            'esc_html' => 0,
            'message'  => __('<p>Projekty sa upravujú v menu <strong>Projekty</strong>. Rozhodujúce je pole <em>Stav projektu</em>: „Dokončené“ presunie projekt z Programu do Výsledkov, kdekoľvek na webe.</p><p>Veľkú kartu s vizualizáciou dostanú projekty s prepínačom <em>Hlavná téma</em>. Poradie určuje pole <em>Poradie</em> (atribúty stránky) na profile projektu.</p>', 'nexdigital'),
        ],
    ],
]);
