<?php

/*
 * Plugin Name: Disable query vars parameters
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// permalink_structure must be enabled
add_action(
    'parse_request',
    static function ($wp) {
        $is_pretty_rest_request = isset($wp->query_vars['rest_route'])
            && !array_key_exists('rest_route', $_GET);
        if (is_admin() || $is_pretty_rest_request) {
            return;
        }
        $whitelist = [
            's', // Search
            'preview', // Post preview
        ];
        $requested_query_vars = array_intersect(
            array_keys($_GET),
            $wp->public_query_vars
        );
        $blocked_query_vars = array_diff(
            $requested_query_vars,
            $whitelist
        );
        if ($blocked_query_vars !== []) {
            $wp->query_vars = ['error' => '404'];
        }
    },
    0,
    1
);
