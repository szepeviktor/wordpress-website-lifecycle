<?php
/**
 * Divi child theme.
 *
 * @see https://www.elegantthemes.com/blog/divi-resources/divi-child-theme
 */

// Set API key
// wp option update et_automatic_updates_options '{"username":"USERNAME","api_key":"API-KEY"}' --format=json

// Elegant Themes API call: core/components/Updates.php:573

function prefix_theme_enqueue_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'prefix_theme_enqueue_styles');
