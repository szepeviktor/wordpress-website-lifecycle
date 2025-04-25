<?php

/*
 * Insert this snippet in WP_Hook::apply_filters()
 *
 * wp-includes/class-wp-hook.php:328
 */

global $wp_filter;
$_do_debug_filter = ($wp_filter['wp_head'] ?? null) === $this;
if ($_do_debug_filter) do_action('qm/start', 'filter');

    // foreach ( $this->callbacks[ $priority ] as $the_ ) {
    // call_user_func calls ...

if ($_do_debug_filter)
    do_action('qm/lap', 'filter', QM_Util::populate_callback(['function' => $the_['function']])['name'] ?? 'N/A');

    // }

if ($_do_debug_filter) do_action('qm/stop', 'filter');
