<?php

/*
 * Plugin Name: Prevent space in username
 * Plugin URI: https://core.trac.wordpress.org/ticket/44690
 */

add_filter(
    'sanitize_user',
    static function($username, $raw_username, $strict) {
        if ($strict) {
            return preg_replace('/\s+/', '', $username);
        }
        return $username;
    },
    10,
    3
);
