<?php

/*
 * Plugin Name: Jetpack plugin settings
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

// Disable Jetpack promotions
// https://github.com/wearerequired/hide-jetpack-promotions/blob/master/hide-jetpack-promotions.php
