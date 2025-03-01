<?php

/*
 * Insert these snippets into the first line of the hook functions.
 *
 * wp-includes/plugin.php
 */

// function apply_filters( $hook_name, $value, ...$args ) {
if (!in_array($hook_name, ['gettext', 'gettext_default', 'gettext_with_context', 'gettext_with_context_default']))
wp_debug_log_hook_triggered('*'.$hook_name, $value);

// function apply_filters_ref_array( $hook_name, $args ) {
if (!in_array($hook_name, ['gettext', 'gettext_default', 'gettext_with_context', 'gettext_with_context_default']))
wp_debug_log_hook_triggered('*'.$hook_name, isset($args[0]) ? $args[0] : null);

// function do_action( $hook_name, ...$arg ) {
wp_debug_log_hook_triggered($hook_name, isset($arg[0]) ? $arg[0] : null);

// function do_action_ref_array( $hook_name, $args ) {
wp_debug_log_hook_triggered($hook_name, isset($args[0]) ? $args[0] : null);

/*
 * Copy this function to wp-config.
 *
 * wp-config.php
 */

function wp_debug_log_hook_triggered($hook_name, $value) {
    file_put_contents(
        WP_CONTENT_DIR.'/debug-hooks.log',
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
                    : is_bool($value)
                        ? ($value ? 'TRUE' : 'FALSE')
                        : gettype($value)).'>',
            10
        ),
        FILE_APPEND | LOCK_EX
    );
}
