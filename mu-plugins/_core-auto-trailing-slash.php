<?php

/*
 * Plugin Name: Trailing slash for no-dot paths
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'mod_rewrite_rules',
    static function (string $rules) {
        $insertion = <<<HTACCESS
# Add trailing slash if URL doesn't contain a dot and doesn't already end with /
RewriteCond %{REQUEST_URI} !(\.|/$)
RewriteRule ^(.+)$ /$1/ [R=301,L]

HTACCESS;
        $needle = "RewriteEngine On\n";
        if (strpos($rules, $needle) === false) {
            return $needle.$insertion.$rules;
        }
        return str_replace($needle, $needle.$insertion, $rules);
    },
    20,
    1
);
