<?php

/*
 * Plugin Name: Disallow OSZKbot
 * Plugin URI: https://webarchivum.oszk.hu/tartalomgazdaknak/technikai-tudnivalok-az-archivalasrol/
 */

add_filter(
    'robots_txt',
    static function ($output, $is_public) {
        $lines = [
            'User-agent: OSZKbot',
            'Disallow: /',
        ];
        if (!$is_public) {
            return $output;
        }
        return implode("\n", $lines) . "\n\n" . $output;
    },
    -1,
    2
);
