<?php

/*
 * Plugin Name: Profile stages with Query Monitor (DBG)
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

$qm_profiling_stage_hooks = [
    'bootstrap' => [
        // Too early for QM 'muplugins_loaded',
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
        // This is a filter 'the_posts',
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
    foreach ($hooks as $index => $hook) {
        if ($index === 0) {
            add_action(
                $hook,
                static function () use ($stage) {
                    do_action('qm/start', $stage);
                },
                PHP_INT_MIN,
                0
            );
        }
        $is_last = $index === count($hooks) - 1;
        add_action(
            $hook,
            static function () use ($stage, $hook, $is_last) {
                do_action('qm/lap', $stage, $hook);
                if ($is_last) {
                    do_action('qm/stop', $stage);
                }
            },
            PHP_INT_MAX,
            0
        );
    }
}
