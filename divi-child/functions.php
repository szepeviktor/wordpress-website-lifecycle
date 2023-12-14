<?php
/**
 * Divi child theme.
 *
 * @see https://www.elegantthemes.com/blog/divi-resources/divi-child-theme
 */

// Set API key
// wp option update et_automatic_updates_options '{"username":"USERNAME","api_key":"API-KEY"}' --format=json

// Elegant Themes API call: core/components/Updates.php:573

/*
// The Blog Posts Index page / home.php / `page_for_posts` cannot be a Divi page
// https://help.elegantthemes.com/en/articles/2188833-blog-page-not-changing-when-built-with-divi-builder
add_filter(
    'pre_option_page_for_posts',
    '__return_zero',
    PHP_INT_MAX,
    0
);
*/

function prefix_theme_enqueue_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'prefix_theme_enqueue_styles');
