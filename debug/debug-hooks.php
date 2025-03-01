<?php

/*
 * Plugin Name: Log all triggered hooks (DBG)
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'all',
    static function () {
        $skip_hooks = [
            'gettext',
            'gettext_default',
            'gettext_with_context',
            'gettext_with_context_default'
        ];

        $log_path = WP_CONTENT_DIR.'/debug-hooks.log';
        $args = func_get_args();
        $hook_name = $args[0];
        $value = isset($args[1]) ? $args[1] : null;

        if (in_array($hook_name, $skip_hooks, true)) {
            return;
        }

        file_put_contents(
            $log_path,
            sprintf(
                '%.2f %s@%.4f %s %s %s =%s%c',
                microtime(true),
                php_sapi_name() === 'cli' ? 'CLI' : $_SERVER['REMOTE_ADDR'],
                $_SERVER['REQUEST_TIME_FLOAT'],
                $_SERVER['REQUEST_METHOD'],
                $_SERVER['REQUEST_URI'],
                $hook_name,
                is_string($value)
                    ? $value
                    : '<'.(is_object($value)
                        ? get_class($value)
                        : (is_bool($value)
                            ? ($value ? 'TRUE' : 'FALSE')
                            : gettype($value))).'>',
                10
            ),
            FILE_APPEND | LOCK_EX
        );
    },
    10,
    0
);
