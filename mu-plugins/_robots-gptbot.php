<?php

/*
 * Plugin Name: Disallow GPTBot
 * Plugin URI: https://platform.openai.com/docs/gptbot
 */

add_filter(
    'robots_txt',
    static function ($output, $is_public) {
        $lines = [
            'User-agent: GPTBot',
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
