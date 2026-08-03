<?php
/**
 * Hero carousel block.
 *
 * Wraps template-parts/hero-carousel.php so the client edits the slides in the
 * page editor. The template part already treats one slide as a static hero, so
 * an editor who deletes a slide gets a plain hero rather than a carousel with
 * one dot — nothing here needs to know about that.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Blocks\HeroCarousel;

use function NexDigital\Theme\Fields\image_field;

if (!defined('ABSPATH')) {
    exit;
}

const NAME = 'ts-hero-carousel';

/** Logo heights, keyed by the shape of the mark. */
const LOGO_SIZES = [
    'wordmark' => 'h-7 sm:h-8',
    'lockup'   => 'h-11 sm:h-12',
];

acf_register_block_type([
    'name'            => NAME,
    // v2 is what ACF 6 / SCF expects; v1 blocks make WordPress fall back to the
    // legacy editor canvas, where the field form ends up in the narrow sidebar.
    'api_version'     => 2,
    'title'           => __('Hero carousel', 'nexdigital'),
    'description'     => __('Úvodná sekcia. Jedna snímka = statické hero, dve a viac = carousel so šípkami a bodkami.', 'nexdigital'),
    'category'        => \NexDigital\Theme\Blocks\CATEGORY,
    'icon'            => 'slides',
    'keywords'        => ['hero', 'carousel', 'banner', 'uvod'],
    'supports'        => [
        'align'  => false,
        'anchor' => true,
        'jsx'    => false,
        'multiple' => false,
    ],
    'render_callback' => __NAMESPACE__ . '\\render',
]);

/**
 * Map the block's fields onto the template part's arguments.
 *
 * The template part is also used with hard-coded arrays, so the shape it
 * receives here is exactly the shape it already expects — the field names are
 * Slovak for the client, the array keys stay English for the template.
 */
function render(): void {
    $rows = get_field('snimky');
    $slides = [];

    foreach (is_array($rows) ? $rows : [] as $row) {
        if (!is_array($row)) {
            continue;
        }

        $type = ($row['typ'] ?? 'banner') === 'teza' ? 'thesis' : 'banner';

        $slide = [
            'type'         => $type,
            'eyebrow'      => trim((string) ($row['eyebrow'] ?? '')),
            'title'        => trim((string) ($row['nadpis'] ?? '')),
            'title_accent' => trim((string) ($row['nadpis_zvyraznenie'] ?? '')),
            'text'         => trim((string) ($row['text'] ?? '')),
            'image'        => $type === 'thesis' ? ($row['portret'] ?? null) : ($row['obrazok'] ?? null),
            'image_alt'    => trim((string) ($row['portret_alt'] ?? '')),
            'ctas'         => ctas($row['tlacidla'] ?? null),
        ];

        if ($type === 'banner') {
            $slide['image_fit'] = match ($row['vyrez'] ?? 'pozadie') {
                'cela'     => 'whole',
                'vyplnit'  => 'cover',
                'cela-sirka' => 'full',
                default    => 'background',
            };
            $slide['logos'] = logos($row['loga'] ?? null);
        }

        if ($type === 'thesis') {
            $slide['caption'] = caption($row);
        }

        $slides[] = $slide;
    }

    if ($slides === []) {
        // An empty block prints nothing on the front end; the editor still sees
        // its form, so this is a half-finished slide, not an error.
        return;
    }

    get_template_part('template-parts/hero-carousel', null, [
        'slides' => $slides,
        'label'  => __('Hlavné oznámenia kampane', 'nexdigital'),
    ]);
}

/**
 * Normalise the CTA repeater. A link field carries url, title and target in one
 * value, so a row without a link is simply dropped.
 *
 * @return array<int, array<string, string>>
 */
function ctas(mixed $rows): array {
    $ctas = [];

    foreach (is_array($rows) ? $rows : [] as $row) {
        $link = is_array($row['odkaz'] ?? null) ? $row['odkaz'] : [];
        $url = trim((string) ($link['url'] ?? ''));
        $label = trim((string) ($link['title'] ?? ''));

        if ($url === '' || $label === '') {
            continue;
        }

        $ctas[] = [
            'label'  => $label,
            'url'    => $url,
            'style'  => ($row['styl'] ?? 'primary') === 'ghost' ? 'ghost' : 'primary',
            'target' => trim((string) ($link['target'] ?? '')),
        ];
    }

    return $ctas;
}

