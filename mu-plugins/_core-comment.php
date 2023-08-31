<?php

/*
 * Plugin Name: Disable comment moderation notification to admin
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'comment_moderation_recipients',
    static function ($emails) {
        $admin_email = get_bloginfo('admin_email');
        return array_filter(
            $emails,
            static function ($address) use ($admin_email) {
                return $address !== $admin_email;
            }
        );
    },
    10,
    1
);
