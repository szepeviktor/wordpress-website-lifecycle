<?php

/*
 * Plugin Name: Disallow Site Health
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// Remove empty cron hook: wp cron event delete wp_site_health_scheduled_check

// Reset default REST Site Health capabilities.
array_map(
    static function ($check) {
        add_filter(
            sprintf('site_health_test_rest_capability_%s', $check),
            static function () {
                return 'view_site_health_checks';
            },
            PHP_INT_MAX,
            0
        );
    },
    [
        // From WP_REST_Site_Health_Controller::register_routes
        'background_updates',
        'loopback_requests',
        'https_status',
        'dotorg_communication',
        'authorization_header',
        // Incorrectly named "directory_sizes"
        'debug_enabled',
        'directory_sizes',
        // Incorrectly named "page_cache"
        'view_site_health_checks',
        'page_cache',
    ]
);

// Revoke capability to access Site Health.
add_filter(
    'user_has_cap',
    static function ($capabilities) {
        return array_merge($capabilities, ['view_site_health_checks' => false]);
    },
    PHP_INT_MAX,
    1
);

/**
 * No-op WP_Site_Health class.
 */
class WP_Site_Health
{
    public function __construct() {}

    public static function get_instance() {}
}
