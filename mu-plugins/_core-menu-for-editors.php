<?php

/*
 * Plugin Name: Classic menu management for Editors
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'user_has_cap',
    static function ($allcaps, $caps, $args, $user) {
        if (
            wp_is_block_theme()
            || ($args[0] ?? '') !== 'edit_theme_options'
            || !in_array('editor', $user->roles, true)
        ) {
            return $allcaps;
        }

        $isMenuPage = ($GLOBALS['pagenow'] ?? '') === 'nav-menus.php';
        $ajaxAction = isset($_REQUEST['action']) && is_string($_REQUEST['action'])
            ? $_REQUEST['action']
            : '';
        $isMenuAjax = wp_doing_ajax() && in_array(
            $ajaxAction,
            [
                'add-menu-item',
                'menu-get-metabox',
                'menu-locations-save',
                'menu-quick-search',
            ],
            true
        );

        if ($isMenuPage || $isMenuAjax) {
            $allcaps['edit_theme_options'] = true;
        }

        return $allcaps;
    },
    10,
    4
);

add_action(
    'admin_menu',
    static function () {
        $user = wp_get_current_user();
        if (
            wp_is_block_theme()
            || !in_array('editor', $user->roles, true)
        ) {
            return;
        }

        remove_menu_page('themes.php');
        add_menu_page(
            __('Menus'),
            __('Menus'),
            'edit_pages',
            'nav-menus.php',
            '',
            'dashicons-admin-appearance',
            60
        );
    },
    999,
    0
);
