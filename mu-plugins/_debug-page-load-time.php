<?php

/*
 * Plugin Name: Display page load time
 * Plugin URI: https://www.stevesouders.com/blog/2014/08/21/resource-timing-practical-tips/
 */

add_action(
    'wp_footer',
    static function () {

        ?>
        <script>
        window.onload = function () {
            "use strict";
            // EDIT here!
            var selector = ".welcome-msg";
            var timing = window.performance.timing;
            var ttfb = (timing.responseStart - timing.navigationStart) / 1000;
            var pageloadtime = (timing.loadEventStart - timing.navigationStart) / 1000;
            document.querySelector(selector).innerHTML =
                "TTFB = " + ttfb.toString() + " / Page load time = " + pageloadtime.toString();
        };
        </script>
        <?php

    },
    10,
    0
);
