<?php
/**
 * Form definitions.
 *
 * The nxd-forms plugin owns validation, storage, mail and spam handling but
 * prints only the hidden fields — the visible markup and the field list belong
 * to the theme. Defining them here rather than in a template means the same
 * definition drives the rendered form, the validator and the notification mail,
 * so a field cannot exist on screen and be missing from the e-mail.
 *
 * Keys the plugin reads: `name` (the admin menu label — not `title`, which it
 * silently ignores and then warns about) and per field label, type, required.
 * The rest (placeholder, hint, autocomplete, width) are ours and only reach
 * the renderer.
 *
 * `recipient` and `subject` in the registration are NOT read by the plugin —
 * its mailer takes both from the `nxd_form_settings_<id>` option (empty here)
 * and falls back to admin_email, which is the agency's address. The
 * notification_to() filter below is what actually routes the mail to the
 * campaign inbox.
 *
 * @package NexDigital
 */

declare(strict_types=1);

namespace NexDigital\Theme\Forms;

use function NexDigital\Theme\Fields\option;

if (!defined('ABSPATH')) {
    exit;
}

/** Contact form slug. */
const CONTACT = 'kontakt';

/** Resident-ideas form slug, used by the front page prompt. */
const IDEA = 'napad-obyvatela';

/**
 * Route notification mail to the campaign inbox from Nastavenia webu —
 * but only as the default.
 *
 * The plugin's mailer ignores the `recipient` key passed to
 * nxd_form_register() and falls back to admin_email — the agency, not the
 * campaign. This filter replaces that fallback. An address typed into the
 * form's own settings screen (Formuláre → Nastavenia) must win, though:
 * the first version of this filter overrode it unconditionally, and a
 * deliberately configured Gmail address silently kept arriving at opt_email.
 * The mailer hands the filter a single string, so telling "explicit" from
 * "fallback" means reading the form's settings option ourselves.
 */
function notification_to(string $to, string $form_id): string {
    $settings = (array) get_option('nxd_form_settings_' . sanitize_key($form_id), []);
    $explicit = sanitize_email((string) ($settings['notification_email'] ?? ''));

    if (is_email($explicit)) {
        return $to;
    }

    $recipient = trim((string) (option('opt_email') ?? ''));

    return is_email($recipient) ? $recipient : $to;
}
add_filter('nxd_form_notification_to', __NAMESPACE__ . '\\notification_to', 10, 2);

/**
 * The GDPR consent row, shared by both forms.
 *
 * A campaign asking for someone's e-mail has to say what happens to it, and the
 * link has to be in the label itself rather than in fine print elsewhere.
 *
 * @return array<string, mixed>
 */
function consent_field(): array {
    $privacy = get_privacy_policy_url() ?: home_url('/ochrana-sukromia/');

    return [
        'label'    => __('Súhlasím so spracovaním osobných údajov', 'nexdigital'),
        'type'     => 'checkbox',
        'required' => true,
        'hint'     => sprintf(
            /* translators: %s: privacy policy URL */
            __('Údaje použijeme len na odpoveď na vašu správu. Viac v <a href="%s">zásadách ochrany súkromia</a>.', 'nexdigital'),
            esc_url($privacy)
        ),
    ];
}

/**
 * Register both forms.
 *
 * Priority 5 on init: the plugin's handler hooks init at the default priority
 * and looks the form up from the registry, so registration has to be earlier.
 */
function register(): void {
    if (!function_exists('nxd_form_register')) {
        return;
    }

    $recipient = trim((string) (option('opt_email') ?? '')) ?: get_option('admin_email');

    nxd_form_register(CONTACT, [
        'name'      => __('Kontaktný formulár', 'nexdigital'),
        'recipient' => $recipient,
        'subject'   => __('Nová správa z webu Pre Stupavu', 'nexdigital'),
        'fields'    => [
            'meno' => [
                'label'        => __('Meno a priezvisko', 'nexdigital'),
                'type'         => 'text',
                'required'     => true,
                'autocomplete' => 'name',
                'width'        => 'half',
            ],
            'email' => [
                'label'        => __('E-mail', 'nexdigital'),
                'type'         => 'email',
                'required'     => true,
                'autocomplete' => 'email',
                'width'        => 'half',
            ],
            'telefon' => [
                'label'        => __('Telefón', 'nexdigital'),
                'type'         => 'tel',
                'required'     => false,
                'autocomplete' => 'tel',
                'hint'         => __('Nepovinné - ak chcete, aby sme zavolali.', 'nexdigital'),
            ],
            'sprava' => [
                'label'       => __('Správa', 'nexdigital'),
                'type'        => 'textarea',
                'required'    => true,
                'placeholder' => __('Napíšte nám, čo vás zaujíma…', 'nexdigital'),
            ],
            'suhlas' => consent_field(),
        ],
    ]);

    nxd_form_register(IDEA, [
        'name'      => __('Nápad obyvateľa', 'nexdigital'),
        'recipient' => $recipient,
        'subject'   => __('Nový námet od obyvateľa Stupavy', 'nexdigital'),
        'fields'    => [
            'meno' => [
                'label'        => __('Meno', 'nexdigital'),
                'type'         => 'text',
                'required'     => true,
                'autocomplete' => 'name',
                'width'        => 'half',
            ],
            'email' => [
                'label'        => __('E-mail', 'nexdigital'),
                'type'         => 'email',
                'required'     => true,
                'autocomplete' => 'email',
                'width'        => 'half',
            ],
            'napad' => [
                'label'       => __('Čo by ste v Stupave zlepšili?', 'nexdigital'),
                'type'        => 'textarea',
                'required'    => true,
                'placeholder' => __('Stačí pár viet — miesto, problém, návrh…', 'nexdigital'),
            ],
            'suhlas' => consent_field(),
        ],
    ]);
}
add_action('init', __NAMESPACE__ . '\\register', 5);

/**
 * Field list for a registered form, or an empty array when the plugin is off.
 *
 * @return array<string, array<string, mixed>>
 */
function fields(string $slug): array {
    if (!function_exists('nxd_form_get')) {
        return [];
    }

    $config = nxd_form_get($slug);

    return is_array($config['fields'] ?? null) ? $config['fields'] : [];
}
