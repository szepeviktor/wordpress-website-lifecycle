<?php

/*
 * Plugin Name: Prevent Redux Framework HTTP requests
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

$GLOBALS['redux_update_check'] = 1;
add_filter(
    'redux/ascend/aURL_filter',
    '__return_empty_string',
    10,
    0
);
add_filter(
    sprintf('%s_%s', 'get_user_option', 'r_tru_u_x'),
    static function () {
        return [
            'expires' => PHP_INT_MAX,
            'id' => '',
        ];
    },
    10,
    0
);
add_action(
    'after_setup_theme',
    static function () {
        remove_all_actions('wp_ajax_nopriv_redux_p');
        remove_all_actions('wp_ajax_redux_p');
    },
    10,
    0
);

// Search for 'opt_name' in the code
