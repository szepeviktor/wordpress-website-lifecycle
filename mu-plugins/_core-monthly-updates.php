<?php

/*
 * Plugin Name: Reschedule updates to once a month
 * Description: Benefit from not installing each version
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'plugins_loaded',
    static function () {
        add_filter(
            'cron_schedules',
            static function ($schedules) {
                $schedules['monthly'] = ['interval' => MONTH_IN_SECONDS, 'display' => __('Once Monthly')];
                return $schedules;
            },
            10,
            1
        );
        remove_action('init', 'wp_schedule_update_checks');
        add_action(
            'init',
            static function () {
                if ( ! wp_next_scheduled( 'wp_version_check' ) && ! wp_installing() ) {
                    wp_schedule_event( time(), 'monthly', 'wp_version_check' );
                }
                if ( ! wp_next_scheduled( 'wp_update_plugins' ) && ! wp_installing() ) {
                    wp_schedule_event( time(), 'monthly', 'wp_update_plugins' );
                }
                if ( ! wp_next_scheduled( 'wp_update_themes' ) && ! wp_installing() ) {
                    wp_schedule_event( time(), 'monthly', 'wp_update_themes' );
                }
            }
        );
    },
    10,
    0
);
