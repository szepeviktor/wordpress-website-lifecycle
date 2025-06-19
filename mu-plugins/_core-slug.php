<?php

/*
 * Plugin Name: Alphanumeric slugs only
 * Plugin URI: https://www.unicode.org/versions/Unicode16.0.0/core-spec/chapter-4/#G134153
 */

add_filter(
    'sanitize_title',
    static function ($title) {
        // Let core handle printable ASCII characters
        return preg_replace('/[^\x20-\x7E\p{N}\p{L}]+/u', ' ', $title);
    },
    0,
    1
);
