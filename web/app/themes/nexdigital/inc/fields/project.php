<?php
/**
 * Project fields.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Fields;

use function NexDigital\Theme\PostTypes\project_stages;

if (!defined('ABSPATH')) {
    exit;
}

acf_add_local_field_group([
    'key'             => 'group_ts_projekt',
    'title'           => __('Údaje projektu', 'nexdigital'),
    'menu_order'      => 0,
    'position'        => 'normal',
    'style'           => 'default',
    'label_placement' => 'top',
    'active'          => true,
    'show_in_rest'    => false,
    'location'        => [
        [
            ['param' => 'post_type', 'operator' => '==', 'value' => 'projekt'],
        ],
    ],
    'fields' => [
        [
            'key'          => 'field_ts_stav',
            'label'        => __('Stav projektu', 'nexdigital'),
            'name'         => key('stav'),
            'type'         => 'select',
            'required'     => 1,
            'choices'      => project_stages(),
            'default_value' => 'priprava',
            'return_format' => 'value',
            'instructions' => __('Toto pole rozhoduje, kde sa projekt zobrazí. „Dokončené“ ho presunie na stránku Výsledky, každý iný stav ho drží v Programe. Stav je zároveň najsilnejší argument — ukazuje, že projekt nie je len sľub.', 'nexdigital'),
            'wrapper'      => ['width' => '50'],
        ],
        [
            'key'          => 'field_ts_je_hlavna_tema',
            'label'        => __('Hlavná téma', 'nexdigital'),
            'name'         => key('je_hlavna_tema'),
            'type'         => 'true_false',
            'ui'           => 1,
            'instructions' => __('Veľké projekty (terminál, zdravotné stredisko…). Zobrazia sa väčšie a vyššie než menšie celomestské témy.', 'nexdigital'),
            'wrapper'      => ['width' => '50'],
        ],
        [
            'key'          => 'field_ts_cena',
            'label'        => __('Rozpočet', 'nexdigital'),
            'name'         => key('cena'),
            'type'         => 'text',
            'placeholder'  => '5 089 423,05 €',
            'instructions' => __('Vrátane meny. Nechaj prázdne, ak suma ešte nie je známa — nezobrazí sa nič.', 'nexdigital'),
            'wrapper'      => ['width' => '50'],
        ],
        [
            'key'          => 'field_ts_termin',
            'label'        => __('Predpokladaný termín', 'nexdigital'),
            'name'         => key('termin'),
            'type'         => 'text',
            'placeholder'  => __('začiatok 2027, trvanie 12 mesiacov', 'nexdigital'),
            'instructions' => __('Voľný text — píš tak, ako to vieš doložiť.', 'nexdigital'),
            'wrapper'      => ['width' => '50'],
        ],
        [
            'key'          => 'field_ts_zdroj',
            'label'        => __('Zdroj financovania', 'nexdigital'),
            'name'         => key('zdroj'),
            'type'         => 'text',
            'placeholder'  => __('fondy EÚ a štátny rozpočet', 'nexdigital'),
            'instructions' => __('Kto to platí. Pri projektoch z eurofondov to stojí za uvedenie.', 'nexdigital'),
        ],

        [
            'key'   => 'field_ts_projekt_tab_media',
            'label' => __('Vizualizácie', 'nexdigital'),
            'type'  => 'tab',
        ],
        [
            'key'          => 'field_ts_projekt_media_info',
            'label'        => '',
            'name'         => '',
            'type'         => 'message',
            'message'      => __('<strong>Hlavný obrázok</strong> (v postrannom paneli) je náhľad projektu v zoznamoch — odporúčaný rozmer <strong>1600 × 900 px</strong>, pomer 16:9.', 'nexdigital'),
            'new_lines'    => '',
            'esc_html'     => 0,
        ],
        [
            'key'           => 'field_ts_galeria',
            'label'         => __('Galéria vizualizácií', 'nexdigital'),
            'name'          => key('galeria'),
            'type'          => 'gallery',
            'return_format' => 'id',
            'preview_size'  => 'medium',
            'mime_types'    => 'jpg,jpeg,png,webp',
            'instructions'  => __('Vizualizácie a situačné výkresy. Odporúčaný rozmer 1920 × 1080 px, pomer 16:9 na šírku. Formát JPG alebo WebP, ideálne do 500 kB na obrázok. Ak je podklad PDF, vyexportuj z neho obrázok — PDF sa v galérii nezobrazí.', 'nexdigital'),
        ],
    ],
]);
