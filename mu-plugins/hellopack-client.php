<?php

/*
 * Plugin Name: Disable HelloPack on frontend
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'option_active_plugins',
    static function ($plugins) {
        if (
            (defined('WP_CLI') && WP_CLI)
            || (defined('DOING_CRON') && DOING_CRON)
            || is_admin()
            || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)
            || (defined('REST_REQUEST') && REST_REQUEST)
        ) {
            return $plugins;
        }

        if (in_array('hellopack-client/hellopack-client.php', $plugins, true)) {
            $plugins = array_values(array_diff($plugins, ['hellopack-client/hellopack-client.php']));
        }

        return $plugins;
    },
    1,
    1
);
