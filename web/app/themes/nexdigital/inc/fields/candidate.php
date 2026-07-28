<?php
/**
 * Candidate fields.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Fields;

if (!defined('ABSPATH')) {
    exit;
}

acf_add_local_field_group([
    'key'                   => 'group_ts_kandidat',
    'title'                 => __('Údaje kandidáta', 'nexdigital'),
    'menu_order'            => 0,
    'position'              => 'normal',
    'style'                 => 'default',
    'label_placement'       => 'top',
    'active'                => true,
    'show_in_rest'          => false,
    'location'              => [
        [
            ['param' => 'post_type', 'operator' => '==', 'value' => 'kandidat'],
        ],
    ],
    'fields' => [
        [
            'key'          => 'field_ts_cislo',
            'label'        => __('Číslo na hlasovacom lístku', 'nexdigital'),
            'name'         => key('cislo'),
            'type'         => 'number',
            'required'     => 1,
            'min'          => 1,
            'instructions' => __('Poradové číslo, ktoré volič krúžkuje na lístku. Podľa neho sa kandidáti radia na webe — musí sedieť s oficiálnou kandidátkou.', 'nexdigital'),
            'wrapper'      => ['width' => '30'],
        ],
        [
            'key'          => 'field_ts_pozicia',
            'label'        => __('Pozícia / povolanie', 'nexdigital'),
            'name'         => key('pozicia'),
            'type'         => 'text',
            'instructions' => __('Napr. „primátor mesta Stupava“ alebo „učiteľka“. Zobrazuje sa pod menom.', 'nexdigital'),
            'wrapper'      => ['width' => '70'],
        ],
        [
            'key'          => 'field_ts_je_lider',
            'label'        => __('Kandidát na primátora', 'nexdigital'),
            'name'         => key('je_lider'),
            'type'         => 'true_false',
            'ui'           => 1,
            'instructions' => __('Zapni len u lídra kandidátky. Na úvodnej stránke dostane väčší profil.', 'nexdigital'),
        ],
        [
            'key'          => 'field_ts_kratke_bio',
            'label'        => __('Krátky popis', 'nexdigital'),
            'name'         => key('kratke_bio'),
            'type'         => 'textarea',
            'rows'         => 3,
            'maxlength'    => 220,
            'instructions' => __('Jedna až dve vety na kartu kandidáta v prehľade. Dlhší životopis patrí do hlavného obsahu nižšie.', 'nexdigital'),
        ],

        [
            'key'   => 'field_ts_kandidat_tab_foto',
            'label' => __('Fotografie', 'nexdigital'),
            'type'  => 'tab',
        ],
        [
            'key'          => 'field_ts_kandidat_foto_info',
            'label'        => '',
            'name'         => '',
            'type'         => 'message',
            'message'      => __('<strong>Hlavný obrázok</strong> (vpravo v postrannom paneli) sa používa ako štúdiový portrét na bielom pozadí — odporúčaný rozmer <strong>1200 × 1600 px</strong>, pomer 3:4 na výšku.', 'nexdigital'),
            'new_lines'    => '',
            'esc_html'     => 0,
        ],
        image_field(
            [
                'key'          => 'field_ts_foto_portret',
                'label'        => __('Fotografia v prostredí', 'nexdigital'),
                'name'         => key('foto_portret'),
                'instructions' => __('Fotka z parku alebo mesta, používa sa v hlavičke profilu kandidáta.', 'nexdigital'),
            ],
            '1800 × 1200 px',
            __('3:2 (na šírku)', 'nexdigital')
        ),
        [
            'key'          => 'field_ts_video',
            'label'        => __('Video-vizitka', 'nexdigital'),
            'name'         => key('video'),
            'type'         => 'oembed',
            'instructions' => __('Vlož odkaz na YouTube alebo Vimeo. Prehrávač sa doplní automaticky.', 'nexdigital'),
        ],

        [
            'key'   => 'field_ts_kandidat_tab_kontakt',
            'label' => __('Kontakt', 'nexdigital'),
            'type'  => 'tab',
        ],
        [
            'key'     => 'field_ts_kandidat_email',
            'label'   => __('E-mail', 'nexdigital'),
            'name'    => key('email'),
            'type'    => 'email',
            'wrapper' => ['width' => '50'],
        ],
        [
            'key'     => 'field_ts_kandidat_fb',
            'label'   => __('Facebook', 'nexdigital'),
            'name'    => key('facebook'),
            'type'    => 'url',
            'wrapper' => ['width' => '50'],
        ],
    ],
]);
