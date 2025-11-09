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
        $uri = isset($_SERVER['REQUEST_URI'])
            ? wp_json_encode($_SERVER['REQUEST_URI'], JSON_UNESCAPED_SLASHES)
            : 'CLI';
        $message = sprintf('Peak memory usage = %d bytes, %s', $peakUsage, $uri);
        switch (true) {
            case defined('WP_CLI') && WP_CLI:
                WP_CLI::debug($message, 'memory-usage');
                break;
            case wp_doing_cron() && php_sapi_name() === 'cli':
                // No output during WP-Cron run from CLI.
                break;
            default:
                error_log($message);
                break;
        }
    },
    -1,
    0
);
