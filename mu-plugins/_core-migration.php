<?php

/*
 * Plugin Name: Migration source server guard
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// Block WP-Cron
if (defined('DOING_CRON')) {
    error_log('WP-Cron is blocked.');
    exit;
}

// Display "Old server" on login page
add_filter(
    'login_message',
    static function ($message) {
        return '<p class="message">Old server</p>'.$message;
    },
    0,
    1
);

// Block outbound HTTP requests
add_filter(
    'pre_http_request',
    static function ($response, $parsed_args, $url) {
        error_log('Outbound HTTP requests are blocked: '.$url);
        return new WP_Error('outbound_http_blocked');
    },
    PHP_INT_MAX,
    3
);

// Block email sending
add_filter(
    'pre_wp_mail',
    static function ($return, $atts) {
        error_log('Email sending is blocked: '.var_export($atts, true));
        return false;
    },
    PHP_INT_MAX,
    2
);

// Block Action Scheduler
add_action(
    'init',
    static function () {
        if (!class_exists('ActionScheduler')) {
            return;
        }

        remove_action(
            'action_scheduler_run_queue',
            [ActionScheduler::runner(), 'run']
        );
    },
    PHP_INT_MAX,
    0
);
add_filter(
    'action_scheduler_allow_async_request_runner',
    '__return_false',
    10,
    0
);
