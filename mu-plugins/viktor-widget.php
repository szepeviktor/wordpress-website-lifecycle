<?php

/*
 * Plugin Name: Viktor widget
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action('wp_dashboard_setup', 'szv_add_dashboard_widget', 10, 0);

function szv_add_dashboard_widget()
{
    wp_add_dashboard_widget(
        'szv_widget',
        'Welcome developer!', // widget_name
        'szv_render_dashboard_widget',
        null,
        null,
        'normal', // context
        'high' // priority
    );
}

function szv_render_dashboard_widget($post, $callback_args)
{
    echo '<div class="main"><ul>';
    echo '<li class="onboarding-server"><a href="https://github.com/szepeviktor/debian-server-tools/blob/master/Onboarding.md#onboarding-for-developers" target="_blank">Server info for developers</a></li>';
    echo '<li class="onboarding-wordpress"><a href="https://github.com/szepeviktor/wordpress-website-lifecycle/blob/master/README.md#onboarding-for-developers" target="_blank">WordPress info for developers</a></li>';
    echo '<li class="questions"><a href="mailto:viktor@szepe.net">Questions?</a></li>';
    echo '</ul></div>';
}
