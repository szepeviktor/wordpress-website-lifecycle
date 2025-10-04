<?php

/*
 * Plugin Name: Remove Polylang wizard
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'plugins_loaded',
    static function () {
        class PLL_Wizard
        {
            public static function start_wizard($network_wide)
            {
            }
        }
    },
    -1,
    0
);
