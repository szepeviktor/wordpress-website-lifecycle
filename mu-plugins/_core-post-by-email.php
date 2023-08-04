<?php

/*
 * Plugin Name: Disable post-by-email
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'enable_post_by_email_configuration',
    '__return_false',
    PHP_INT_MAX,
    0
);
