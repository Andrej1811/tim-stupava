<?php
/**
 * Page fields.
 *
 * Replaces the CMB2 "Page Settings" box the skeleton shipped with.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Fields;

if (!defined('ABSPATH')) {
    exit;
}

acf_add_local_field_group([
    'key'             => 'group_ts_stranka',
    'title'           => __('Nastavenia stránky', 'nexdigital'),
    'menu_order'      => 10,
    'position'        => 'side',
    'style'           => 'default',
    'label_placement' => 'top',
    'active'          => true,
    'show_in_rest'    => false,
    'location'        => [
        [
            ['param' => 'post_type', 'operator' => '==', 'value' => 'page'],
        ],
    ],
    'fields' => [
        [
            'key'          => 'field_ts_subtitle',
            'label'        => __('Podnadpis', 'nexdigital'),
            'name'         => key('subtitle'),
            'type'         => 'text',
            'instructions' => __('Zobrazí sa pod názvom stránky. Nechaj prázdne, ak ho nechceš.', 'nexdigital'),
        ],
        [
            'key'          => 'field_ts_hide_title',
            'label'        => __('Skryť názov stránky', 'nexdigital'),
            'name'         => key('hide_title'),
            'type'         => 'true_false',
            'ui'           => 1,
            'instructions' => __('Zapni, ak si názov riešiš vo vlastnom obsahu.', 'nexdigital'),
        ],
    ],
]);
