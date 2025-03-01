<?php

/*
 * Plugin Name: Log external HTTP requests (DBG)
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'http_api_debug',
    static function ($response, $context, $class, $parsedArgs, $url) {
        if ($context !== 'response' || $class !== 'Requests' || is_wp_error($response)) {
            return;
        }
        error_log(
            sprintf(
                '%s: %s (%s)',
                'WordPress external HTTP request',
                $url,
                wp_json_encode($parsedArgs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            )
        );
    },
    100,
    5
);
