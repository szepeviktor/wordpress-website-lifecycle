<?php

/*
 * Insert this snippet at the top of wp-config.php
 */

// Write an Apache-like access.log
register_shutdown_function(function () {
    file_put_contents(dirname(__DIR__).'/access.log', sprintf(
        '%s - - [%s] "%s %s %s" %d %.2f "%s" "%s"' . PHP_EOL,
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        date('d/M/Y:H:i:s O'),
        $_SERVER['REQUEST_METHOD'] ?? 'GET',
        $_SERVER['REQUEST_URI'] ?? '/',
        $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1',
        http_response_code(),
        timer_float(),
        $_SERVER['HTTP_REFERER'] ?? '-',
        $_SERVER['HTTP_USER_AGENT'] ?? '-'
    ), FILE_APPEND | LOCK_EX);
});
