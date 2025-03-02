<?php

/*
 * Plugin Name: Disable login
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'wp_authenticate_user',
    static function () {
        return new WP_Error('authentication_failed', __( 'Login disabled.'));
    },
    0,
    0
);
