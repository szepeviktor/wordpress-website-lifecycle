<?php

/*
 * Plugin Name: Log WP-Cron (HTTP) callback output and errors
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'cron_request',
    static function ($cron_request_array) {
        add_action(
            'http_api_debug',
            static function ($response) {
                static $run_once = 0;
                $run_once += 1;
                if ($run_once !== 1) {
                    return;
                }
                if (is_wp_error($response)) {
                    error_log(sprintf(
                        'WP-Cron error: (%s) "%s"',
                        $response->get_error_code(),
                        $response->get_error_message()
                    ));
                    return;
                }
                if ($response['body'] === '') {
                    return;
                }
                error_log(sprintf('WP-Cron output: "%s"', $response['body']));
            },
            10,
            1
        );
        return $cron_request_array;
    },
    10,
    1
);
