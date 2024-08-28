<?php

/*
 * Plugin Name: Metronet Tag Manager settings
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter('option_metronet_tag_manager', static function ($value) {
    if (is_admin()) {
        return $value;
    }

    $defaults = [
        'code' => '',
        'code_head' => '',
        'variables' => [
            0 => ['name' => 'title', 'value' => '%post_title%'],
            1 => ['name' => 'author', 'value' => '%author_name%'],
            2 => ['name' => 'wordcount', 'value' => '%wordcount%'],
            3 => ['name' => 'logged_in', 'value' => '%logged_in%'],
            4 => ['name' => 'page_id', 'value' => '%page_id%'],
            5 => ['name' => 'post_date', 'value' => '%post_date%'],
            6 => ['name' => 'post_type', 'value' => '%post_type%'],
        ],
        'external_variables' => [],
        'is_post_enabled' => 'on',
        'enable_tiny_mce' => 'on',
        'enable_gutenberg' => 'on',
    ];

    // Read CookieYes cookie
    if (!isset($_COOKIE['cookieyes-consent'])) {
        return $defaults;
    }

    return in_array('analytics:yes', explode(',', $_COOKIE['cookieyes-consent']), true)
        ? $value
        : $defaults;
}, 20, 1);
