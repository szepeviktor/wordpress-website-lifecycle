<?php

/*
 * Plugin Name: Devberry
 * Description: Your development companion
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

// Discourage Search Engines.
add_filter(
    'pre_option_blog_public',
    '__return_zero',
    10,
    0
);

// Discourage Search Engines in HTTP headers.
add_action(
    'send_headers',
    static function () {
        header('X-Robots-Tag: noindex, nofollow', true);
    },
    PHP_INT_MAX,
    0
);

// Display Devberry.
add_action(
    'wp_footer',
    static function () {
        $title = sprintf('%s: v%s', wp_get_theme()->get('Name'), wp_get_theme()->get('Version'));
        // $title = sprintf('%s: v%s', wp_get_theme()->get('Name'), \Company\Project\Theme::VERSION);

        ?>
        <style>
        .devberry {
            position: fixed;
            width: 20px;
            height: 20px;
            margin: 7px;
            top: 0;
            left: 0;
            z-index: 999990;
            border-radius: 50%;
            /* margin: 10px; */
            background: rgba(154, 89, 181, 1);
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(154, 89, 181, 1);
            animation: devberry-pulse-purple 2s infinite;
        }
        .devberry-static {
            position: fixed;
            width: 0;
            height: 0;
            top: 0;
            right: 0;
            z-index: 999990;
            border-style: solid;
            border-width: 16px;
            border-top-color: rgba(154, 89, 181, 1);
            border-right-color: rgba(154, 89, 181, 1);
            border-bottom-color: transparent;
            border-left-color: transparent;
        }
        @keyframes devberry-pulse-purple {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(154, 89, 181, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(154, 89, 181, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(154, 89, 181, 0);
            }
        }
        </style>
        <div class="devberry" title="<?php echo esc_attr($title); ?>"></div>
        <!--
        <div class="devberry-static" title="<?php echo esc_attr($title); ?>"></div>
        -->
        <?php

    },
    10,
    0
);

// Force visitors to log in.
add_action(
    'wp',
    static function () {
        if (!is_user_logged_in() && !is_robots()) {
            wp_redirect(wp_login_url($_SERVER['REQUEST_URI']));
            exit;
        }
    },
    0,
    0
);
