<?php

/*
 * Emulate a web request
 *
 * php web-request.php
 */

$_SERVER = [
    // Copy this line to the top of your /index.php
    // echo '<pre>'; var_export($_SERVER); exit;
    // Copy its output here

    // Edit these as necessary
    'REQUEST_TIME_FLOAT' => microtime(true),
    'REQUEST_TIME' => (int) microtime(true),
    'HTTP_COOKIE' => 'name=value',
    'REQUEST_URI' => '/',
    'QUERY_STRING' => '',
];
$_GET = [];
parse_str($_SERVER['QUERY_STRING'], $_GET);
$_POST = [];
$_COOKIE = array_reduce(
    explode(';', $_SERVER['HTTP_COOKIE'] ?? ''),
    function ($c, $p) {
        [$k, $v] = array_pad(explode('=', trim($p), 2), 2, '');
        if ($k !== '') {
            $c[urldecode($k)] = urldecode($v);
        }
        return $c;
    },
    []
);
$_REQUEST = array_merge($_GET, $_POST, $_COOKIE);

// Disable page cache
define('DONOTCACHEPAGE', true);
// Disable firewall
define('WF_PHP_UNSUPPORTED', true);
define('NFW_WPWAF', 2);
define('MCDATAPATH', '.');

require_once 'index.php';
