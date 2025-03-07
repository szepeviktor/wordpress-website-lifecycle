<?php

/*
 * Plugin Name: Hide ACF admin pages
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
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

add_action(
    'admin_footer',
    static function () {
        echo '<style>#tmpl-acf-field-group-pro-features { display: none !important; }</style>';
    },
    PHP_INT_MAX,
    0
);
