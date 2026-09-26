<?php

/*
 * Plugin Name: HTTP/2 Login Guard
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'login_init',
    static function () {
        $serverProtocol = $_SERVER['SERVER_PROTOCOL'] ?? '';
        // $serverProtocol = $_SERVER['HTTP_CLOUDFRONT_VIEWER_HTTP_VERSION'] ?? $serverProtocol;
        // $serverProtocol = $_SERVER['HTTP_X_VIEWER_HTTP_VERSION'] ?? $serverProtocol;

        if (strpos($serverProtocol, 'HTTP/2') === 0) {
            return;
        }

        status_header(403);
        nocache_headers();
        header('Content-Type: text/html; charset=utf-8');

        echo '<!doctype html><meta charset="utf-8"><p>Please log in with a modern HTTP/2-capable browser.</p>';

        exit;
    },
    0,
    0
);
