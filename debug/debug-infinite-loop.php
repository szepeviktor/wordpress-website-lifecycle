<?php

/*
 * Insert this snippet in wp-config
 * and watch error.log
 */

if (function_exists('pcntl_async_signals')) {
    pcntl_async_signals(true);
}
if (function_exists('pcntl_signal') && function_exists('pcntl_alarm')) {
    pcntl_signal(SIGALRM, function () {
        error_log("\n\n=== WATCHDOG TIMEOUT ===\n");
        error_log(print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 60), true));
        exit(124);
    });
    // Trigger after this many seconds
    pcntl_alarm(4);
} else {
    error_log("pcntl_* not available\n");
}
