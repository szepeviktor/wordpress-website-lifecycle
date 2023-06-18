<?php

/*
 * Plugin Name: Disable WPBakery Visual Composer plugin updates
 */

add_action(
    'plugins_loaded',
    static function () {
        global $vc_manager;
        if (! method_exists($vc_manager, 'disableUpdater')) {
            return;
        }

        $vc_manager->disableUpdater(true);
        add_filter(
            'pre_option_wpb_js_js_composer_purchase_code',
            '__return_true',
            10,
            0
        );
    },
    10,
    0
);
