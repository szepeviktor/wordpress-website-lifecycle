<?php

/*
 * Plugin Name: Disallow core, plugin, theme installation
 * Description: WordPress is managed with Composer
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'user_has_cap',
    static function ($capabilities) {
        return array_merge(
            $capabilities,
            [
                'install_plugins' => false,
                'install_themes' => false,
                // 'switch_themes' => false,
                'delete_plugins' => false,
                'delete_themes' => false,
                'update_core' => false,
                'update_plugins' => false,
                'update_themes' => false,
                'update_languages' => false,
                'install_languages' => false,
            ]
        );
    },
    PHP_INT_MAX,
    1
);
