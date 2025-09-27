<?php

/*
 * Plugin Name: ASCII media file names only
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'wp_handle_upload_prefilter',
    static function ($file) {
        $transliterator = Transliterator::create('Any-Latin; Latin-ASCII; [:^ASCII:] remove');
        $file['name'] = preg_replace('/[^0-9A-Za-z._-]/', ' ', $transliterator->transliterate($file['name']));
        return $file;
    },
    0,
    1
);
add_filter(
    'wp_handle_sideload_prefilter',
    static function ($file) {
        $transliterator = Transliterator::create('Any-Latin; Latin-ASCII; [:^ASCII:] remove');
        $file['name'] = preg_replace('/[^0-9A-Za-z._-]/', ' ', $transliterator->transliterate($file['name']));
        return $file;
    },
    0,
    1
);
