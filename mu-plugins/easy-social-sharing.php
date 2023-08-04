<?php

/*
 * Plugin Name: Disable Easy Social Share Buttons plugin updates
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'plugins_loaded',
    static function () {
        global $essb_manager;
        if (method_exists($essb_manager, 'disableUpdater')) {
            $essb_manager->disableUpdates(true);
        }
    },
    10,
    0
);
