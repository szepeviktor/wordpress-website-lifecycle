<?php

/*
 * Plugin Name: Log Disqus API errors
 */

add_filter(
    'http_response',
    static function ($response, $parsed_args, $url) {
        if (
            strpos($url, 'https://disqus.com/api/') === 0
            && wp_remote_retrieve_response_code($response) !== 200
        ) {
            error_log('Disqus API error: ' . wp_remote_retrieve_body($response));
        }
        return $response;
    },
    10,
    3
);
