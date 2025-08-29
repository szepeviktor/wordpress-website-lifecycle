<?php

/*
 * Plugin Name: Remove admin notices from Elementor
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'init',
    static function () {
        if (!did_action('elementor/loaded')) {
            return;
        }
        require_once __DIR__.'/elementor/Admin_Notices.php';
        require_once __DIR__.'/elementor/Feedback.php';
        require_once __DIR__.'/elementor/Promotions.php';
    },
    // Before Elementor\Plugin::__construct
    -1,
    0
);

// Disable tracking.
add_filter(
    'pre_option_elementor_allow_tracking',
    '__return_empty_string',
    10,
    0
);
