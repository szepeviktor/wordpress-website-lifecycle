<?php

/*
 * Insert this snippet at the top of wpdb::_do_query()
 *
 * wp-includes/class-wpdb.php
 */

file_put_contents(
    WP_CONTENT_DIR.'/debug-queries.log',
    sprintf(
        '%.2f %s@%.4f %s %s %s%c',
        microtime(true),
        php_sapi_name() === 'cli' ? 'CLI' : $_SERVER['REMOTE_ADDR'],
        $_SERVER['REQUEST_TIME_FLOAT'],
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI'],
        $query,
        10
    ),
    FILE_APPEND | LOCK_EX
);
