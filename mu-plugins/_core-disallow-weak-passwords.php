<?php

/*
 * Plugin Name: Disallow weak passwords
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'admin_enqueue_scripts',
    static function ($hook) {
        if ($hook !== 'profile.php') {
            return;
        }

        wp_add_inline_style(
            'wp-admin',
            '.pw-weak { display: none !important; }'
        );
    },
    20,
    1
);
