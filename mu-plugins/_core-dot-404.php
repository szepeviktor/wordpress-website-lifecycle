<?php

/*
 * Plugin Name: Dot URL early 404
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'muplugins_loaded',
    static function () {
        // wp rewrite list --fields=match | grep -F '\.'
        $allow_patterns = [
            '~^sitemap_index\.xml$~',
            '~^([^/]+?)-sitemap([0-9]+)?\.xml$~',
            '~^([a-z]+)?-?sitemap\.xsl$~',
            '~^wp-sitemap\.xml$~',
            '~^wp-sitemap\.xsl$~',
            '~^wp-sitemap-index\.xsl$~',
            '~^wp-sitemap-([a-z]+?)-([a-z\d_-]+?)-(\d+?)\.xml$~',
            '~^wp-sitemap-([a-z]+?)-(\d+?)\.xml$~',
            '~^robots\.txt$~',
            '~^favicon\.ico$~',
            '~^sitemap\.xml$~',
            // old '~^.*wp-(atom|rdf|rss|rss2|feed|commentsrss2)\.php$~',
            // 403 '~^.*wp-app\.php(?:/.*)?$~',
        ];

        // Filter out WP entrypoints like /wp-admin/admin-ajax.php
        if (!isset($_SERVER['REDIRECT_URL'])) {
            return;
        }

        $url_path = ltrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');

        if (!str_contains($url_path, '.')) {
            return;
        }

        foreach ($allow_patterns as $pattern) {
            if (preg_match($pattern, $url_path) === 1) {
                return;
            }
        }

        error_log('Malicious traffic detected: w4wp_dot_404');
        status_header(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo "404 Not Found\n";
        echo "URLs containing a dot are denied because the requested file is missing.\n";
        exit;
    },
    0,
    0
);
