<?php
/**
 * Theme options fields.
 *
 * One field group per options sub-page. Splitting by subject rather than
 * stacking tabs on a single screen means the client finds settings by the name
 * of the thing they want to change.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Fields;

if (!defined('ABSPATH')) {
    exit;
}

/** Location rule helper for an options sub-page. */
function on_options_page(string $slug): array {
    return [
        [
            ['param' => 'options_page', 'operator' => '==', 'value' => $slug],
        ],
    ];
}

/* -------------------------------------------------------------------------
   Kontakt
   ------------------------------------------------------------------------- */
acf_add_local_field_group([
    'key'      => 'group_ts_opt_kontakt',
    'title'    => __('Kontaktné údaje', 'nexdigital'),
    'active'   => true,
    'location' => on_options_page('nastavenia-kontakt'),
    'fields'   => [
        [
            'key'          => 'field_ts_opt_email',
            'label'        => __('E-mail', 'nexdigital'),
            'name'         => key('opt_email'),
            'type'         => 'email',
            'instructions' => __('Zobrazuje sa v pätičke a na stránke Kontakt. Sem chodia aj správy z formulárov, ak nie je nastavené inak.', 'nexdigital'),
            'wrapper'      => ['width' => '50'],
        ],
        [
            'key'     => 'field_ts_opt_telefon',
            'label'   => __('Telefón', 'nexdigital'),
            'name'    => key('opt_telefon'),
            'type'    => 'text',
            'wrapper' => ['width' => '50'],
        ],
        [
            'key'          => 'field_ts_opt_adresa',
            'label'        => __('Adresa', 'nexdigital'),
            'name'         => key('opt_adresa'),
            'type'         => 'textarea',
            'rows'         => 3,
            'instructions' => __('Každý riadok sa zobrazí zvlášť.', 'nexdigital'),
        ],
        [
            'key'          => 'field_ts_opt_zodpovedna_osoba',
            'label'        => __('Zadávateľ kampane', 'nexdigital'),
            'name'         => key('opt_zadavatel'),
            'type'         => 'text',
            'instructions' => __('Povinný údaj podľa zákona o volebnej kampani — zobrazuje sa v pätičke. Napr. meno kandidáta alebo názov politickej strany a IČO.', 'nexdigital'),
        ],
    ],
]);

/* -------------------------------------------------------------------------
   Sociálne siete
   ------------------------------------------------------------------------- */
acf_add_local_field_group([
    'key'      => 'group_ts_opt_socialne',
    'title'    => __('Sociálne siete', 'nexdigital'),
    'active'   => true,
    'location' => on_options_page('nastavenia-socialne'),
    'fields'   => [
        [
            'key'          => 'field_ts_opt_socialne',
            'label'        => __('Profily', 'nexdigital'),
            'name'         => key('opt_socialne'),
            'type'         => 'repeater',
            'layout'       => 'table',
            'button_label' => __('Pridať profil', 'nexdigital'),
            'instructions' => __('Prázdny zoznam znamená, že sa v pätičke nezobrazia žiadne ikony.', 'nexdigital'),
            'sub_fields'   => [
                [
                    'key'     => 'field_ts_opt_soc_siet',
                    'label'   => __('Sieť', 'nexdigital'),
                    'name'    => 'siet',
                    'type'    => 'select',
                    'choices' => [
                        'facebook'  => 'Facebook',
                        'instagram' => 'Instagram',
                        'youtube'   => 'YouTube',
                        'tiktok'    => 'TikTok',
                        'linkedin'  => 'LinkedIn',
                    ],
                ],
                [
                    'key'      => 'field_ts_opt_soc_url',
                    'label'    => __('Odkaz', 'nexdigital'),
                    'name'     => 'url',
                    'type'     => 'url',
                    'required' => 1,
                ],
            ],
        ],
    ],
]);

/* -------------------------------------------------------------------------
   Podpora a dary
   ------------------------------------------------------------------------- */
