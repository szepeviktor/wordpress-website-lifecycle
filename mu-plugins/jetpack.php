<?php

/*
 * Plugin Name: Jetpack plugin settings
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// Remove blacklisted Jetpack modules.
add_filter(
    'jetpack_get_available_modules',
    static function ($modules) {
        $blacklistedModules = [
            'monitor',
            'photon-cdn',
            'photon',
            'post-by-email',
            'protect',
            'seo-tools',
            'sitemaps',
            'sso',
            'vaultpress',
            'verification-tools',
        ];
        foreach ($blacklistedModules as $blacklistedModule) {
            unset($modules[$blacklistedModule]);
        }
        return $modules;
    },
    PHP_INT_MAX,
    1
);

// List modules in jetpack plugin directory.
// grep -r -F 'Module Name:' modules/|sed -e 's#^modules/##; s#\.php: \* Module Name: #\t#'|column -t -s $'\t'|sort

// List available jetpack modules.
// wp option get jetpack_available_modules

// List active jetpack modules.
// wp option get jetpack_active_modules

/*
// Enable Jetpack Search only.
add_filter(
    'jetpack_get_available_modules',
    static function ($modules) {
        return array_intersect_key($modules, ['search' => true]);
    },
    PHP_INT_MAX,
    1
);
*/

/*
// Disable Jetpack promotions.
// https://github.com/wearerequired/hide-jetpack-promotions/blob/master/hide-jetpack-promotions.php
add_filter('jetpack_blaze_enabled', '__return_false', 10, 0);
add_filter('jetpack_just_in_time_msgs', '__return_false', 20, 0);
add_filter('jetpack_show_promotions', '__return_false', 20, 0);
*/

// Remove Newsletter dashboard widget
add_action(
    'wp_dashboard_setup',
    static function () {
        remove_meta_box('jetpack_newsletter_dashboard_widget', 'dashboard', 'side');
    },
    20,
    0
);
