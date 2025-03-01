<?php

/*
 * Insert these snippets into the first line of do_action and apply_filters functions.
 *
 * wp-includes/plugin.php:174
 * wp-includes/plugin.php:483
 */

// function apply_filters( $hook_name, $value, ...$args ) {
if (!in_array($hook_name, ['gettext', 'gettext_default', 'gettext_with_context', 'gettext_with_context_default']))
file_put_contents(
    WP_CONTENT_DIR.'/debug-hooks.log',
    sprintf(
        '%.2f %s@%.4f %s %s *%s =%s%c',
        microtime(true),
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['REQUEST_TIME_FLOAT'],
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI'],
        $hook_name,
        is_string($value)
            ? $value
            : '<'.(is_object($value)
                ? get_class($value)
                : is_bool($value)
                    ? ($value ? 'TRUE' : 'FALSE')
                    : gettype($value)).'>',
        10
    ),
    FILE_APPEND | LOCK_EX
);

// function do_action( $hook_name, ...$arg ) {
file_put_contents(
    WP_CONTENT_DIR.'/debug-hooks.log',
    sprintf(
        '%.2f %s@%.4f %s %s %s =%s%c',
        microtime(true),
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['REQUEST_TIME_FLOAT'],
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI'],
        $hook_name,
        is_string($arg[0])
            ? $arg[0]
            : '<'.(is_object($arg[0])
                ? get_class($arg[0])
                : is_bool($arg[0])
                    ? ($arg[0] ? 'TRUE' : 'FALSE')
                    : gettype($arg[0])).'>',
        10
    ),
    FILE_APPEND | LOCK_EX
);
