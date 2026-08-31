<?php

/*
 * Plugin Name: Validate WooCommerce customer email TLD
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

/*
wget -qO- "https://data.iana.org/TLD/tlds-alpha-by-domain.txt" | sed -e "1d;s#^\\(\\S\\+\\)\$#'\1',#" | paste -s -d ""
*/
const IANA_TLDS = [
'COM',
];

add_filter(
    'woocommerce_process_registration_errors',
    static function($validation_error, $username, $password, $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $validation_error->add('invalid_email', __('The provided email address is not valid', 'woocommerce'));
            return $validation_error;
        }

        $domain = explode('@', $email, 2);
        $parts = explode('.', idn_to_ascii($domain[1]));
        $tld = array_pop($parts);
        if (! in_array(strtoupper($tld), IANA_TLDS, true)) {
            $validation_error->add('invalid_email_domain', __('The provided email address is not valid', 'woocommerce'));
        }
        return $validation_error;
    },
    20,
    4
);

add_action(
    'woocommerce_after_checkout_validation',
    static function ($data, $errors) {
        if (filter_var($data['billing_email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors->add('invalid_email', __('The provided email address is not valid', 'woocommerce'));
            return;
        }

        $domain = explode('@', $data['billing_email'], 2);
        $parts = explode('.', idn_to_ascii($domain[1]));
        $tld = array_pop($parts);
        if (! in_array(strtoupper($tld), IANA_TLDS, true)) {
            $errors->add('invalid_email_domain', __('The provided email address is not valid', 'woocommerce'));
        }
    },
    20,
    2
);

/* Alternative to woocommerce_after_checkout_validation
add_action(
    'woocommerce_checkout_process',
    static function () {
        $email = filter_input(INPUT_POST, 'billing_email', FILTER_VALIDATE_EMAIL);

        wc_add_notice(__('The provided email address is not valid', 'woocommerce'), 'error');
    },
    20,
    0
);
*/
