<?php

/*
 * Plugin Name: Remove WooCommerce guided tour videos
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'current_screen',
    static function () {
        get_current_screen()->remove_help_tab('woocommerce_guided_tour_tab');
    },
    51,
    0
);
