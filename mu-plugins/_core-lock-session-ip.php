<?php

/*
 * Plugin Name: Lock sessions to IP addresses
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'determine_current_user',
    static function ($userId) {
        if (!$userId) {
            return $userId;
        }

        $token = wp_get_session_token();
        if ($token === '') {
            // Ignore authentication methods that do not use WordPress sessions.
            return $userId;
        }

        $sessions = WP_Session_Tokens::get_instance($userId);
        $session = $sessions->get($token);
        $remoteAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

        if (
            is_array($session)
            && isset($session['ip'])
            && $remoteAddress !== ''
            && $session['ip'] === $remoteAddress
        ) {
            return $userId;
        }

        $sessions->destroy($token);
        wp_clear_auth_cookie();
        error_log('Destroying session for user #'.$userId.' after IP address validation failure.');

        return false;
    },
    30,
    1
);
