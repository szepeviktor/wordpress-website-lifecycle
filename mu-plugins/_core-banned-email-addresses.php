<?php

/*
 * Plugin Name: Require individual registration email addresses
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

function _core_banned_email_addresses_helper($errors, $username, $email)
{
    $localPart = strstr(strtolower($email), '@', true);
    if ($localPart === false) {
        return $errors;
    }

    // Based on Mailchimp's role-based address list:
    // https://mailchimp.com/help/limits-on-role-based-addresses/
    // "marketing" and "test" are local additions.
    $blockedLocalParts = [
        'abuse',
        'admin',
        'billing',
        'compliance',
        'devnull',
        'dns',
        'ftp',
        'hostmaster',
        'inoc',
        'ispfeedback',
        'ispsupport',
        'list-request',
        'list',
        'marketing',
        'maildaemon',
        'noc',
        'no-reply',
        'noreply',
        'null',
        'phish',
        'phishing',
        'postmaster',
        'privacy',
        'registrar',
        'root',
        'security',
        'spam',
        'support',
        'sysadmin',
        'tech',
        'test',
        'undisclosed-recipients',
        'unsubscribe',
        'usenet',
        'uucp',
        'webmaster',
        'www',
    ];

    /**
     * Filters whether an email address is unsuitable for an individual user account.
     *
     * Return false to allow a site-specific exception.
     */
    $isBlocked = apply_filters(
        'hosting_is_blocked_registration_email',
        in_array($localPart, $blockedLocalParts, true),
        $email,
        $localPart
    );

    if ($isBlocked) {
        $errors->add(
            'shared_email_address',
            'Please register with your individual email address. Shared or role-based addresses are not accepted.'
        );
    }

    return $errors;
}

add_filter(
    'registration_errors',
    '_core_banned_email_addresses_helper',
    10,
    3
);
add_filter(
    'woocommerce_registration_errors',
    '_core_banned_email_addresses_helper',
    10,
    3
);
add_filter(
    'woocommerce_process_registration_errors',
    static function ($errors, $username, $password, $email) {
        return _core_banned_email_addresses_helper($errors, $username, $email);
    },
    10,
    4
);
