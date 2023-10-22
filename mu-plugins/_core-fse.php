<?php

/*
 * Plugin Name: Remove global styles
 * Plugin URI: https://github.com/WordPress/gutenberg/blob/trunk/docs/explanations/architecture/styles.md#global-styles
 */

add_action(
    'wp',
    static function () {
        remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
        remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
        remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
    },
    100,
    0
);
