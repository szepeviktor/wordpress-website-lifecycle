<?php

/*
 * Get Divi download URL by running this on a WordPress installation with active Divi theme
 *
 * wp eval-file divi-download.php
 */

$args = [
    'headers' => ['rate_limit' => 'false'],
    'body' => [
        'action' => 'check_theme_updates',
        'class_version' => '1.2',
        'installed_themes' => ['Divi' => '4.0.0'],
    ],
];
$response = wp_remote_post('https://www.elegantthemes.com/api/api.php', $args);
$update_data = maybe_unserialize(wp_remote_retrieve_body($response));

$opts = get_option('et_automatic_updates_options');
$et = new ET_Core_API_ElegantThemes($opts['username'], $opts['api_key']);
echo $et->get_download_url('Divi', $update_data['Divi']['new_version']);
