<?php

/*
 * Plugin Name: Profile stages with Query Monitor
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

$qm_profiling_stage_hooks = [
    'bootstrap' => [
        'muplugins_loaded',
        'plugins_loaded',
        'setup_theme',
        'after_setup_theme',
        'init',
        'wp_loaded',
    ],
    'main_query' => [
        'parse_request',
        'send_headers',
        // Fires multiple times 'pre_get_posts',
        'the_posts',
        'wp',
    ],
    'template' => [
        'template_redirect',
        // This is a filter 'template_include',
        'wp_head',
        'loop_start',
        'loop_end',
        'wp_footer',
    ],
];
foreach ($qm_profiling_stage_hooks as $stage => $hooks) {
    foreach ($hooks as $hook) {
        add_action(
            $hook,
            static function () use ($stage, $hook) {
                do_action('qm/start', sprintf('%s/%s', $stage, $hook));
            },
            PHP_INT_MIN,
            0
        );
        add_action(
            $hook,
            static function () use ($stage, $hook) {
                do_action('qm/stop', sprintf('%s/%s', $stage, $hook));
            },
            PHP_INT_MAX,
            0
        );
    }
}
