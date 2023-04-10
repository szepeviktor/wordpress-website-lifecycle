<?php

/*
 * Plugin Name: Add my signature to footer
 */

add_action(
    'wp_footer',
    static function () {
        echo "\n<!-- DevOps services and consulting: Viktor Szépe <viktor@szepe.net> -->\n";
    },
    PHP_INT_MAX,
    0
);
