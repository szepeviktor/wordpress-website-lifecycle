<?php

/*
 * Plugin Name: Log failed SQL queries (DBG)
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

define('SAVEQUERIES', true);

add_filter(
    'log_query_custom_data',
    static function ($query_data, $query) {
        global $wpdb;
        if ($wpdb->result === false) {
            error_log('Failed SQL query:' . $query);
        }
        return $query_data;
    },
    10,
    2
);
