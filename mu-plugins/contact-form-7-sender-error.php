<?php

/*
 * Plugin Name: Remove sender domain error in Contact Form 7 plugin
 */

add_action(
    'wpcf7_config_validator_validate',
    static function ($configValidator) {
        $configValidator->remove_error('mail.sender', WPCF7_ConfigValidator::error_email_not_in_site_domain);
        $configValidator->remove_error('mail_2.sender', WPCF7_ConfigValidator::error_email_not_in_site_domain);
    },
    10,
    1
);
