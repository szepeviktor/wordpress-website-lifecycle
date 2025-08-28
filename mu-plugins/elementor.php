<?php

/*
 * Plugin Name: Remove admin notices from Elementor
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'init',
    static function () {
        require_once __DIR__.'/elementor/Admin_Notices.php';
        require_once __DIR__.'/elementor/Feedback.php';
        require_once __DIR__.'/elementor/Promotions.php';
    },
    // Before Elementor\Plugin::__construct
    -1,
    0
);
