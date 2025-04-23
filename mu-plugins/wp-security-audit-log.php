<?php

/*
 * Plugin Name: WP Activity Log
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// Enable NOFS version in wp-config: define('WSAL_NOFS_TOOL_PATH', true);

// Remove menu items
add_filter(
    'wsal_skip_views',
    static function ($skips) {
        $skips[] = 'WSAL_Views_Help';
        $skips[] = '\WSAL\Views\Premium_Features';
        return $skips;
    },
    10,
    1
);
add_action(
    'admin_menu',
    static function () {
        remove_submenu_page('wsal-auditlog', 'upgrade');
    },
    20,
    0
);

// Hide plugin
add_filter(
    'pre_option_wsal_restrict-plugin-settings',
    static function () {
        return 'only_me';
    },
    PHP_INT_MAX,
    0
);
add_filter(
    'pre_option_wsal_only-me-user-id',
    static function () {
        // User ID
        return 1;
    },
    PHP_INT_MAX,
    0
);
