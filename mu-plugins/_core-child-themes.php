<?php

/*
 * Plugin Name: Prevent activation of themes having a child theme available
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_action(
    'after_switch_theme',
    static function ($old_theme_name, $old_theme) {
        // Child themes are OK.
        if (is_child_theme()) {
            return;
        }
        // Detect child theme.
        $current_theme = wp_get_theme();
        $themes = wp_get_themes();
        foreach ($themes as $theme) {
            // "Theme Name:" header.
            if ($current_theme->name !== $theme->parent_theme) {
                continue;
            }
            // Switch back to the previous theme as this one has a child.
            switch_theme($old_theme->stylesheet);
            error_log(sprintf('%s has a child theme, reverting to %s', $current_theme->name, $old_theme->name));
            add_action(
                'admin_notices',
                static function () {
                    $error_message = 'Reverted to previous theme as new one has a child theme';
                    printf('<div class="notice-error"><p>%s</p></div>', esc_html($error_message));
                },
                10,
                0
            );
            break;
        }
    },
    10,
    2
);
