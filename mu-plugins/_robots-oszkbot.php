<?php

// Disallow OSZKbot.
add_filter(
    'robots_txt',
    static function ($output, $public) {
        $lines = [
            'User-agent: OSZKbot',
            'Disallow: /',
        ];
        if ($public) {
            return implode("\n", $lines) . "\n\n" . $output;
        }
        return $output;
    },
    -1,
    2
);
