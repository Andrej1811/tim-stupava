<?php
/**
 * Custom post types and taxonomies.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\PostTypes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project lifecycle, in order.
 *
 * These are the real permitting stages the client's programme document tracks
 * ("má územné rozhodnutie, pracuje sa na stavebnom povolení, následne
 * verejné obstarávanie"). Order matters — it drives sorting and the progress
 * indicator — so this is an ordered map, not a taxonomy.
 *
 * @return array<string,string> key => label
 */
function project_stages(): array {
    return [
        'priprava'           => __('Príprava', 'nexdigital'),
        'studia'             => __('Štúdia / koncepcia', 'nexdigital'),
        'uzemne-rozhodnutie' => __('Územné rozhodnutie', 'nexdigital'),
        'stavebne-povolenie' => __('Stavebné povolenie', 'nexdigital'),
        'obstaravanie'       => __('Verejné obstarávanie', 'nexdigital'),
        'v-realizacii'       => __('V realizácii', 'nexdigital'),
        'dokoncene'          => __('Dokončené', 'nexdigital'),
    ];
}

/** The stage that moves a project from "Program" to "Výsledky". */
const STAGE_DONE = 'dokoncene';

/** Zero-based position of a stage, or null when the key is unknown. */
function stage_index(string $stage): ?int {
    $index = array_search($stage, array_keys(project_stages()), true);

    return $index === false ? null : $index;
}

/** Human label for a stage key. */
function stage_label(string $stage): string {
    return project_stages()[$stage] ?? '';
}

/**
 * Register the candidate post type.
 *
 * Archive slug is "kandidati", which is why no page with that slug exists —
 * a page and a post type archive cannot share a permalink.
 */
function register_candidate(): void {
    register_post_type('kandidat', [
        'labels' => [
            'name'               => __('Kandidáti', 'nexdigital'),
            'singular_name'      => __('Kandidát', 'nexdigital'),
            'add_new_item'       => __('Pridať kandidáta', 'nexdigital'),
            'edit_item'          => __('Upraviť kandidáta', 'nexdigital'),
            'search_items'       => __('Hľadať kandidátov', 'nexdigital'),
            'not_found'          => __('Žiadni kandidáti', 'nexdigital'),
            'menu_name'          => __('Kandidáti', 'nexdigital'),
        ],
        'public'       => true,
        'has_archive'  => 'kandidati',
        'rewrite'      => ['slug' => 'kandidati', 'with_front' => false],
        'menu_icon'    => 'dashicons-groups',
        'menu_position' => 20,
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'],
        'show_in_rest' => true,
    ]);
}

/**
 * Register the project post type.
 *
 * Programme and results are the same records at different stages, so they share
 * one post type. has_archive is false on purpose: /program/ and /vysledky/ are
 * real pages that query this type, which lets the client edit their intros.
 */
function register_project(): void {
    register_post_type('projekt', [
        'labels' => [
            'name'          => __('Projekty', 'nexdigital'),
            'singular_name' => __('Projekt', 'nexdigital'),
            'add_new_item'  => __('Pridať projekt', 'nexdigital'),
            'edit_item'     => __('Upraviť projekt', 'nexdigital'),
            'search_items'  => __('Hľadať projekty', 'nexdigital'),
            'not_found'     => __('Žiadne projekty', 'nexdigital'),
            'menu_name'     => __('Projekty', 'nexdigital'),
        ],
        'public'        => true,
        'has_archive'   => false,
        'rewrite'       => ['slug' => 'program', 'with_front' => false],
        'menu_icon'     => 'dashicons-hammer',
        'menu_position' => 21,
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
        'show_in_rest'  => true,
    ]);
}

/** Register the project area taxonomy (doprava, šport, zdravotníctvo, …). */
function register_area(): void {
    register_taxonomy('oblast', ['projekt'], [
        'labels' => [
            'name'          => __('Oblasti', 'nexdigital'),
            'singular_name' => __('Oblasť', 'nexdigital'),
            'add_new_item'  => __('Pridať oblasť', 'nexdigital'),
            'menu_name'     => __('Oblasti', 'nexdigital'),
        ],
        'public'            => true,
        'hierarchical'      => true,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'oblast', 'with_front' => false],
        'show_in_rest'      => true,
    ]);
}

function register(): void {
    register_candidate();
    register_project();
    register_area();
}
add_action('init', __NAMESPACE__ . '\\register');

/**
 * Post types whose main content stays on the classic editor.
 *
 * A candidate profile is a photo, a set of fields and a few paragraphs of CV —
 * the block editor adds a canvas, block inserter and sidebar around what is
 * really a text box, and pushes the SCF fields below the fold. `show_in_rest`
 * stays true, so the REST API and the media library are unaffected; this only
 * decides which editor the screen loads.
 *
 * A project is the same shape: a description, a stage, a few facts and a
 * gallery, all of which live in fields rather than in blocks.
 *
 * @return array<int, string>
 */
function classic_editor_post_types(): array {
    return (array) apply_filters('nexdigital/classic_editor_post_types', ['kandidat', 'projekt']);
}

function disable_block_editor(bool $use_block_editor, string $post_type): bool {
    return in_array($post_type, classic_editor_post_types(), true) ? false : $use_block_editor;
}
add_filter('use_block_editor_for_post_type', __NAMESPACE__ . '\\disable_block_editor', 10, 2);

/**
 * Order candidates by ballot number.
 *
 * In Slovak municipal elections voters circle numbers on the ballot, so the
 * ballot order is the only order that helps a voter cross-reference the site
 * against the paper in front of them. Alphabetical would actively mislead.
 */
function order_candidates_by_ballot(\WP_Query $query): void {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if (!$query->is_post_type_archive('kandidat')) {
        return;
    }

    $query->set('meta_key', 'ts_cislo');
    $query->set('orderby', 'meta_value_num');
    $query->set('order', 'ASC');
    $query->set('posts_per_page', -1);
}
add_action('pre_get_posts', __NAMESPACE__ . '\\order_candidates_by_ballot');

/**
 * Query helper shared by the Program and Výsledky templates.
 *
 * @param bool $completed true → finished projects (Výsledky), false → everything still in flight (Program)
 */
function projects(bool $completed): \WP_Query {
    return new \WP_Query([
        'post_type'      => 'projekt',
        'posts_per_page' => -1,
        'meta_key'       => 'ts_stav',
        'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
        'meta_query'     => [
            [
                'key'     => 'ts_stav',
                'value'   => STAGE_DONE,
                'compare' => $completed ? '=' : '!=',
            ],
        ],
    ]);
}
