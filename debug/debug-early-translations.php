<?php

/*
 * Plugin Name: Log early translations (DBG)
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'gettext',
    static function ($translated, $text, $domain) {
        if (did_action('plugins_loaded')) {
            return $translated;
        }
        error_log('[DBG] Early translation: ('.$domain.') '.$text);
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $trace) {
            if (isset($trace['file'], $trace['line'])) {
                error_log('[DBG] Stack trace: '.$trace['file'].':'.$trace['line']);
            }
        }
        return $translated;
    },
    0,
    3
);
