<?php

/*
 * Plugin Name: Move WP User Activity plugin menu under Dashboard
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'wp_user_activity_menu_humility',
    '__return_true',
    10,
    0
);
