<?php

/*
 * Plugin Name: Hide ACF admin pages
 *
 * Add-ons
 *
 * - acf-gravityforms-add-on
 *
 * @see https://awesomeacf.com/
 */

add_filter(
    'acf/settings/show_admin',
    '__return_false',
    10,
    0
);

// Export fields to .acf/acf-export.json and as code to inc/acf-fields.php
