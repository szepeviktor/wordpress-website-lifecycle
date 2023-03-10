<?php

/*
 * Plugin Name: Disable SOGO Accessibility plugin (a11y) license check
 */

add_action(
    'wp_ajax_check_license',
    static function () {
        add_filter(
            'pre_http_request',
            static function () {
                return new WP_Error('sogo_license_check_disabled');
            },
            0,
            0
        );
    },
    10,
    0
);
