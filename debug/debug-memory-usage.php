<?php

/*
 * Plugin Name: Log memory usage (DBG)
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'shutdown',
    static function () {
        $peakUsage = memory_get_peak_usage(true);
        // Report above 20 MB.
        if ($peakUsage < 20 * 1024 * 1024) {
            return;
        }
        $uri = 'CLI';
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = wp_json_encode($_SERVER['REQUEST_URI'], JSON_UNESCAPED_SLASHES);
        }
        error_log(sprintf('Peak memory usage = %d bytes, %s', $peakUsage, $uri));
    },
    -1,
    0
);
