<?php

/*
 * Plugin Name: FluentSMTP settings
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// Hide menu items
add_action(
    'fluent_mail_loading_app',
    static function () {
        wp_add_inline_script(
            'fluent_mail_admin_app_boot',
            <<<'JS'
window.FluentMail.addFilter(
    'fluentmail_top_menus',
    'remove_about_and_documentation',
    function (menus) {
        return menus.filter(function (menu) {
            return !['support', 'docs'].includes(menu.route);
        });
    }
);
JS
        );
    },
    10,
    0
);

// Disable email sending in non-production environments
add_action(
    'plugins_loaded',
    static function () {
        if (wp_get_environment_type() !== 'production') {
            define('FLUENTMAIL_SIMULATE_EMAILS', true);
        }
    },
    0,
    0
);

// Hide notice on the plugin-install search screen
add_action(
    'admin_head-plugin-install.php',
    static function () {
        echo '<style>.fluent_smtp_box { display: none !important; }</style>';
    }
);

// Suppress the subscription prompt
add_filter(
    'pre_option__fluentsmtp_dismissed_timestamp',
    static function () {
        return time();
    }
);

// Remove the promotion from admin footer
add_filter(
    'admin_footer_text',
    static function ($text) {
        if (empty($_GET['page']) || $_GET['page'] !== 'fluent-mail') {
            return $text;
        }

        return '';
    },
    PHP_INT_MAX
);
