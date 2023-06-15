<?php

/*
 * Plugin Name: Log not WordPress friendly URL query strings
 */

add_action(
    'parse_request',
    static function () {
        if (empty($_SERVER['QUERY_STRING'])) {
            return;
        }

        if (strpos($_SERVER['QUERY_STRING'], '+') !== false) {
            error_log(sprintf('Non-WordPress query string: plus_encoded_whitespace ("%s")', $_SERVER['QUERY_STRING']));
            return;
        }

        if (preg_match('/%[[:xdigit:]]?[a-f]/', $_SERVER['QUERY_STRING']) === 1) {
            error_log(sprintf('Non-WordPress query string: lower_case_hexadecimal_digit ("%s")', $_SERVER['QUERY_STRING']));
            return;
        }
    },
    0,
    0
);
