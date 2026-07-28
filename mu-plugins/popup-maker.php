<?php

/*
 * Plugin Name: Popup Maker Admin Cleanup
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 * Description: Removes Popup Maker upsells, telemetry prompts, review requests, and unnecessary admin UI.
 */

// Use Popup Maker's official kill switch before the plugin initializes.
defined('POPUP_MAKER_DISABLE_UPSELLS') || define('POPUP_MAKER_DISABLE_UPSELLS', true);

/*
 * Popup Maker registers its real controllers at plugins_loaded priority 11.
 * Alias an anonymous inert controller to the PluginsPage class name first,
 * preventing the original controller from ever being autoloaded.
 */
add_action(
    'plugins_loaded',
    static function () {
        $pluginsPage = 'PopupMaker\\Controllers\\Admin\\WP\\PluginsPage';

        if (class_exists($pluginsPage, false) || ! class_exists('PopupMaker\\Plugin\\Controller')) {
            return;
        }

        $inertController = new class(null) extends \PopupMaker\Plugin\Controller {
            /**
             * Keep the replacement controller registered but disabled.
             *
             * @return bool
             */
            public function controller_enabled()
            {
                return false;
            }

            /**
             * Register no hooks.
             *
             * @return void
             */
            public function init()
            {
            }
        };

        class_alias(get_class($inertController), $pluginsPage);
    },
    PHP_INT_MIN
);

// Remove the Error Log tab from Tools.
add_filter(
    'pum_tools_tabs',
    static function ($tabs) {
        unset($tabs['error_log']);

        return $tabs;
    }
);

// Disable vendor notices, tips, and telemetry.
add_filter(
    'popmake_get_option',
    static function ($value, $key, $default) {
        if (in_array($key, ['disable_notices', 'disable_tips'], true)) {
            return true;
        }

        if ('telemetry' === $key) {
            return false;
        }

        return $value;
    },
    PHP_INT_MAX,
    3
);

// Disable all review-request triggers.
add_filter('pum_reviews_triggers', '__return_empty_array', PHP_INT_MAX, 0);

// Remove the commercial submenu and external support iframe page.
add_filter(
    'pum_admin_pages',
    static function ($pages) {
        unset(
            $pages['extensions'],
            $pages['support']
        );

        /*
         * Popup Maker outputs a hard-coded upsell template at the end of its
         * Settings page, outside PUM_Upsell and its official kill switch.
         * Wrap the page callback and remove that template together with the
         * script that injects it into every settings tab.
         */
        if (isset($pages['settings']['callback']) && is_callable($pages['settings']['callback'])) {
            $settingsCallback = $pages['settings']['callback'];

            $pages['settings']['callback'] = static function () use ($settingsCallback) {
                ob_start();
                call_user_func($settingsCallback);
                $output = ob_get_clean();

                if (false === $output) {
                    return;
                }

                $templateStart = strpos($output, '<template id="pum-pro-upsell-tpl">');

                if (false !== $templateStart) {
                    $scriptEnd = strpos($output, '</script>', $templateStart);

                    if (false !== $scriptEnd) {
                        $scriptEnd += strlen('</script>');
                        $output = substr($output, 0, $templateStart) . substr($output, $scriptEnd);
                    }
                }

                echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            };
        }

        return $pages;
    },
    PHP_INT_MAX
);

// Remove the Go Pro settings tab and its fields.
add_filter(
    'pum_settings_tabs',
    static function ($tabs) {
        unset($tabs['go-pro']);

        return $tabs;
    },
    PHP_INT_MAX
);
add_filter(
    'pum_settings_tab_sections',
    static function ($sections) {
        unset($sections['go-pro']);

        return $sections;
    },
    PHP_INT_MAX
);
add_filter(
    'pum_settings_fields',
    static function ($fields) {
        unset($fields['go-pro']);

        return $fields;
    },
    PHP_INT_MAX
);

// Remove promotional alerts while preserving operational warnings.
add_filter(
    'pum_alert_list',
    static function ($alerts) {
        $exactCodes = [
            'pum_telemetry_notice',
            'review_request',
            'pum_tip_alert',
        ];

        $prefixes = [
            'pm_upsell_',
            'pm_feat_',
            'pm_tip_',
            'pum_notice_',
        ];

        return array_values(
            array_filter(
                $alerts,
                static function ($alert) use ($exactCodes, $prefixes) {
                    $code = isset($alert['code']) ? (string) $alert['code'] : '';

                    if (in_array($code, $exactCodes, true)) {
                        return false;
                    }

                    foreach ($prefixes as $prefix) {
                        if (0 === strpos($code, $prefix)) {
                            return false;
                        }
                    }

                    return true;
                }
            )
        );
    },
    PHP_INT_MAX
);

// Keep the Popup Analytics dashboard widget, but hide its Pro promotion.
add_action(
    'admin_head-index.php',
    static function () {
        ?>
        <style id="popup-maker-admin-cleanup-dashboard">
            .pum-upgrade-box {
                display: none !important;
            }
        </style>
        <?php
    }
);

// Remove translation prompts and the one-time activation redirect.
add_action(
    'plugins_loaded',
    static function () {
        remove_filter(
            'pum_alert_list',
            ['PUM_Utils_Alerts', 'translation_request'],
            10
        );

        remove_action(
            'admin_init',
            ['PUM_Admin_Onboarding', 'welcome_redirect']
        );
    },
    99
);
