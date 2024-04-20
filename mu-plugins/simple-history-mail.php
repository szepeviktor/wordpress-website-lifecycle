<?php

/*
 * Plugin Name: Simple History mail logger
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'wp_mail_succeeded',
    static function ($data) {
        if (!function_exists('SimpleLogger')) {
            error_log('Mail sent to ' . implode(',', $data['to']));
            return;
        }
        $context = [
            '_message_key' => 'wp_mail_succeeded',
            '_server_request_method' => $_SERVER['REQUEST_METHOD'],
        ];
        if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            $context['_server_http_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }
        if (!class_exists('SimpleLogger')) {
            \SimpleHistory::get_instance()->load_loggers();
        }
        \SimpleLogger()->log('info', 'Mail sent to ' . implode(',', $data['to']), $context);
    },
    10,
    1
);
