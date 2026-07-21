<?php

/*
 * Plugin Name: Disable query vars parameters
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// permalink_structure must of enabled
add_action(
    'parse_request',
    static function ($wp) {
        if (is_admin()) {
            return;
        }
        $requested_query_vars = array_intersect(
            array_keys($_GET),
            $wp->public_query_vars
        );
        if ($requested_query_vars !== []) {
            $wp->query_vars = ['error' => '404'];
        }
    },
    0,
    1
);
