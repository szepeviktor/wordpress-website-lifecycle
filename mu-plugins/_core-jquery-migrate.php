<?php

/*
 * Plugin Name: Never enqueue jQuery Migrate before WordPress 5.5
 */

add_action(
    'wp_default_scripts',
    static function ($scripts) {
        if (is_admin() || ! isset($scripts->registered['jquery'])) {
            return;
        }
        $scripts->registered['jquery']->deps = array_diff(
            $scripts->registered['jquery']->deps,
            ['jquery-migrate']
        );
    },
    10,
    1
);
