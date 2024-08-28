<?php

/*
 * Plugin Name: Metronet Tag Manager settings
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter('option_metronet_tag_manager', static function ($value) {
    // Read CookieYes cookie
    if (!isset($_COOKIE['cookieyes-consent'])) {
        return false;
    }

    return in_array('analytics:yes', explode(',', $_COOKIE['cookieyes-consent']), true)
        ? $value
        : false;
}, 20, 1);
