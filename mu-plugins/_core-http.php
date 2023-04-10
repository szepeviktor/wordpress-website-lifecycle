<?php

/*
 * Plugin Name: HTTP settings
 *
 * Use these constants to restrict outbound HTTP requests.
 *
 * define('WP_HTTP_BLOCK_EXTERNAL', true);
 * define('WP_ACCESSIBLE_HOSTS', 'api.wordpress.org');
 */

// Disable HTTPS detection by setting error-free result.
add_filter(
    'pre_wp_update_https_detection_errors',
    static function () {
        return new \WP_Error();
    },
    10,
    0
);

// Log failed external HTTP requests.
add_action(
    'http_api_debug',
    static function ($response, $context, $class, $parsed_args, $url) {
        if ('response' !== $context || 'Requests' !== $class || ! is_wp_error($response)) {
            return;
        }
        error_log(
            sprintf(
                'WordPress external HTTP request failed with message [%s:%s] %s (%s)',
                $response->get_error_code(),
                $response->get_error_message(),
                $url,
                json_encode($parsed_args, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            )
        );
    },
    99,
    5
);

// Debug external HTTP requests.
if (defined('WP_DEBUG') && WP_DEBUG) :
    add_action(
        'http_api_debug',
        static function ($response, $context, $class, $parsed_args, $url) {
            if ('response' !== $context || 'Requests' !== $class || is_wp_error($response)) {
                return;
            }
            error_log(
                sprintf(
                    '%s: %s (%s)',
                    'WordPress external HTTP request',
                    $url,
                    json_encode($parsed_args, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                )
            );
        },
        100,
        5
    );
endif;

// Send Pragma HTTP header only for HTTP/1.0 clients.
add_action(
    'send_headers',
    static function () {
        // https://github.com/php/php-src/blob/php-8.1.14/ext/session/session.c#L1208-L1212
        if (wp_get_server_protocol() === 'HTTP/1.0') {
            return;
        }
        header_remove('Pragma');
    },
    PHP_INT_MAX,
    0
);
