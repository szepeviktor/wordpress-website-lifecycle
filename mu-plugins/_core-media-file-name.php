<?php

/*
 * Plugin Name: ASCII media file names only
 * Plugin URI: https://www.unicode.org/versions/Unicode16.0.0/core-spec/chapter-4/#G134153
 */

add_filter(
    'wp_handle_upload_prefilter',
    static function ($file) {
        $transliterator = Transliterator::create('Any-Latin; Latin-ASCII; [\u007f-\u7fff] remove');
        $file['name'] = preg_replace('/[^0-9A-Za-z._-]/', ' ', $transliterator->transliterate($file['name']));
        return $file;
    },
    0,
    1
);
add_filter(
    'wp_handle_sideload_prefilter',
    static function ($file) {
        $transliterator = Transliterator::create('Any-Latin; Latin-ASCII; [\u007f-\u7fff] remove');
        $file['name'] = preg_replace('/[^0-9A-Za-z._-]/', ' ', $transliterator->transliterate($file['name']));
        return $file;
    },
    0,
    1
);
