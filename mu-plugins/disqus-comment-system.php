<?php

/*
 * Plugin Name: Log Disqus API errors
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'http_response',
    static function ($response, $parsedArgs, $url) {
        if (
            strpos($url, 'https://disqus.com/api/') === 0
            && wp_remote_retrieve_response_code($response) !== 200
        ) {
            error_log(sprintf('Disqus API error: %s', wp_remote_retrieve_body($response)));
        }
        return $response;
    },
    10,
    3
);
