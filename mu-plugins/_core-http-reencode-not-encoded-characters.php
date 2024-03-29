<?php

/*
 * Plugin Name: Re-encode not encoded characters in query string
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'init',
    static function () {
        $parsed_url = parse_url($_SERVER['REQUEST_URI']);
        if (!isset($parsed_url['path']) || $parsed_url['path'] === '') {
            return;
        }

        $request_uri = $parsed_url['path'];
        $query_string = '';
        if (isset($parsed_url['query'])) {
            // + character from Facebook Ads
            // * character from Google Analytics
            $query_parameters = [];
            parse_str($parsed_url['query'], $query_parameters);
            // RFC 3986
            $request_uri = add_query_arg(rawurlencode_deep($query_parameters), $request_uri);
            $query_string = ltrim(add_query_arg(rawurlencode_deep($query_parameters), ''), '?');
        }

        $_SERVER['REQUEST_URI'] = $request_uri;
        $_SERVER['QUERY_STRING'] = $query_string;
    },
    0,
    0
);
