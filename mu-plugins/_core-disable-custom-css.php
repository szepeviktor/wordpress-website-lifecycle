<?php

/*
 * Plugin Name: Disable Customizer CSS
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'customize_register',
    static function ($manager) {
        $manager->remove_section('custom_css');
    },
    20,
    1
);
