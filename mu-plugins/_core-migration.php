<?php

/*
 * Plugin Name: Migration
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// Block WP-Cron
if (defined('DOING_CRON')) {
    error_log('WP-Cron is blocked.');
    exit;
}

// Display "Old server" on login page
add_filter(
    'login_message',
    static function ($message) {
        return '<p class="message">Old server</p>'.$message;
    },
    0,
    1
);
