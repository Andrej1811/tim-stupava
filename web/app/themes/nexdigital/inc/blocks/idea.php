<?php
/**
 * Resident-ideas block.
 *
 * The client's brief put a prompt on the front page — "Máte nejaké otázky
 * alebo návrhy čo zlepšiť v obci?" — and this is that prompt with the
 * napad-obyvatela form beside it. The form itself has been defined in
 * inc/forms.php since the contact work; this block is where it finally
 * renders.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Blocks\Idea;

use const NexDigital\Theme\Forms\IDEA;

if (!defined('ABSPATH')) {
    exit;
}

const NAME = 'ts-napad';

acf_register_block_type([
    'name'            => NAME,
    'api_version'     => 2,
    'title'           => __('Nápad obyvateľa — formulár', 'nexdigital'),
    'description'     => __('Výzva s formulárom na námety obyvateľov.', 'nexdigital'),
    'category'        => \NexDigital\Theme\Blocks\CATEGORY,
    'icon'            => 'lightbulb',
    'keywords'        => ['napad', 'formular', 'navrh'],
    'supports'        => [
        'align'  => false,
        'anchor' => true,
        'jsx'    => false,
    ],
    'render_callback' => __NAMESPACE__ . '\\render',
]);

function render(): void {
    // Same h1 rule as the contact block: when the page hides its own title,
    // the section heading is the page's only candidate for the h1. The post
    // ID is passed explicitly — inside a block render callback a bare
    // get_field() resolves against the block, not the post.
    $owns_heading = is_singular()
        && (bool) \NexDigital\Theme\Fields\field('hide_title', (int) get_the_ID());

    get_template_part('template-parts/idea-section', null, [
        'heading' => $owns_heading ? 'h1' : 'h2',
        'eyebrow' => (string) (get_field('eyebrow') ?: ''),
        'title'   => (string) (get_field('nadpis') ?: ''),
        'text'    => (string) (get_field('text') ?: ''),
        'submit'  => (string) (get_field('tlacidlo') ?: ''),
        'note'    => (string) (get_field('poznamka') ?: ''),
        'slug'    => IDEA,
    ]);
}

acf_add_local_field_group([
    'key'      => 'group_ts_block_napad',
    'title'    => __('Nápad obyvateľa — nastavenia', 'nexdigital'),
    'active'   => true,
    'location' => [
        [
            ['param' => 'block', 'operator' => '==', 'value' => 'acf/' . NAME],
        ],
    ],
    'fields'   => [
        [
            'key'         => 'field_ts_napad_eyebrow',
            'label'       => __('Malý text nad nadpisom', 'nexdigital'),
            'name'        => 'eyebrow',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('Zapojte sa', 'nexdigital'),
        ],
        [
            'key'         => 'field_ts_napad_nadpis',
            'label'       => __('Nadpis', 'nexdigital'),
            'name'        => 'nadpis',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('Máte nápad, ako zlepšiť Stupavu?', 'nexdigital'),
        ],
        [
            'key'   => 'field_ts_napad_text',
            'label' => __('Text pod nadpisom', 'nexdigital'),
            'name'  => 'text',
            'type'  => 'textarea',
            'rows'  => 3,
        ],
        [
            'key'         => 'field_ts_napad_tlacidlo',
            'label'       => __('Text tlačidla', 'nexdigital'),
            'name'        => 'tlacidlo',
            'type'        => 'text',
            'wrapper'     => ['width' => '50'],
            'placeholder' => __('Odoslať nápad', 'nexdigital'),
        ],
        [
            'key'          => 'field_ts_napad_poznamka',
            'label'        => __('Poznámka pod formulárom', 'nexdigital'),
            'name'         => 'poznamka',
            'type'         => 'textarea',
            'rows'         => 2,
            'wrapper'      => ['width' => '50'],
            'instructions' => __('Prázdne pole sa nezobrazí.', 'nexdigital'),
        ],
        [
            'key'      => 'field_ts_napad_info',
            'label'    => __('Kam chodia námety', 'nexdigital'),
            'name'     => '',
            'type'     => 'message',
            'esc_html' => 0,
            'message'  => __('<p>Odoslané námety nájdete v menu <strong>Formuláre</strong>; notifikácia chodí na e-mail z <strong>Nastavenia webu → Kontakt</strong>.</p>', 'nexdigital'),
        ],
    ],
]);
