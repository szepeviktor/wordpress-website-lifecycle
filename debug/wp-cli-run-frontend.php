<?php

/*
 * Run WordPress front-end with WP-CLI.
 *
 * wp --url="https://example.com/" eval-file wp-cli-run-frontend.php
 */

/*
These have already ran
muplugins_loaded
plugins_loaded
setup_theme
after_setup_theme
init
wp_loaded
*/

// Add --skip-wordpress 
// WP_CLI::get_runner()->load_wordpress();
// then use https://make.wordpress.org/cli/handbook/references/internal-api/wp-cli-add-wp-hook/

// Log fired hooks
add_action(
    'all',
    static function () {
        $args = func_get_args();
        $hook_name = $args[0];
        $value = isset($args[1]) ? $args[1] : null;
        $formatted_value = (is_string($value) || is_numeric($value))
            ? $value
            : '<'.(is_object($value)
                ? get_class($value)
                : (is_bool($value)
                    ? ($value ? 'TRUE' : 'FALSE')
                    : gettype($value))).'>';
        printf("%s=%s\n", $hook_name, $formatted_value);
    },
    PHP_INT_MAX,
    0
);

// Display HTTP headers
add_filter(
    'wp_headers',
    static function ($headers) {
        var_export($headers);
        echo "\n";
        return $headers;
    },
    PHP_INT_MAX,
    1
);

wp();
define('WP_USE_THEMES', true);
require_once ABSPATH . WPINC . '/template-loader.php';
