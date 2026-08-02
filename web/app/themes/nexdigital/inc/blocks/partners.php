<?php
/**
 * Partners and endorsements block.
 *
 * The other half of "podpora": who stands behind the campaign, as opposed to
 * who pays for it. Logos come from Nastavenia webu → Partneri, so the footer
 * strip and this page never disagree about the coalition.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Blocks\Partners;

use function NexDigital\Theme\Fields\option;

if (!defined('ABSPATH')) {
    exit;
}

const NAME = 'ts-partneri';

acf_register_block_type([
    'name'            => NAME,
    'api_version'     => 2,
    'title'           => __('Podporovatelia — logá a vyjadrenia', 'nexdigital'),
    'description'     => __('Kto kampaň podporuje: logá partnerov z nastavení a osobné vyjadrenia.', 'nexdigital'),
    'category'        => \NexDigital\Theme\Blocks\CATEGORY,
    'icon'            => 'groups',
    'keywords'        => ['partneri', 'podpora', 'koalicia'],
    'supports'        => [
        'align'  => false,
        'anchor' => true,
        'jsx'    => false,
    ],
    'render_callback' => __NAMESPACE__ . '\\render',
]);

function render(): void {
    $partners = [];

    foreach ((array) (option('opt_partneri') ?: []) as $row) {
        $name = trim((string) ($row['nazov'] ?? ''));
        $logo = (int) ($row['logo'] ?? 0);

        if ($name === '' && $logo === 0) {
            continue;
        }

        $partners[] = [
            'name' => $name,
            'logo' => $logo,
            'url'  => trim((string) ($row['url'] ?? '')),
        ];
    }

    $quotes = [];

    foreach ((array) (get_field('vyjadrenia') ?: []) as $row) {
        $quote = trim((string) ($row['citat'] ?? ''));

        if ($quote === '') {
            continue;
        }

        $quotes[] = [
            'quote' => $quote,
            'name'  => trim((string) ($row['meno'] ?? '')),
            'role'  => trim((string) ($row['funkcia'] ?? '')),
            'photo' => (int) ($row['foto'] ?? 0),
        ];
    }

    get_template_part('template-parts/partners-section', null, [
        'eyebrow'  => (string) (get_field('eyebrow') ?: ''),
        'title'    => (string) (get_field('nadpis') ?: ''),
        'text'     => (string) (get_field('text') ?: ''),
        'partners' => $partners,
        'quotes'   => $quotes,
    ]);
}

acf_add_local_field_group([
    'key'      => 'group_ts_block_partneri',
    'title'    => __('Podporovatelia — nastavenia', 'nexdigital'),
    'active'   => true,
    'location' => [
        [
            ['param' => 'block', 'operator' => '==', 'value' => 'acf/' . NAME],
        ],
    ],
    'fields'   => [
        [
            'key'         => 'field_ts_par_eyebrow',
            'label'       => __('Malý text nad nadpisom', 'nexdigital'),
            'name'        => 'eyebrow',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('Kto stojí za nami', 'nexdigital'),
        ],
        [
            'key'         => 'field_ts_par_nadpis',
            'label'       => __('Nadpis', 'nexdigital'),
            'name'        => 'nadpis',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('Podporujú nás', 'nexdigital'),
        ],
        [
            'key'   => 'field_ts_par_text',
            'label' => __('Text pod nadpisom', 'nexdigital'),
            'name'  => 'text',
            'type'  => 'textarea',
            'rows'  => 2,
        ],
        [
            'key'          => 'field_ts_par_vyjadrenia',
            'label'        => __('Osobné vyjadrenia', 'nexdigital'),
            'name'         => 'vyjadrenia',
            'type'         => 'repeater',
            'layout'       => 'row',
            'button_label' => __('Pridať vyjadrenie', 'nexdigital'),
            'instructions' => __('Citát od človeka, ktorý kampaň verejne podporuje. <strong>Pridávaj sem len vyjadrenia, ktoré ti dotyčný naozaj dal a schválil</strong> — je to výrok pripísaný konkrétnej osobe. Prázdny zoznam sa nezobrazí.', 'nexdigital'),
            'sub_fields'   => [
                [
                    'key'      => 'field_ts_par_citat',
                    'label'    => __('Citát', 'nexdigital'),
                    'name'     => 'citat',
                    'type'     => 'textarea',
                    'rows'     => 3,
                    'required' => 1,
                ],
                [
                    'key'      => 'field_ts_par_meno',
                    'label'    => __('Meno', 'nexdigital'),
                    'name'     => 'meno',
                    'type'     => 'text',
                    'wrapper'  => ['width' => '50'],
                    'required' => 1,
                ],
                [
                    'key'     => 'field_ts_par_funkcia',
                    'label'   => __('Kto to je', 'nexdigital'),
                    'name'    => 'funkcia',
                    'type'    => 'text',
                    'wrapper' => ['width' => '50'],
                    'placeholder' => __('napr. riaditeľka ZŠ, podnikateľ zo Stupavy', 'nexdigital'),
                ],
                \NexDigital\Theme\Fields\image_field(
                    [
                        'key'   => 'field_ts_par_foto',
                        'label' => __('Fotografia', 'nexdigital'),
                        'name'  => 'foto',
                    ],
                    '400 × 400 px',
                    __('1:1 (štvorec)', 'nexdigital')
                ),
            ],
        ],
        [
            'key'      => 'field_ts_par_info',
            'label'    => __('Odkiaľ sa berú logá', 'nexdigital'),
            'name'     => '',
            'type'     => 'message',
            'esc_html' => 0,
            'message'  => __('<p>Logá podporovateľov sa berú z <strong>Nastavenia webu → Partneri</strong>, takže sú rovnaké tu aj v pätičke. Prázdny zoznam znamená, že sa pás s logami nezobrazí.</p>', 'nexdigital'),
        ],
    ],
]);
