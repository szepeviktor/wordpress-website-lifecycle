<?php

/*
 * Plugin Name: WAF for WordPress (MU)
 * Plugin URI: https://github.com/szepeviktor/waf4wordpress
 */

new SzepeViktor\WordPress\Waf\CoreEvents();

// Display as a drop-in.
add_action(
    'after_plugin_row',
    static function ($plugin_file, $plugin_data, $status) {
        static $rowAdded = false;
        if ($status !== 'dropins' || $rowAdded) {
            return;
        }
        $rowAdded = true;
        ?>
        <tr class="active" data-slug="http-analyzer-php" data-plugin="HttpAnalyzer.php">
            <th scope="row" class="check-column"></th>
            <td class="plugin-title column-primary"><strong>HttpAnalyzer.php</strong></td>
            <td class="column-description desc">
                <div class="plugin-description"><p><strong>HTTP request analyzer part of WAF for WordPress.</strong></p></div>
                <div class="active second plugin-version-author-uri"><a
                    href="https://github.com/szepeviktor/waf4wordpress"
                    aria-label="Visit plugin site"
                ><?php _e('Visit plugin site'); ?></a></div>
            </td>
        </tr>
        <?php
    },
    10,
    3
);
