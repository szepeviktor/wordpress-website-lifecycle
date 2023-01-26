<?php

/*
 * Plugin Name: Disable new user registration email to admin
 */

remove_action('register_new_user', 'wp_send_new_user_notifications');
add_action(
    'register_new_user',
    static function ($user_id) {
        wp_new_user_notification($user_id, null, 'user');
    },
    10,
    1
);
