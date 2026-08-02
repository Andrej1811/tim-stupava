<?php
/**
 * Donate block.
 *
 * The header's magenta button points at /podpora/, so this is where the ask
 * lands and it is the one page allowed to spend magenta on its primary button —
 * see the palette note in app.css. The account details come from Nastavenia
 * webu → Podpora a dary, so the IBAN is written once for the footer, this page
 * and anywhere else it appears.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Blocks\Support;

if (!defined('ABSPATH')) {
    exit;
}

const NAME = 'ts-podpora';

acf_register_block_type([
    'name'            => NAME,
    'api_version'     => 2,
    'title'           => __('Podpora — transparentný účet', 'nexdigital'),
    'description'     => __('Výzva na finančnú podporu s IBAN, QR kódom a rozpisom, na čo peniaze idú.', 'nexdigital'),
    'category'        => \NexDigital\Theme\Blocks\CATEGORY,
    'icon'            => 'heart',
    'keywords'        => ['podpora', 'dar', 'iban', 'ucet'],
    'supports'        => [
        'align'  => false,
        'anchor' => true,
        'jsx'    => false,
    ],
    'render_callback' => __NAMESPACE__ . '\\render',
]);

function render(): void {
    $uses = [];

    foreach ((array) (get_field('vyuzitie') ?: []) as $row) {
        $label = trim((string) ($row['suma'] ?? ''));
        $text = trim((string) ($row['popis'] ?? ''));

        if ($label === '' && $text === '') {
            continue;
        }

        $uses[] = ['amount' => $label, 'text' => $text];
    }

    $owns_heading = is_singular()
        && (bool) \NexDigital\Theme\Fields\field('hide_title', (int) get_the_ID());

    get_template_part('template-parts/support-section', null, [
        'heading' => $owns_heading ? 'h1' : 'h2',
        'eyebrow' => (string) (get_field('eyebrow') ?: ''),
        'title'   => (string) (get_field('nadpis') ?: ''),
        'text'    => (string) (get_field('text') ?: ''),
        'uses'    => $uses,
        'note'    => (string) (get_field('poznamka') ?: ''),
    ]);
}

acf_add_local_field_group([
    'key'      => 'group_ts_block_podpora',
    'title'    => __('Podpora — nastavenia', 'nexdigital'),
    'active'   => true,
    'location' => [
        [
            ['param' => 'block', 'operator' => '==', 'value' => 'acf/' . NAME],
        ],
    ],
    'fields'   => [
        [
            'key'         => 'field_ts_pod_eyebrow',
            'label'       => __('Malý text nad nadpisom', 'nexdigital'),
            'name'        => 'eyebrow',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('Transparentný účet', 'nexdigital'),
        ],
        [
            'key'         => 'field_ts_pod_nadpis',
            'label'       => __('Nadpis', 'nexdigital'),
            'name'        => 'nadpis',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('Kampaň platia ľudia zo Stupavy', 'nexdigital'),
        ],
        [
            'key'   => 'field_ts_pod_text',
            'label' => __('Text pod nadpisom', 'nexdigital'),
            'name'  => 'text',
            'type'  => 'textarea',
            'rows'  => 3,
        ],
        [
            'key'          => 'field_ts_pod_vyuzitie',
            'label'        => __('Na čo peniaze idú', 'nexdigital'),
            'name'         => 'vyuzitie',
            'type'         => 'repeater',
            'layout'       => 'table',
            'max'          => 4,
            'button_label' => __('Pridať položku', 'nexdigital'),
            'instructions' => __('Konkrétne sumy presvedčia viac než všeobecná výzva — darca vidí, čo jeho príspevok kúpi. Prázdny zoznam sa nezobrazí.', 'nexdigital'),
            'sub_fields'   => [
                [
                    'key'         => 'field_ts_pod_suma',
                    'label'       => __('Suma', 'nexdigital'),
                    'name'        => 'suma',
                    'type'        => 'text',
                    'wrapper'     => ['width' => '25'],
                    'placeholder' => '20 €',
                ],
                [
                    'key'         => 'field_ts_pod_popis',
                    'label'       => __('Čo za ňu bude', 'nexdigital'),
                    'name'        => 'popis',
                    'type'        => 'text',
                    'wrapper'     => ['width' => '75'],
                    'placeholder' => __('500 letákov do schránok', 'nexdigital'),
                ],
            ],
        ],
        [
            'key'          => 'field_ts_pod_poznamka',
            'label'        => __('Poznámka pod účtom', 'nexdigital'),
            'name'         => 'poznamka',
            'type'         => 'textarea',
            'rows'         => 2,
            'instructions' => __('Napríklad podmienky darovania podľa zákona o volebnej kampani.', 'nexdigital'),
        ],
        [
            'key'      => 'field_ts_pod_info',
            'label'    => __('Odkiaľ sa berú údaje', 'nexdigital'),
            'name'     => '',
            'type'     => 'message',
            'esc_html' => 0,
            'message'  => __('<p>IBAN, QR kód a odkaz na výpis z účtu sa berú z <strong>Nastavenia webu → Podpora a dary</strong>. Čo tam nie je vyplnené, sa nezobrazí.</p>', 'nexdigital'),
        ],
    ],
]);
