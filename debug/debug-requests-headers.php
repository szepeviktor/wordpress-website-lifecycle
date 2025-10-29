<?php

/*
 * Plugin Name: Log HTTP request headers (DBG)
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

if (isset($_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'])) {
    $log_item = sprintf("[%s] %s --- HTTP headers\n", date('c'), $_SERVER['REMOTE_ADDR']);
    $log_item .= sprintf("%s %s %s\n", $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['SERVER_PROTOCOL']);
    foreach (apache_request_headers() as $k => $v) {
        $log_item .= sprintf("%s: %s\n", $k, $v);
    }
    file_put_contents(
        __DIR__.'/wp-content/debug-requests-headers.log',
        $log_item,
        FILE_APPEND | LOCK_EX
    );
}
