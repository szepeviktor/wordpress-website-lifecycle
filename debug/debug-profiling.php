<?php

/*
 * Plugin Name: Profiling (DBG)
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// Output time and memory usage.
array_map(
    static function ($hook) {
        add_action(
            $hook,
            static function () use ($hook) {
                if (
                    php_sapi_name() === 'cli'
                    || wp_doing_cron()
                    || wp_doing_ajax()
                    || wp_is_json_request()
                    || is_admin()
                ) {
                    return;
                }
                $time = timer_float();
                $mem = round(memory_get_usage(false) / 1048576, 0);
                add_action(
                    'shutdown',
                    static function () use ($hook, $time, $mem) {
                        if (is_robots()) {
                            return;
                        }
                        printf(
                            '%c<!-- Profiling: [%.03f] %s - mem %d MB -->',
                            10,
                            $time,
                            $hook,
                            $mem
                        );
                    },
                    11,
                    0
                );
            },
            PHP_INT_MAX,
            0
        );
    },
    [
        'plugins_loaded',
        'setup_theme',
        'after_setup_theme',
        'init',
        'wp_loaded',
        'send_headers',
        'template_redirect',
        'wp_head',
        'loop_start',
        'loop_end',
        'wp_footer',
    ]
);