/**
 * Normalise the partner logo repeater.
 *
 * @return array<int, array<string, mixed>>
 */
function logos(mixed $rows): array {
    $logos = [];

    foreach (is_array($rows) ? $rows : [] as $row) {
        $id = (int) ($row['logo'] ?? 0);

        if ($id <= 0) {
            continue;
        }

        $logos[] = [
            'src'  => $id,
            'alt'  => trim((string) ($row['nazov'] ?? '')),
            'size' => LOGO_SIZES[$row['tvar'] ?? 'wordmark'] ?? LOGO_SIZES['wordmark'],
        ];
    }

    return $logos;
}

/**
 * The caption chip under a thesis slide's portrait.
 *
 * @param array<string, mixed> $row
 * @return array<string, string>|null
 */
function caption(array $row): ?array {
    $name = trim((string) ($row['meno'] ?? ''));

    if ($name === '') {
        return null;
    }

    return [
        'number' => trim((string) ($row['cislo'] ?? '')),
        'name'   => $name,
        'role'   => trim((string) ($row['funkcia'] ?? '')),
    ];
}

acf_add_local_field_group([
    'key'      => 'group_ts_block_hero_carousel',
    'title'    => __('Hero carousel — snímky', 'nexdigital'),
    'active'   => true,
    'location' => [
        [
            ['param' => 'block', 'operator' => '==', 'value' => 'acf/' . NAME],
        ],
    ],
    'fields'   => [
        [
            'key'          => 'field_ts_hero_snimky',
            'label'        => __('Snímky', 'nexdigital'),
            'name'         => 'snimky',
            'type'         => 'repeater',
            'layout'       => 'block',
            'min'          => 1,
            'button_label' => __('Pridať snímku', 'nexdigital'),
            'instructions' => __('Jedna snímka sa zobrazí ako obyčajné hero — šípky ani bodky sa nevykreslia. Od dvoch snímok vyššie sa z toho stane carousel. Poradie tu určuje poradie na webe.', 'nexdigital'),
            'sub_fields'   => [
                [
                    'key'           => 'field_ts_hero_typ',
                    'label'         => __('Typ snímky', 'nexdigital'),
                    'name'          => 'typ',
                    'type'          => 'select',
                    'default_value' => 'banner',
                    'choices'       => [
                        'banner' => __('Banner — tmavé pozadie, fotka vpravo, logá partnerov', 'nexdigital'),
                        'teza'   => __('Téza — svetlé pozadie, portrét s menovkou', 'nexdigital'),
                    ],
                    'instructions'  => __('Typ mení celé rozloženie snímky aj to, ktoré polia nižšie sa zobrazia.', 'nexdigital'),
                ],
                [
                    'key'          => 'field_ts_hero_eyebrow',
                    'label'        => __('Malý text nad nadpisom', 'nexdigital'),
                    'name'         => 'eyebrow',
                    'type'         => 'text',
                    'placeholder'  => 'Komunálne voľby 2026 · Stupava',
                    'instructions' => __('Zobrazí sa veľkými písmenami. Krátky — jeden riadok.', 'nexdigital'),
                ],
                [
                    'key'      => 'field_ts_hero_nadpis',
                    'label'    => __('Nadpis', 'nexdigital'),
                    'name'     => 'nadpis',
                    'type'     => 'text',
                    'required' => 1,
                    'wrapper'  => ['width' => '50'],
                ],
                [
                    'key'          => 'field_ts_hero_nadpis_akcent',
                    'label'        => __('Druhý riadok nadpisu', 'nexdigital'),
                    'name'         => 'nadpis_zvyraznenie',
                    'type'         => 'text',
                    'wrapper'      => ['width' => '50'],
                    'instructions' => __('Zalomí sa na nový riadok a bude farebný. Nechaj prázdne, ak stačí jeden riadok.', 'nexdigital'),
                ],
                [
                    'key'   => 'field_ts_hero_text',
                    'label' => __('Text pod nadpisom', 'nexdigital'),
                    'name'  => 'text',
                    'type'  => 'textarea',
                    'rows'  => 3,
                ],
                image_field(
                    [
                        'key'               => 'field_ts_hero_obrazok',
                        'label'             => __('Fotka vpravo', 'nexdigital'),
                        'name'              => 'obrazok',
                        'instructions'      => __('Na počítači vypĺňa pravú polovicu snímky, na mobile pás pod textom. Hlavná postava alebo motív patrí do stredu.', 'nexdigital'),
                        'conditional_logic' => [
                            [
                                ['field' => 'field_ts_hero_typ', 'operator' => '==', 'value' => 'banner'],
                            ],
                        ],
                    ],
                    '1200 × 1200 px',
                    __('1:1 (štvorec)', 'nexdigital'),
                    'JPG alebo WebP'
                ),
                [
                    'key'               => 'field_ts_hero_vyrez',
                    'label'             => __('Zobrazenie fotky', 'nexdigital'),
                    'name'              => 'vyrez',
                    'type'              => 'select',
                    'default_value'     => 'pozadie',
                    'choices'           => [
                        'cela-sirka' => __('Cez celú šírku — text leží na fotke s tmavým prekrytím', 'nexdigital'),
                        'pozadie'    => __('Na pozadí — fotka cez väčšinu šírky, text na gradiente', 'nexdigital'),
                        'vyplnit'    => __('V pravej polovici — vyplní ju, okraje fotky sa orežú', 'nexdigital'),
                        'cela'       => __('V pravej polovici — celá fotka, nič sa neoreže', 'nexdigital'),
                    ],
                    'instructions'      => __('Pri širokej skupinovej fotke použi „Cez celú šírku“ — fotka sa oreže len zhora a zdola, takže nikto z radu nezmizne. „Na pozadí“ nechá vľavo tmavý priestor pre text, ale najkrajnejší človek môže skončiť za gradientom. „V pravej polovici“ sa hodí na fotku s jednou postavou.', 'nexdigital'),
                    'conditional_logic' => [
                        [
                            ['field' => 'field_ts_hero_typ', 'operator' => '==', 'value' => 'banner'],
                        ],
                    ],
                ],
                image_field(
                    [
                        'key'               => 'field_ts_hero_portret',
                        'label'             => __('Portrét', 'nexdigital'),
                        'name'              => 'portret',
                        'instructions'      => __('Zobrazí sa v bielej karte s menovkou. Najlepšie funguje štúdiová fotka na svetlom pozadí.', 'nexdigital'),
                        'conditional_logic' => [
                            [
                                ['field' => 'field_ts_hero_typ', 'operator' => '==', 'value' => 'teza'],
                            ],
                        ],
                    ],
                    '900 × 1200 px',
                    __('3:4 (na výšku)', 'nexdigital'),
                    'JPG alebo WebP'
                ),
                [
                    'key'               => 'field_ts_hero_portret_alt',
                    'label'             => __('Popis portrétu pre čítačky obrazovky', 'nexdigital'),
                    'name'              => 'portret_alt',
                    'type'              => 'text',
                    'placeholder'       => __('Peter Novisedlák, primátor Stupavy', 'nexdigital'),
                    'instructions'      => __('Kto je na fotke. Číta ho nevidiaci návštevník a vyhľadávače.', 'nexdigital'),
                    'conditional_logic' => [
                        [
                            ['field' => 'field_ts_hero_typ', 'operator' => '==', 'value' => 'teza'],
                        ],
                    ],
                ],
                [
                    'key'          => 'field_ts_hero_tlacidla',
                    'label'        => __('Tlačidlá', 'nexdigital'),
                    'name'         => 'tlacidla',
                    'type'         => 'repeater',
                    'layout'       => 'table',
                    'max'          => 2,
                    'button_label' => __('Pridať tlačidlo', 'nexdigital'),
                    'instructions' => __('Maximálne dve. Prvé nech je to, čo má návštevník naozaj spraviť.', 'nexdigital'),
                    'sub_fields'   => [
                        [
                            'key'      => 'field_ts_hero_tlacidlo_odkaz',
                            'label'    => __('Odkaz', 'nexdigital'),
                            'name'     => 'odkaz',
                            'type'     => 'link',
                            'required' => 1,
                            'wrapper'  => ['width' => '65'],
                        ],
                        [
                            'key'           => 'field_ts_hero_tlacidlo_styl',
                            'label'         => __('Štýl', 'nexdigital'),
                            'name'          => 'styl',
                            'type'          => 'select',
                            'default_value' => 'primary',
                            'choices'       => [
                                'primary' => __('Plné (petrolejové)', 'nexdigital'),
                                'ghost'   => __('Obrysové', 'nexdigital'),
                            ],
                            'wrapper'       => ['width' => '35'],
                        ],
                    ],
                ],
                [
                    'key'               => 'field_ts_hero_loga',
                    'label'             => __('Logá partnerov', 'nexdigital'),
                    'name'              => 'loga',
                    'type'              => 'repeater',
                    'layout'            => 'block',
                    'button_label'      => __('Pridať logo', 'nexdigital'),
                    'instructions'      => __('Zobrazia sa na bielom podklade pod tlačidlami, aby si strany zachovali vlastné farby. Prázdny zoznam = žiadne logá.', 'nexdigital'),
                    'conditional_logic' => [
                        [
                            ['field' => 'field_ts_hero_typ', 'operator' => '==', 'value' => 'banner'],
                        ],
                    ],
                    'sub_fields'        => [
                        [
                            'key'          => 'field_ts_hero_logo_nazov',
                            'label'        => __('Názov', 'nexdigital'),
                            'name'         => 'nazov',
                            'type'         => 'text',
                            'required'     => 1,
                            'wrapper'      => ['width' => '50'],
                            'instructions' => __('Použije sa ako alternatívny text loga.', 'nexdigital'),
                        ],
                        [
                            'key'           => 'field_ts_hero_logo_tvar',
                            'label'         => __('Tvar loga', 'nexdigital'),
                            'name'          => 'tvar',
                            'type'          => 'select',
                            'default_value' => 'wordmark',
                            'choices'       => [
                                'wordmark' => __('Na šírku (názov v jednom riadku)', 'nexdigital'),
                                'lockup'   => __('Na výšku (značka nad názvom)', 'nexdigital'),
                            ],
                            'wrapper'       => ['width' => '50'],
                            'instructions'  => __('Určuje výšku loga, aby vedľa seba pôsobili rovnako veľké.', 'nexdigital'),
                        ],
                        image_field(
                            [
                                'key'          => 'field_ts_hero_logo_subor',
                                'label'        => __('Logo', 'nexdigital'),
                                'name'         => 'logo',
                                'required'     => 1,
                                'instructions' => __('Najlepšie na priehľadnom pozadí.', 'nexdigital'),
                            ],
                            '600 × 300 px',
                            __('2:1 (na šírku)', 'nexdigital'),
                            'PNG s priehľadnosťou'
                        ),
                    ],
                ],
                [
                    'key'               => 'field_ts_hero_cislo',
                    'label'             => __('Číslo na hlasovacom lístku', 'nexdigital'),
                    'name'              => 'cislo',
                    'type'              => 'text',
                    'maxlength'         => 3,
                    'wrapper'           => ['width' => '20'],
                    'instructions'      => __('Zobrazí sa v krúžku pri mene. Prázdne = bez krúžku.', 'nexdigital'),
                    'conditional_logic' => [
                        [
                            ['field' => 'field_ts_hero_typ', 'operator' => '==', 'value' => 'teza'],
                        ],
                    ],
                ],
                [
                    'key'               => 'field_ts_hero_meno',
                    'label'             => __('Meno', 'nexdigital'),
                    'name'              => 'meno',
                    'type'              => 'text',
                    'wrapper'           => ['width' => '40'],
                    'instructions'      => __('Bez mena sa menovka pod portrétom nezobrazí.', 'nexdigital'),
                    'conditional_logic' => [
                        [
                            ['field' => 'field_ts_hero_typ', 'operator' => '==', 'value' => 'teza'],
                        ],
                    ],
                ],
                [
                    'key'               => 'field_ts_hero_funkcia',
                    'label'             => __('Funkcia', 'nexdigital'),
                    'name'              => 'funkcia',
                    'type'              => 'text',
                    'wrapper'           => ['width' => '40'],
                    'placeholder'       => __('Primátor Stupavy · kandidát na primátora', 'nexdigital'),
                    'conditional_logic' => [
                        [
                            ['field' => 'field_ts_hero_typ', 'operator' => '==', 'value' => 'teza'],
                        ],
                    ],
                ],
            ],
        ],
    ],
]);
