<?php

/*
 * Plugin Name: Remove Address Book submenu from Flamingo plugin
 */

add_action(
    'admin_menu',
    static function () {
        remove_submenu_page('flamingo', 'flamingo');
    },
    9,
    0
);
