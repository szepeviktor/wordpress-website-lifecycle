<?php

/*
 * Plugin Name: Remove WordPress branding
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// Remove WordPress logo admin bar menu
add_action(
    'add_admin_bar_menus',
    static function () {
        remove_action('admin_bar_menu', 'wp_admin_bar_wp_menu', 10);
    },
    10,
    0
);

// Block access to about WordPress pages.
array_map(
    static function ($page) {
        add_action(
            $page,
            static function () {
                wp_redirect(admin_url());
                exit;
            },
            0,
            0
        );
    },
    [
        'load-about.php',
        'load-credits.php',
        'load-freedoms.php',
        'load-privacy.php',
        'load-contribute.php',
    ]
);

// Remove footer text
add_action(
    'admin_footer_text',
    '__return_empty_string',
    0,
    0
);
