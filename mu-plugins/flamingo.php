<?php

/*
 * Plugin Name: Remove Address Book submenu from Flamingo plugin
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'admin_menu',
    static function () {
        remove_submenu_page('flamingo', 'flamingo');
    },
    9,
    0
);
