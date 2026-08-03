<?php
/**
 * "How to vote" block.
 *
 * Turnout information, kept editable because the date is set by the Speaker of
 * parliament and the polling details come from the town hall — neither is
 * something a template should hard-code.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Blocks\Vote;

if (!defined('ABSPATH')) {
    exit;
}

const NAME = 'ts-ako-volit';

acf_register_block_type([
    'name'            => NAME,
    'api_version'     => 2,
    'title'           => __('Ako a kedy voliť', 'nexdigital'),
    'description'     => __('Praktické informácie k voľbám — termín, čas, čo si vziať a ako krúžkovať.', 'nexdigital'),
    'category'        => \NexDigital\Theme\Blocks\CATEGORY,
    'icon'            => 'yes-alt',
    'keywords'        => ['volby', 'termin', 'ako volit'],
    'supports'        => [
        'align'    => false,
        'anchor'   => true,
        'jsx'      => false,
        'multiple' => false,
    ],
    'render_callback' => __NAMESPACE__ . '\\render',
]);

function render(): void {
    $items = [];

    foreach ((array) (get_field('body') ?: []) as $row) {
        $title = trim((string) ($row['nadpis'] ?? ''));

        if ($title === '') {
            continue;
        }

        $items[] = [
            'icon'  => (string) ($row['ikona'] ?? 'info'),
            'title' => $title,
            'text'  => trim((string) ($row['text'] ?? '')),
        ];
    }

    $link = get_field('tlacidlo');

    get_template_part('template-parts/vote-section', null, [
        'eyebrow' => (string) (get_field('eyebrow') ?: ''),
        'title'   => (string) (get_field('nadpis') ?: ''),
        'date'    => (string) (get_field('datum') ?: ''),
        'time'    => (string) (get_field('cas') ?: ''),
        'note'    => (string) (get_field('poznamka') ?: ''),
        'items'   => $items,
        'link'    => is_array($link) ? $link : null,
    ]);
}

acf_add_local_field_group([
    'key'      => 'group_ts_block_volby',
    'title'    => __('Ako a kedy voliť — nastavenia', 'nexdigital'),
    'active'   => true,
    'location' => [
        [
            ['param' => 'block', 'operator' => '==', 'value' => 'acf/' . NAME],
        ],
    ],
    'fields'   => [
        [
            'key'         => 'field_ts_volby_eyebrow',
            'label'       => __('Malý text nad nadpisom', 'nexdigital'),
            'name'        => 'eyebrow',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('Voľby 2026', 'nexdigital'),
        ],
        [
            'key'         => 'field_ts_volby_nadpis',
            'label'       => __('Nadpis', 'nexdigital'),
            'name'        => 'nadpis',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('Ako a kedy voliť', 'nexdigital'),
        ],
        [
            'key'          => 'field_ts_volby_datum',
            'label'        => __('Termín volieb', 'nexdigital'),
            'name'         => 'datum',
            'type'         => 'text',
            'wrapper'      => ['width' => '50'],
            'placeholder'  => __('sobota 24. októbra 2026', 'nexdigital'),
            'instructions' => __('Vyplň až vtedy, keď je termín oficiálne vyhlásený. Nesprávny dátum na kampaňovom webe je presne to, čo si všimnú.', 'nexdigital'),
        ],
        [
            'key'         => 'field_ts_volby_cas',
            'label'       => __('Čas', 'nexdigital'),
            'name'        => 'cas',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('volebné miestnosti otvorené 7:00 – 20:00', 'nexdigital'),
        ],
        [
            'key'          => 'field_ts_volby_body',
            'label'        => __('Praktické body', 'nexdigital'),
            'name'         => 'body',
            'type'         => 'repeater',
            'layout'       => 'block',
            'max'          => 6,
            'button_label' => __('Pridať bod', 'nexdigital'),
            'instructions' => __('Krátke odpovede na otázky, ktoré ľudia riešia deň pred voľbami. Zobrazujú sa v dvoch stĺpcoch.', 'nexdigital'),
            'sub_fields'   => [
                [
                    'key'           => 'field_ts_volby_bod_ikona',
                    'label'         => __('Ikona', 'nexdigital'),
                    'name'          => 'ikona',
                    'type'          => 'select',
                    'default_value' => 'info',
                    'choices'       => [
                        'map-pin'  => __('Miesto', 'nexdigital'),
                        'id-card'  => __('Doklad', 'nexdigital'),
                        'pencil'   => __('Krúžkovanie', 'nexdigital'),
                        'calendar' => __('Termín', 'nexdigital'),
                        'clock'    => __('Čas', 'nexdigital'),
                        'info'     => __('Informácia', 'nexdigital'),
                    ],
                    'wrapper'       => ['width' => '30'],
                ],
                [
                    'key'     => 'field_ts_volby_bod_nadpis',
                    'label'   => __('Nadpis bodu', 'nexdigital'),
                    'name'    => 'nadpis',
                    'type'    => 'text',
                    'wrapper' => ['width' => '70'],
                ],
                [
                    'key'   => 'field_ts_volby_bod_text',
                    'label' => __('Text', 'nexdigital'),
                    'name'  => 'text',
                    'type'  => 'textarea',
                    'rows'  => 2,
                ],
            ],
        ],
        [
            'key'          => 'field_ts_volby_poznamka',
            'label'        => __('Poznámka pod termínom', 'nexdigital'),
            'name'         => 'poznamka',
            'type'         => 'textarea',
            'rows'         => 3,
            'instructions' => __('Miesto na upozornenie, ktoré sa nezmestí do bodov — napríklad zmena volebnej miestnosti.', 'nexdigital'),
        ],
        [
            'key'          => 'field_ts_volby_tlacidlo',
            'label'        => __('Tlačidlo', 'nexdigital'),
            'name'         => 'tlacidlo',
            'type'         => 'link',
            'instructions' => __('Odkaz na voľby na stránke mesta: <code>https://www.stupava.sk/sk/samosprava/volby-referendum/</code> — mesto tam zverejňuje oznámenia k voľbám. Zoznam okrskov s adresami zverejní až po vyhlásení termínu; dovtedy je správnym cieľom táto rozcestníková stránka a text tlačidla by nemal sľubovať zoznam, ktorý ešte neexistuje.', 'nexdigital'),
        ],
    ],
]);
