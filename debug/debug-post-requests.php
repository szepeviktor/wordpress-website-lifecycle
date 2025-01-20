<?php

/*
 * Insert this snippet at the top of wp-config.php
 */

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log(sprintf('POSTed:%s:%s', $_SERVER['REQUEST_URI'], json_encode($_POST)));
}
