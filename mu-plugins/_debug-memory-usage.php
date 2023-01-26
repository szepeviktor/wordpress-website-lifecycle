<?php

/*
 * Plugin Name: Log memory usage
 */

add_action(
    'shutdown',
    static function () {
        $peak_usage = memory_get_peak_usage(true);
        // Report above 20 MB.
        if ($peak_usage < 20 * 1024 * 1024) {
            return;
        }
        $uri = 'CLI';
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = wp_json_encode($_SERVER['REQUEST_URI'], JSON_UNESCAPED_SLASHES);
        }
        error_log(sprintf('Peak memory usage = %s %s', $peak_usage, $uri));
    },
    -1,
    0
);
