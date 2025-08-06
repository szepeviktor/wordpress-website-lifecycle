<?php

/*
 * Plugin Name: Cron event logger (DBG)
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

if (defined('DOING_CRON') && DOING_CRON) {
    add_action(
        'all',
        static function ($hook_name) {
            // From wp-cron.php
            global $hook;

            if ($hook_name !== $hook) {
                return;
            }

            error_log('[CRON] Event running: ' . $hook_name);
        },
        PHP_INT_MAX,
        1
    );
}
