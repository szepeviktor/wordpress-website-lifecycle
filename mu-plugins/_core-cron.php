<?php

/*
 * Plugin Name: Cron settings
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// Record the time of last WordPress Cron run.
add_filter(
    'pre_set_transient_doing_cron',
    static function ($value) {
        update_option('cron_last_run', $value, 'no');
        return $value;
    },
    0,
    1
);

// Check last run time in a shell script
/*
CRON_LAST_RUN="$(wp option get cron_last_run)"
test "${CRON_LAST_RUN%%.*}" -ge "$(date -d "1 hour ago" "+%s")"
*/
