<?php

/*
 * Plugin Name: The Events Calendar
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// Load tribe-common translations
add_action(
    'init',
    function () {
        load_plugin_textdomain('tribe-common', false, WP_LANG_DIR);
    },
    10,
    0
);
