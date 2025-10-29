<?php

/*
 * Plugin Name: Log HTTP request headers (DBG)
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

function _core_debug_requests_headers()
{
    if (!isset($_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'])) {
        return;
    }
    $log_items = [
        sprintf('[%s] %s --- HTTP headers', date('c'), $_SERVER['REMOTE_ADDR']),
        sprintf('%s %s %s', $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['SERVER_PROTOCOL']),
    ];
    foreach (getallheaders() as $name => $value) {
        $log_items[] = sprintf('%s: %s', $name, $value);
    }
    file_put_contents(
        WP_CONTENT_DIR.'/debug-requests-headers.log',
        implode("\n", $log_items),
        FILE_APPEND | LOCK_EX
    );
}
_core_debug_requests_headers();
