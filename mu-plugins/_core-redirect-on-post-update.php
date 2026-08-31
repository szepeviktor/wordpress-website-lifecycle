<?php

/*
 * Plugin Name: Redirect to Posts page on post update
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'redirect_post_location',
    static function ($location, $post_id) {
        return add_query_arg(
            ['post_type' => get_post_type($post_id)],
            admin_url('edit.php') // Posts page
        );
/*
        return add_query_arg(
            ['post_type' => get_post_type($post_id)],
            admin_url('post-new.php') // Add New Post page
        );
*/
/*
        return get_preview_post_link($post_id);
*/
    },
    10,
    2
);
