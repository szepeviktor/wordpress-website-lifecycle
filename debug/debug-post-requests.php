<?php

/*
 * Insert this snippet at the top of wp-config.php
 */

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log(sprintf('[HTTP POST] %s:%s', $_SERVER['REQUEST_URI'], json_encode($_POST)));
    /*
    file_put_contents(
        __DIR__.'/wp-content/debug-post-request.log',
        sprintf(
            '%.2f %s %s %s%c',
            microtime(true),
            php_sapi_name() === 'cli' ? 'CLI' : $_SERVER['REMOTE_ADDR'],
            $_SERVER['REQUEST_URI'],
            json_encode($_POST),
            10
        ),
        FILE_APPEND | LOCK_EX
    );
    */
}
