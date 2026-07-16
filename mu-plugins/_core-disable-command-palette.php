<?php

/*
 * Plugin Name: Disable command palette
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'admin_init',
    static function () {
        remove_action('admin_enqueue_scripts', 'wp_enqueue_command_palette_assets');
    },
    10,
    0
);
