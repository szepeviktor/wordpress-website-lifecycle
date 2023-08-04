<?php

/*
 * Plugin Name: Deprecation logger
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// Log usage of deprecated WordPress features.
array_map(
    static function ($hook) {
        add_action(
            $hook,
            static function ($deprecated) use ($hook) {
                error_log(sprintf('WordPress %s: %s', $hook, $deprecated));
            },
            0,
            1
        );
    },
    [
        'deprecated_function_run',
        'deprecated_constructor_run',
        'deprecated_file_included',
        'deprecated_argument_run',
        'deprecated_hook_run',
    ]
);