acf_add_local_field_group([
    'key'      => 'group_ts_opt_podpora',
    'title'    => __('Podpora a dary', 'nexdigital'),
    'active'   => true,
    'location' => on_options_page('nastavenia-podpora'),
    'fields'   => [
        [
            'key'          => 'field_ts_opt_podpora_text',
            'label'        => __('Text výzvy', 'nexdigital'),
            'name'         => key('opt_podpora_text'),
            'type'         => 'textarea',
            'rows'         => 3,
            'instructions' => __('Krátke vysvetlenie, na čo peniaze idú. Konkrétne účely fungujú lepšie než všeobecná výzva.', 'nexdigital'),
        ],
        [
            'key'          => 'field_ts_opt_iban',
            'label'        => __('IBAN transparentného účtu', 'nexdigital'),
            'name'         => key('opt_iban'),
            'type'         => 'text',
            'placeholder'  => 'SK00 0000 0000 0000 0000 0000',
            'instructions' => __('Volebná kampaň musí byť financovaná cez transparentný účet. Uvedenie IBAN priamo na webe pôsobí dôveryhodnejšie než odkaz do banky.', 'nexdigital'),
        ],
        [
            'key'          => 'field_ts_opt_ucet_url',
            'label'        => __('Odkaz na výpis z účtu', 'nexdigital'),
            'name'         => key('opt_ucet_url'),
            'type'         => 'url',
            'instructions' => __('Verejný odkaz na transparentný účet v internet bankingu.', 'nexdigital'),
        ],
        image_field(
            [
                'key'          => 'field_ts_opt_qr',
                'label'        => __('QR kód na platbu', 'nexdigital'),
                'name'         => key('opt_qr'),
                'instructions' => __('Voliteľné. Uľahčí darcom platbu z mobilu.', 'nexdigital'),
            ],
            '600 × 600 px',
            __('1:1 (štvorec)', 'nexdigital'),
            'PNG'
        ),
    ],
]);

/* -------------------------------------------------------------------------
   Partneri
   ------------------------------------------------------------------------- */
acf_add_local_field_group([
    'key'      => 'group_ts_opt_partneri',
    'title'    => __('Partneri a podporovatelia', 'nexdigital'),
    'active'   => true,
    'location' => on_options_page('nastavenia-partneri'),
    'fields'   => [
        [
            'key'          => 'field_ts_opt_partneri',
            'label'        => __('Logá partnerov', 'nexdigital'),
            'name'         => key('opt_partneri'),
            'type'         => 'repeater',
            'layout'       => 'block',
            'button_label' => __('Pridať partnera', 'nexdigital'),
            'instructions' => __('Politické strany a organizácie, ktoré kampaň podporujú. Poradie tu určuje poradie na webe — ťahaj za šípky vľavo.', 'nexdigital'),
            'sub_fields'   => [
                [
                    'key'      => 'field_ts_opt_partner_nazov',
                    'label'    => __('Názov', 'nexdigital'),
                    'name'     => 'nazov',
                    'type'     => 'text',
                    'required' => 1,
                    'instructions' => __('Použije sa aj ako alternatívny text loga pre čítačky obrazovky.', 'nexdigital'),
                    'wrapper'  => ['width' => '60'],
                ],
                [
                    'key'     => 'field_ts_opt_partner_url',
                    'label'   => __('Odkaz', 'nexdigital'),
                    'name'    => 'url',
                    'type'    => 'url',
                    'wrapper' => ['width' => '40'],
                ],
                image_field(
                    [
                        'key'          => 'field_ts_opt_partner_logo',
                        'label'        => __('Logo', 'nexdigital'),
                        'name'         => 'logo',
                        'required'     => 1,
                        'instructions' => __('Najlepšie na priehľadnom pozadí.', 'nexdigital'),
                    ],
                    '600 × 300 px',
                    __('2:1 (na šírku)', 'nexdigital'),
                    'SVG, PNG s priehľadnosťou'
                ),
            ],
        ],
    ],
]);

/* -------------------------------------------------------------------------
   Zdieľanie a SEO
   ------------------------------------------------------------------------- */
acf_add_local_field_group([
    'key'      => 'group_ts_opt_zdielanie',
    'title'    => __('Zdieľanie a SEO', 'nexdigital'),
    'active'   => true,
    'location' => on_options_page('nastavenia-zdielanie'),
    'fields'   => [
        image_field(
            [
                'key'          => 'field_ts_opt_og',
                'label'        => __('Obrázok pri zdieľaní', 'nexdigital'),
                'name'         => key('opt_og_image'),
                'instructions' => __('Zobrazí sa, keď niekto zdieľa web na Facebooku alebo pošle odkaz v správe. Bez neho si Facebook vyberie obrázok sám — obvykle zle.', 'nexdigital'),
            ],
            '1200 × 630 px',
            __('1,91:1 (na šírku)', 'nexdigital'),
            'JPG alebo PNG'
        ),
        [
            'key'          => 'field_ts_opt_meta_popis',
            'label'        => __('Popis webu pre vyhľadávače', 'nexdigital'),
            'name'         => key('opt_meta_popis'),
            'type'         => 'textarea',
            'rows'         => 2,
            'maxlength'    => 160,
            'instructions' => __('Jedna až dve vety, ktoré uvidia ľudia v Google. Maximálne 160 znakov — dlhší text sa oreže.', 'nexdigital'),
        ],
    ],
]);
