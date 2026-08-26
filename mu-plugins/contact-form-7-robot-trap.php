<?php

/*
 * Plugin Name: Contact Form 7 Robot Trap
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

/**
 * Hidden trap field for Contact Form 7.
 *
 * Usage:
 * Add <code>[robottrap email-verify class:email-verify tabindex:2]</code>
 * before the email field, then hide it with:
 * <code>div.wpcf7 .email-verify { display:none; }</code>
 *
 * Fires the robottrap_hiddenfield and robottrap_mx hooks.
 *
 * WARNING
 *
 * DNS failures can cause false positives during domain validation.
 * Disable domain validation by adding this to wp-config.php:
 *
 * define('CF7_ROBOT_TRAP_TOLERATE_DNS_FAILURE', true);
 */
add_action('plugins_loaded', 'wpcf7_robottrap_bootstrap');

/**
 * Register hooks after Contact Form 7 is loaded.
 */
function wpcf7_robottrap_bootstrap(): void
{
    if (! function_exists('wpcf7_add_form_tag') || ! class_exists('WPCF7_FormTag')) {
        return;
    }

    add_action('wpcf7_init', 'wpcf7_add_form_tag_robottrap');
    add_filter('wpcf7_validate_robottrap', 'wpcf7_robottrap_validation_filter', 10, 2);

    if (! (defined('CF7_ROBOT_TRAP_TOLERATE_DNS_FAILURE') && CF7_ROBOT_TRAP_TOLERATE_DNS_FAILURE)) {
        add_filter('wpcf7_validate_email', 'wpcf7_robottrap_domain_validation_filter', 20, 2);
        add_filter('wpcf7_validate_email*', 'wpcf7_robottrap_domain_validation_filter', 20, 2);
    }
}

/**
 * Create the [robottrap] form tag.
 */
function wpcf7_add_form_tag_robottrap(): void
{
    wpcf7_add_form_tag(
        ['robottrap'],
        'wpcf7_robottrap_form_tag_handler',
        [
            'name-attr' => true,
            'not-for-mail' => true,
        ]
    );
}

/**
 * Render the [robottrap] form tag.
 *
 * @param WPCF7_FormTag|array $tag Form tag definition.
 * @return string
 */
function wpcf7_robottrap_form_tag_handler($tag): string
{
    $tag = new WPCF7_FormTag($tag);

    if (empty($tag->name)) {
        return '';
    }

    $validation_error = wpcf7_get_validation_error($tag->name);
    $class = wpcf7_form_controls_class('text');

    if ($validation_error) {
        $class .= ' wpcf7-not-valid';
    }

    $atts = array();

    /**
     * Robots may look for the word "hidden".
     *
     * $atts['aria-hidden'] = 'true';
     */
    $atts['size'] = $tag->get_size_option('40');
    $atts['maxlength'] = $tag->get_maxlength_option();
    $atts['class'] = $tag->get_class_option($class);
    $atts['id'] = $tag->get_id_option();
    $atts['tabindex'] = $tag->get_option('tabindex', 'signed_int', true);

    if ($validation_error) {
        $atts['aria-invalid'] = 'true';
        $atts['aria-describedby'] = wpcf7_get_validation_error_reference($tag->name);
    } else {
        $atts['aria-invalid'] = 'false';
    }

    $atts['value'] = '';
    $atts['type'] = 'text';
    $atts['name'] = $tag->name;

    $atts = wpcf7_format_atts($atts);

    return sprintf(
        '<span class="wpcf7-form-control-wrap" data-name="%s"><input %s />%s</span>',
        esc_attr($tag->name),
        $atts,
        $validation_error
    );
}

/**
 * Detect submitted hidden field.
 *
 * This is the validator function of [robottrap].
 *
 * @param WPCF7_Validation $result The WPCF7 result object.
 * @param WPCF7_FormTag|array $tag The source form tag.
 * @return WPCF7_Validation
 */
function wpcf7_robottrap_validation_filter($result, $tag): WPCF7_Validation
{
    $tag = new WPCF7_FormTag($tag);
    $submission = WPCF7_Submission::get_instance();
    $value = $submission instanceof WPCF7_Submission
        ? $submission->get_posted_string($tag->name)
        : '';

    if ($value !== '') {
        /**
         * Counteraction for filled-out hidden field
         *
         * Only a robot is able to see fields hidden by CSS.
         *
         * @param string $value Sanitized value of field.
         */
        do_action('robottrap_hiddenfield', $value);

        $result->invalidate($tag, wpcf7_get_message('spam'));
    }

    return $result;
}

/**
 * Validate email domain.
 *
 * This is the validator function of [email]. Does robottrap_mx action on invalid email domain.
 *
 * @param WPCF7_Validation $result WPCF7 result object.
 * @param WPCF7_FormTag|array $tag Source form tag.
 * @return WPCF7_Validation
 */
function wpcf7_robottrap_domain_validation_filter($result, $tag): WPCF7_Validation
{
    $tag = new WPCF7_FormTag($tag);
    $submission = WPCF7_Submission::get_instance();
    $value = $submission instanceof WPCF7_Submission
        ? $submission->get_posted_string($tag->name)
        : '';

    if (! $result->is_valid($tag->name) || $value === '') {
        return $result;
    }

    $at_position = strrpos($value, '@');

    if (false === $at_position) {
        return $result;
    }

    $domain = sanitize_text_field(substr($value, $at_position + 1));

    if (empty($domain) || ! checkdnsrr($domain, 'MX')) {
        /**
         * Counteraction for empty or MX-less domain part of email addresses
         *
         * Usually this is a spammer robot.
         *
         * @param string $domain Email domain.
         */
        do_action('robottrap_mx', $domain);

        $result->invalidate($tag, wpcf7_get_message('spam'));
    }

    return $result;
}
