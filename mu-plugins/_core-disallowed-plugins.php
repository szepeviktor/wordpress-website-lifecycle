<?php

/*
 * Plugin Name: Disallow plugins
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

const HOSTING_DISALLOWED_PLUGINS = [
    'all-in-one-wp-security-and-firewall',
    'hello-dolly',
    'ithemes-security',
    'ithemes-security-pro',
    'jetpack',
    'limit-login-attempts-reloaded',
    'litespeed-cache',
    'loginizer',
    'ninjafirewall',
    'really-simple-ssl',
    'redirection',
    'sucuri-scanner',
    'svg-support',
    'updraftplus',
    'wordfence',
    'worker',
    'wp-fastest-cache',
    'wp-file-manager',
    'wp-rocket',
    'wpremote',
    'wps-hide-login',
    // From https://wpengine.com/support/disallowed-plugins/
    'adminer',
    'async-google-analytics',
    'backup',
    'backup-scheduler',
    'backupwordpress',
    'backwpup',
    'bad-behavior',
    'content-molecules',
    'contextual-related-posts',
    'db-cache-reloaded',
    'duplicator',
    'dynamic-related-posts',
    'ezpz-one-click-backup',
    'file-commander',
    'fuzzy-seo-booster',
    'gd-system-plugin',
    'gd-system-plugin.php',
    'google-xml-sitemaps-with-multisite-support',
    'hc-custom-wp-admin-url',
    'hcs.php',
    'hello.php',
    'hyper-cache',
    'jr-referrer',
    'jumpple',
    'missed-schedule',
    'nginx-champuru',
    'no-revisions',
    'ozh-who-sees-ads',
    'p3',
    'pluginsamonsters',
    'pluginsmonsters',
    'portable-phpmyadmin',
    'quick-cache',
    'quick-cache-pro',
    'recommend-a-friend',
    'seo-alrp',
    'si-captcha-for-wordpress',
    'similar-posts',
    'spamreferrerblock',
    'ssclassic',
    'sspro',
    'super-post',
    'superslider',
    'sweetcaptcha-revolutionary-free-captcha-service',
    'text-passwords',
    'the-codetree-backup',
    'toolspack',
    'ToolsPack',
    'tweet-blender',
    'versionpress',
    'w3-total-cache',
    'wordpress-database-abstraction',
    'wordpress-gzip-compression',
    'wp-cache',
    'wp-database-optimizer',
    'wp-db-backup',
    'wp-dbmanager',
    'wp-engine-snapshot',
    'wp-file-cache',
    'wp-phpmyadmin',
    'wp-postviews',
    'wp-slimstat',
    'wp-super-cache',
    'wp-symposium-alerts',
    'wpengine-migrate',
    'wpengine-snapshot',
    'wponlinebackup',
    'wpsmilepack',
];

// Deactivate disallowed plugins
add_action(
    'plugin_loaded',
    static function ($plugin_path) {
        $plugin = plugin_basename($plugin_path);
        $plugin_slug = explode('/', $plugin)[0];
        if (in_array($plugin_slug, HOSTING_DISALLOWED_PLUGINS, true)) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            deactivate_plugins([$plugin], true);
            add_action(
                'admin_notices',
                static function () {
                    $message = 'This plugin is disallowed in this hosting environment.';
                    printf('<div class="notice notice-error"><p>%s</p></div>', esc_html($message));
                },
                10,
                0
            );
        }
    },
    10,
    1
);

// Remove Activate plugin action
add_filter(
    'user_has_cap',
    static function ($allcaps, $caps, $args) {
        if (count($args) === 3 && $args[0] === 'activate_plugin') {
            $plugin_slug = explode('/', $args[2])[0];
            if (in_array($plugin_slug, HOSTING_DISALLOWED_PLUGINS, true)) {
                $allcaps['activate_plugins'] = false;
            }
        }
        return $allcaps;
    },
    PHP_INT_MAX,
    3
);
