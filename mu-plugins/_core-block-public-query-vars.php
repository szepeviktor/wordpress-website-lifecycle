<?php

/*
 * Plugin Name: Block public query variables
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// permalink_structure must be enabled
add_action(
    'parse_request',
    static function ($wp) {
        $is_pretty_rest_request = isset($wp->query_vars['rest_route'])
            && !array_key_exists('rest_route', $_GET);
        if (is_admin() || $is_pretty_rest_request) {
            return;
        }
        // Allow Divi Visual Builder requests for logged-in editors.
        if (is_user_logged_in() && ($_GET['et_fb'] ?? '') === '1') {
            return;
        }
        $whitelist = [
            's', // Search
        ];
        $is_the_events_calendar_request = did_action('tec_events_fully_loaded') > 0
            && (
                ($wp->query_vars['post_type'] ?? null) === 'tribe_events'
                || array_key_exists('tribe_events_cat', $wp->query_vars)
            );
        if ($is_the_events_calendar_request) {
            $whitelist = array_merge(
                $whitelist,
                [
                    'eventDisplay',
                    'eventDate',
                    'event-date',
                    'eventSequence',
                    'event_date',
                    'featured',
                    'hide_subsequent_recurrences',
                    'ical',
                    'outlook-ical',
                    'page',
                    'paged',
                    'post_tag',
                    'post_type',
                    'posts_per_page',
                    'start_date',
                    'end_date',
                    'tag',
                    'tribe-bar-date',
                    'tribe_event_display',
                    'tribe_events_cat',
                    'tribe_events_views_kitchen_sink',
                    'tribe_paged',
                    'tribe_redirected',
                    'tribe_remove_date_filters',
                    'tec_render',
                ]
            );
        }
        if (array_key_exists('preview', $_GET) && is_user_logged_in()) {
            $whitelist[] = 'preview';
            $whitelist[] = 'p';
            $whitelist[] = 'page_id';
        }
        $requested_query_vars = array_intersect(
            array_keys($_GET),
            $wp->public_query_vars
        );
        $blocked_query_vars = array_diff(
            $requested_query_vars,
            $whitelist
        );
        if ($blocked_query_vars !== []) {
            $wp->query_vars = ['error' => '404'];
        }
    },
    0,
    1
);
