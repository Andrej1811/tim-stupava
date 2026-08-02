<?php
/**
 * Contact block.
 *
 * The form and the contact details are one block rather than two, because on a
 * contact page they are one decision: a visitor either writes or calls, and
 * splitting them lets the client accidentally publish a page with a form and no
 * phone number, or the reverse.
 *
 * The details come from Nastavenia webu, so nothing here is typed twice.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Blocks\Contact;

if (!defined('ABSPATH')) {
    exit;
}

const NAME = 'ts-kontakt';

acf_register_block_type([
    'name'            => NAME,
    'api_version'     => 2,
    'title'           => __('Kontakt — formulár a údaje', 'nexdigital'),
    'description'     => __('Kontaktný formulár vedľa údajov z Nastavenia webu → Kontakt.', 'nexdigital'),
    'category'        => \NexDigital\Theme\Blocks\CATEGORY,
    'icon'            => 'email',
    'keywords'        => ['kontakt', 'formular', 'email'],
    'supports'        => [
        'align'  => false,
        'anchor' => true,
        'jsx'    => false,
    ],
    'render_callback' => __NAMESPACE__ . '\\render',
]);

function render(): void {
    // When the page hides its own title, this heading is the only candidate for
    // the h1 — a page with no h1 at all is worse than one whose h1 sits inside
    // a block. Everywhere else the page owns it and this stays an h2.
    //
    // The post ID is passed explicitly: inside a block render callback a bare
    // get_field() resolves against the block's own context, not the post the
    // block sits on, and silently returns nothing.
    $owns_heading = is_singular()
        && (bool) \NexDigital\Theme\Fields\field('hide_title', (int) get_the_ID());

    get_template_part('template-parts/contact-section', null, [
        'heading'      => $owns_heading ? 'h1' : 'h2',
        'eyebrow'      => (string) (get_field('eyebrow') ?: ''),
        'title'        => (string) (get_field('nadpis') ?: ''),
        'text'         => (string) (get_field('text') ?: ''),
        'form_title'   => (string) (get_field('nadpis_formulara') ?: ''),
        'submit'       => (string) (get_field('tlacidlo') ?: ''),
        'show_form'    => (bool) (get_field('zobrazit_formular') ?? true),
        'show_details' => (bool) (get_field('zobrazit_udaje') ?? true),
        'note'         => (string) (get_field('poznamka') ?: ''),
        'slug'         => \NexDigital\Theme\Forms\CONTACT,
    ]);
}

acf_add_local_field_group([
    'key'      => 'group_ts_block_kontakt',
    'title'    => __('Kontakt — nastavenia', 'nexdigital'),
    'active'   => true,
    'location' => [
        [
            ['param' => 'block', 'operator' => '==', 'value' => 'acf/' . NAME],
        ],
    ],
    'fields'   => [
        [
            'key'         => 'field_ts_kont_eyebrow',
            'label'       => __('Malý text nad nadpisom', 'nexdigital'),
            'name'        => 'eyebrow',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('Ozvite sa nám', 'nexdigital'),
        ],
        [
            'key'         => 'field_ts_kont_nadpis',
            'label'       => __('Nadpis', 'nexdigital'),
            'name'        => 'nadpis',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('Napíšte nám', 'nexdigital'),
        ],
        [
            'key'   => 'field_ts_kont_text',
            'label' => __('Text pod nadpisom', 'nexdigital'),
            'name'  => 'text',
            'type'  => 'textarea',
            'rows'  => 2,
        ],
        [
            'key'           => 'field_ts_kont_zobrazit_formular',
            'label'         => __('Zobraziť formulár', 'nexdigital'),
            'name'          => 'zobrazit_formular',
            'type'          => 'true_false',
            'ui'            => 1,
            'default_value' => 1,
            'wrapper'       => ['width' => '50'],
        ],
        [
            'key'           => 'field_ts_kont_zobrazit_udaje',
            'label'         => __('Zobraziť kontaktné údaje', 'nexdigital'),
            'name'          => 'zobrazit_udaje',
            'type'          => 'true_false',
            'ui'            => 1,
            'default_value' => 1,
            'wrapper'       => ['width' => '50'],
            'instructions'  => __('Údaje sa berú z Nastavenia webu → Kontakt. Prázdna položka sa nezobrazí.', 'nexdigital'),
        ],
        [
            'key'         => 'field_ts_kont_nadpis_formulara',
            'label'       => __('Nadpis nad formulárom', 'nexdigital'),
            'name'        => 'nadpis_formulara',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('Napíšte nám správu', 'nexdigital'),
        ],
        [
            'key'         => 'field_ts_kont_tlacidlo',
            'label'       => __('Text tlačidla', 'nexdigital'),
            'name'        => 'tlacidlo',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('Odoslať správu', 'nexdigital'),
        ],
        [
            'key'          => 'field_ts_kont_poznamka',
            'label'        => __('Poznámka pod formulárom', 'nexdigital'),
            'name'         => 'poznamka',
            'type'         => 'textarea',
            'rows'         => 2,
            'instructions' => __('Napríklad dokedy odpovedáte. Prázdne pole sa nezobrazí.', 'nexdigital'),
        ],
        [
            'key'      => 'field_ts_kont_info',
            'label'    => __('Odkiaľ sa berú údaje', 'nexdigital'),
            'name'     => '',
            'type'     => 'message',
            'esc_html' => 0,
            'message'  => __('<p>E-mail, telefón, adresa a sociálne siete sa berú z <strong>Nastavenia webu → Kontakt</strong> a <strong>Sociálne siete</strong>. Čo tam nie je vyplnené, sa na stránke nezobrazí.</p><p>Odoslané správy nájdete v menu <strong>Formuláre</strong>.</p>', 'nexdigital'),
        ],
    ],
]);
