<?php
/**
 * Render a registered nxd-forms form.
 *
 * Driven entirely by the definition in inc/forms.php, so a field added there
 * appears here, in the validator and in the notification mail at once. Markup
 * uses the theme's .form-* components — see app.css.
 *
 * The plugin's JavaScript looks for `form[data-ngo-form]`, marks failed fields
 * by `[data-field="<key>"]`, and replaces the container on success, so those
 * three hooks are a contract and must stay.
 *
 * Expected args:
 *   slug   string Registered form slug.
 *   submit string Submit button label.
 *
 * @package NexDigital
 */

declare(strict_types=1);

use function NexDigital\Theme\Forms\fields;

$slug = trim((string) ($args['slug'] ?? ''));
$fields = $slug !== '' ? fields($slug) : [];

if ($fields === []) {
    // Plugin off or slug unknown: render nothing rather than a form that
    // silently discards what somebody types into it.
    return;
}

$submit = trim((string) ($args['submit'] ?? '')) ?: __('Odoslať správu', 'nexdigital');
?>

<div data-ngo-form>
    <form
        class="flex flex-col gap-5"
        method="post"
        data-ngo-form
        novalidate
    >
        <?php nxd_form_fields($slug); ?>

        <?php // Half-width fields pair up on a wide screen; everything else runs
              // the full width of the column. ?>
        <?php
        $rows = [];
        $pending = null;

        foreach ($fields as $key => $field) {
            if (($field['width'] ?? '') === 'half') {
                if ($pending === null) {
                    $pending = [$key => $field];

                    continue;
                }

                $rows[] = $pending + [$key => $field];
                $pending = null;

                continue;
            }

            if ($pending !== null) {
                $rows[] = $pending;
                $pending = null;
            }

            $rows[] = [$key => $field];
        }

        if ($pending !== null) {
            $rows[] = $pending;
        }
        ?>

        <?php foreach ($rows as $row) : ?>
            <?php $paired = count($row) > 1; ?>
            <div class="<?php echo $paired ? 'grid gap-5 sm:grid-cols-2' : ''; ?>">
                <?php foreach ($row as $key => $field) : ?>
                    <?php
                    $type = (string) ($field['type'] ?? 'text');
                    $label = (string) ($field['label'] ?? $key);
                    $required = !empty($field['required']);
                    $hint = (string) ($field['hint'] ?? '');
                    $id = $slug . '-' . $key;
                    ?>

                    <?php if ($type === 'checkbox') : ?>
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="<?php echo esc_attr($id); ?>"
                                name="<?php echo esc_attr($key); ?>"
                                value="1"
                                data-field="<?php echo esc_attr($key); ?>"
                                <?php echo $required ? 'required' : ''; ?>
                            >
                            <label class="form-check-label" for="<?php echo esc_attr($id); ?>">
                                <?php echo esc_html($label); ?><?php echo $required ? ' *' : ''; ?>
                                <?php if ($hint !== '') : ?>
                                    <span class="block">
                                        <?php // Hint carries the privacy-policy link, so a limited
                                              // set of tags is allowed through rather than escaped. ?>
                                        <?php echo wp_kses($hint, ['a' => ['href' => [], 'target' => [], 'rel' => []]]); ?>
                                    </span>
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php else : ?>
                        <div class="form-field">
                            <label
                                class="form-label"
                                for="<?php echo esc_attr($id); ?>"
                                <?php echo $required ? 'data-required' : ''; ?>
                            >
                                <?php echo esc_html($label); ?>
                            </label>

                            <?php if ($type === 'textarea') : ?>
                                <textarea
                                    class="form-textarea"
                                    id="<?php echo esc_attr($id); ?>"
                                    name="<?php echo esc_attr($key); ?>"
                                    rows="6"
                                    data-field="<?php echo esc_attr($key); ?>"
                                    placeholder="<?php echo esc_attr((string) ($field['placeholder'] ?? '')); ?>"
                                    <?php echo $required ? 'required' : ''; ?>
                                ></textarea>
                            <?php else : ?>
                                <input
                                    class="form-input"
                                    type="<?php echo esc_attr($type === 'tel' ? 'tel' : ($type === 'email' ? 'email' : 'text')); ?>"
                                    id="<?php echo esc_attr($id); ?>"
                                    name="<?php echo esc_attr($key); ?>"
                                    data-field="<?php echo esc_attr($key); ?>"
                                    placeholder="<?php echo esc_attr((string) ($field['placeholder'] ?? '')); ?>"
                                    <?php echo isset($field['autocomplete']) ? 'autocomplete="' . esc_attr((string) $field['autocomplete']) . '"' : ''; ?>
                                    <?php echo $required ? 'required' : ''; ?>
                                >
                            <?php endif; ?>

                            <?php if ($hint !== '') : ?>
                                <p class="form-hint"><?php echo esc_html($hint); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <div>
            <button type="submit" class="btn btn-primary">
                <?php echo esc_html($submit); ?>
            </button>
        </div>
    </form>
</div>
