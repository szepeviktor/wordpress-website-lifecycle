<?php

/*
 * Plugin Name: Disable TGMPA
 */

// Disable TGMPA (procedural)
add_action(
    'after_setup_theme',
    static function () {
        remove_action('admin_init', 'tgmpa_load_bulk_installer');
        // EDIT here!
        remove_action('tgmpa_register', 'CUSTOM-FUNCTION');
    },
    PHP_INT_MAX,
    0
);

// Disable TGMPA (OOP)
add_action(
    'after_setup_theme',
    static function () {
        // EDIT here!
        global $wpoEngine;
        if (method_exists($wpoEngine, 'initRequiredPlugin')) {
            remove_action('admin_init', 'tgmpa_load_bulk_installer');
            remove_action('tgmpa_register', [$wpoEngine, 'initRequiredPlugin']);
        }
    },
    PHP_INT_MAX,
    0
);
