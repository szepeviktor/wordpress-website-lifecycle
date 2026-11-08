<?php

/*
 * Plugin Name: Divi ActiveCampaign Form Guard
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

namespace DiviActiveCampaignFormGuard;

defined('ABSPATH') || exit;

add_action('wp_footer', __NAMESPACE__ . '\\render_guard', 100);
add_filter('et_module_shortcode_output', __NAMESPACE__ . '\\protect_module', 100, 2);

/**
 * Preserve form actions until the visitor interacts with the form.
 */
function protect_module($output, $slug)
{
    if ('et_pb_code' !== $slug || ! is_string($output)) {
        return $output;
    }

    $html = new \WP_HTML_Tag_Processor($output);
    $protected_form = false;
    while ($html->next_tag(['tag_closers' => 'visit'])) {
        $tag = $html->get_tag();
        if ('FORM' === $tag && $html->is_tag_closer()) {
            $protected_form = false;
            continue;
        }
        if ($html->is_tag_closer()) {
            continue;
        }
        if ('FORM' !== $tag) {
            $type = strtolower((string) $html->get_attribute('type'));
            $is_submit = ('INPUT' === $tag && in_array($type, ['submit', 'image'], true))
                || ('BUTTON' === $tag && ! in_array($type, ['button', 'reset'], true));
            if ($protected_form && $is_submit && null === $html->get_attribute('disabled')) {
                $html->set_attribute('disabled', true);
                $html->set_attribute('data-ac-guard-disabled', '1');
            }
            continue;
        }
        $protected_form = false;
        $action = $html->get_attribute('action');
        if (
            ! is_string($action)
            || ! preg_match('~^https://[a-z0-9-]+\\.activehosted\\.com/proc\\.php(?:\\?[^#]*)?$~i', $action)
        ) {
            continue;
        }
        $html->set_attribute('data-ac-guard-action', $action);
        $html->remove_attribute('action');
        $protected_form = true;
    }

    return $html->get_updated_html();
}

/**
 * Restore protected form actions on interaction and submission.
 */
function render_guard()
{
    ?>
    <script id="divi-activecampaign-form-guard">
    (function () {
        'use strict';

        function activate(event) {
            const target = event.target;
            if (!(target instanceof Element)) return;
            const form = target.closest('.et_pb_code_inner form');
            if (!form) return;
            const action = form.getAttribute('data-ac-guard-action');
            if (action) {
                form.setAttribute('action', action);
            } else if (event.type === 'submit' && form.hasAttribute('data-ac-guard-action')) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }

        ['focusin', 'pointerdown', 'submit'].forEach(function (name) {
            document.addEventListener(name, activate, true);
        });
        document.querySelectorAll(
            '.et_pb_code_inner form[data-ac-guard-action] [data-ac-guard-disabled="1"]'
        ).forEach(function (button) {
            if (!button.closest('form').getAttribute('data-ac-guard-action')) return;
            button.removeAttribute('disabled');
            button.removeAttribute('data-ac-guard-disabled');
        });
    }());
    </script>
    <?php
}
