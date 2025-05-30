<?php

/*
 * Plugin Name: Yoast SEO plugin settings
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// Remove JSON for Linking Data.
// https://json-ld.org/
// https://developers.google.com/search/docs/guides/intro-structured-data
add_filter(
    'wpseo_json_ld_output',
    '__return_empty_array',
    10,
    0
);

// Disable HelpScout Beacon.
add_action(
    'admin_enqueue_scripts',
    static function () {
        wp_dequeue_script('yoast-seo-help-scout-beacon');
    },
    99,
    0
);
add_filter(
    'wpseo_helpscout_show_beacon',
    '__return_false',
    10,
    0
);

// Hide Premium Upsell elements and dim sidebar.
add_action(
    'admin_enqueue_scripts',
    static function ($hook) {
        if (strpos($hook, 'wpseo_') === false) {
            return;
        }
        $style = '.wp-admin .yoast_premium_upsell, .yoast-button-upsell { display:none !important; }';
        $style .= '.wp-admin #sidebar-container { opacity: 0.30; }';
        wp_add_inline_style('wp-admin', $style);
    },
    20,
    1
);

// Remove Premium pages.
add_filter(
    'wpseo_submenu_pages',
    static function ($submenuPages) {
        $premiumPages = [
            'wpseo_licenses',
            'wpseo_redirects',
            'wpseo_workouts',
            'wpseo_page_academy',
            'wpseo_page_support',
        ];
        foreach ($submenuPages as $pageIndex => $submenuPage) {
            // Fifth element is $page_slug
            if (in_array($submenuPage[4], $premiumPages, true)) {
                unset($submenuPages[$pageIndex]);
            }
        }
        return $submenuPages;
    },
    99,
    1
);
