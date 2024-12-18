<?php

/*
 * Insert this snippet before the end of innermost "foreach" loop in WP_Hook::apply_filters()
 *
 * wp-includes/class-wp-hook.php:328
 */

// Filtered value has changed.
global $wp_filter;
if (
    // EDIT filter name here!
    ($wp_filter['template_redirect'] ?? null) === $this
    && $value !== $args[0]
) {
    printf('<div>Value changed from %s to %s by<br><pre>', var_export($args[0], true), var_export($value, true));
    var_dump($the_['function']);
    echo '</pre><hr></div>';
}
