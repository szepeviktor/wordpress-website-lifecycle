<?php

/*
 * Plugin Name: Log response time (DBG)
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'shutdown',
    static function () {
        $time = timer_float();
        $uri = isset($_SERVER['REQUEST_URI'])
            ? wp_json_encode($_SERVER['REQUEST_URI'], JSON_UNESCAPED_SLASHES)
            : 'CLI';
        $message = sprintf('Response time = %.03f sec, %s', $time, $uri);
        switch (true) {
            case defined('WP_CLI') && WP_CLI:
                WP_CLI::debug($message, 'memory-usage');
                break;
            case wp_doing_cron() && php_sapi_name() === 'cli':
                // No output during WP-Cron run from CLI.
                break;
            default:
                file_put_contents(
                    WP_CONTENT_DIR.'/debug-response-time.log',
                    $message."\n",
                    FILE_APPEND | LOCK_EX
                );
                break;
        }
    },
    -1,
    0
);
