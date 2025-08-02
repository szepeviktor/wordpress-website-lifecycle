<?php

/**
 * WordPress configuration skeleton.
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 * @package WordPress
 */

/** Shared hosting shortcomings. */

// // User home directory: absolute path without trailing slash.
// define('_HOME_DIR', realpath(empty($_SERVER['HOME']) ? getenv('HOME') : $_SERVER['HOME']));
//
// // Upload-temp and session directory.
// ini_set('upload_tmp_dir', _HOME_DIR . '/tmp');
// ini_set('session.save_path', _HOME_DIR . '/session');
//
// // Different FTP/PHP UID.
// define('FS_CHMOD_DIR', (0775 & ~ umask()));
// define('FS_CHMOD_FILE', (0664 & ~ umask()));
//
// // Create dirs - Comment out after first use!
// mkdir(_HOME_DIR . '/tmp', 0700);
// mkdir(_HOME_DIR . '/session', 0700);
//
// // See shared-hosting-aid/enable-logging.php
// ini_set('error_log', _HOME_DIR . '/log/error.log');
// ini_set('log_errors', '1');
// ini_set('display_errors', '0');

/** Composer. */

require_once dirname(__DIR__) . '/vendor/autoload.php';

/** Security. */

/**
 * composer require szepeviktor/waf4wordpress
 * Copy mu-plugins/waf4wordpress.php
 *
 * @see https://github.com/szepeviktor/waf4wordpress/blob/master/README.md#composer-installation
 */

// WAF for WordPress.
define('W4WP_ALLOW_CONNECTION_EMPTY', true); // HTTP2.
define('W4WP_CDN_HEADERS', 'HTTP_X_AMZ_CF_ID:HTTP_VIA:HTTP_X_FORWARDED_FOR'); // CDN.
// define('W4WP_ALLOW_REDIRECT', true); // Polylang with separate domains.
new SzepeViktor\WordPress\Waf\HttpAnalyzer();

/** Core. */

// See wp-config-live-debugger/
define('WP_DEBUG', false);
// Don't allow any other write method.
define('FS_METHOD', 'direct');

// "wp-content" location.
// EDIT!
define('WP_CONTENT_DIR', __DIR__ . '/wp-content');
define('WP_CONTENT_URL', 'https://EXAMPLE.COM/wp-content');

define('WP_ALLOW_REPAIR', false);
define('WP_MEMORY_LIMIT', '40M');
define('WP_MAX_MEMORY_LIMIT', '256M');
define('DISALLOW_FILE_EDIT', true);
//define('DISALLOW_FILE_MODS', true);
define('WP_USE_EXT_MYSQL', false);
//define('WP_HTTP_BLOCK_EXTERNAL', true);
//define('WP_ACCESSIBLE_HOSTS', 'api.wordpress.org');
// +Yoast SEO define('WP_ACCESSIBLE_HOSTS', 'api.wordpress.org,www.google.com,www.bing.com');
define('WP_POST_REVISIONS', 20);
define('MEDIA_TRASH', true);

/**
 * Full page cache.
define('WP_CACHE', true);
 */

// CLI cron job: https://github.com/szepeviktor/debian-server-tools/blob/master/webserver/wp-install/wp-cron-cli.sh
// Simple CLI cron job: /usr/bin/php7.4 ABSPATH/wp-cron.php # stdout and stderr to cron email.
define('DISABLE_WP_CRON', true);
define('AUTOMATIC_UPDATER_DISABLED', true);

/**
 * Multisite.
 *
define('WP_ALLOW_MULTISITE', true);
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', true);
$base = '/';
define('DOMAIN_CURRENT_SITE', 'example.com');
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);
// define('WP_DEFAULT_THEME', 'theme-slug');
 */

/** Plugins. */

// typisttech/wp-password-argon-two
define('WP_PASSWORD_ARGON_TWO_PEPPER', getenv('WP_PASSWORD_ARGON_TWO_PEPPER'));
// Tiny CDN - No trailing slash!
define('TINY_CDN_INCLUDES_URL', 'https://d2aaaaaaaaaaae.cloudfront.net/project/wp-includes');
define('TINY_CDN_CONTENT_URL', 'https://d2aaaaaaaaaaae.cloudfront.net/wp-content');
// WP Redis
define('WP_CACHE_KEY_SALT', 'SITE-SHORT:');
// disable-updates.php
define('ENABLE_FORCE_CHECK_UPDATE', true);
// Performance Lab
define('PERFLAB_DISABLE_OBJECT_CACHE_DROPIN', true);
define('PERFLAB_DISABLE_SERVER_TIMING', true);
/**
 * https://polylang.wordpress.com/documentation/documentation-for-developers/list-of-options-which-can-be-set-in-wp-config-php/
define('PLL_WPML_COMPAT', false);
define('WP_APCU_KEY_SALT', 'SITE-SHORT_');
define('MEMCACHED_SERVERS', '127.0.0.1:11211:0');
define('PODS_LIGHT', true);
define('PODS_SESSION_AUTO_START', false);
define('WPCF7_LOAD_CSS', false);
define('WPCF7_LOAD_JS', false);
define('ACF_LITE', true); // Use 'acf/settings/show_admin' filter!
define('YIKES_MC_API_KEY', '00000000-us3');
 * Non-free plugins.
define('ACF_PRO_LICENSE', '00000000'); // ACF PRO "acf_pro_license"
define('GF_LICENSE_KEY', ''); // Gravity Forms "rg_gforms_key"
define('OTGS_DISABLE_AUTO_UPDATES', true); // WPML.
 */

/** Database. */

// Use /mysql/wp-createdb.sh
define('DB_NAME', 'database_name_here');
define('DB_USER', 'username_here');
define('DB_PASSWORD', 'password_here');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');
$table_prefix = 'wp_';

/** Salts. */

/**
 * Use WordPress.org API
wget -qO- https://api.wordpress.org/secret-key/1.1/salt/
 */

/** Absolute path to the WordPress directory. */
if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/project/');
    error_log('Please use wp-load.php to load WordPress.');
    exit;
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
