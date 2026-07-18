<?php

/*
 * Plugin Name: Elementor Cleanup
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 * Description: Disables Elementor onboarding, promotional notices, tracking prompts, and related admin noise.
 * Disabled Features: 38
 */

namespace {
    // Keep Elementor usage tracking disabled regardless of saved options.
    add_filter(
        'pre_option_elementor_allow_tracking',
        static function (): string {
            return 'no';
        }
    );

    // Mark the tracker notice as already handled.
    add_filter(
        'pre_option_elementor_tracker_notice',
        static function (): string {
            return '1';
        }
    );

    // Tell Elementor the old onboarding flow has already completed.
    add_filter(
        'pre_option_elementor_onboarded',
        static function (): string {
            return '2.0.0';
        }
    );

    // Force Elementor AI off for all users, regardless of saved profile preferences.
    add_filter(
        'get_user_option_elementor_enable_ai',
        static function (): string {
            return '0';
        }
    );

    // Mark Elementor's recurring editor upgrade notice as recently dismissed.
    add_filter(
        'pre_option__elementor_editor_upgrade_notice_dismissed',
        static function (): int {
            return time();
        }
    );

    // Prevent the Elementor 4 Atomic editor welcome popover for every user.
    add_filter(
        'get_user_metadata',
        static function ($value, $user_id, $meta_key) {
            if ('_e_welcome_popover_displayed' === $meta_key) {
                return true;
            }

            return $value;
        },
        100,
        3
    );

    // Keep the Angie promotion from auto-opening if Elementor loads its fallback code.
    add_filter(
        'pre_option_elementor_angie_guide_auto_shown',
        static function (): string {
            return 'yes';
        }
    );

    // Disable Elementor's Angie/MCP admin integration.
    add_action(
        'elementor/init',
        static function (): void {
            if (! class_exists(\Elementor\Plugin::class)) {
                return;
            }

            $module = \Elementor\Plugin::instance()->modules_manager->get_modules(
                'elementor-capabilities-mcp'
            );

            if (! $module) {
                return;
            }

            remove_action('admin_enqueue_scripts', [$module, 'register_packages']);
            remove_action('admin_enqueue_scripts', [$module, 'enqueue_scripts'], 20);
            remove_filter(
                'elementor/editor/v2/packages',
                [$module, 'add_editor_packages']
            );
        },
        1
    );

    add_filter(
        'elementor/editor/v2/packages',
        static function (array $packages): array {
            $disabled_packages = [
                'elementor-capabilities-mcp',
                'elementor-v3-mcp',
                'elementor-kit-mcp',
            ];

            return array_values(array_diff($packages, $disabled_packages));
        },
        PHP_INT_MAX
    );

    // Disable Elementor One's upgrade entry at its source.
    add_filter('elementor_one/upgrade_available', '__return_false', 100);

    // Disable both generations of the remote "What's New" feed.
    add_filter(
        'elementor/core/admin/notifications',
        static function (): array {
            return [];
        },
        100
    );

    add_filter(
        'rest_endpoints',
        static function (array $endpoints): array {
            unset($endpoints['/elementor-one/v1/top-bar/feedback']);

            if (isset($endpoints['/elementor-one/v1/top-bar/notifications'])) {
                foreach ($endpoints['/elementor-one/v1/top-bar/notifications'] as &$handler) {
                    if (is_array($handler) && array_key_exists('callback', $handler)) {
                        $handler['callback'] = static function (): \WP_REST_Response {
                            return new \WP_REST_Response([], 200);
                        };
                    }
                }
                unset($handler);
            }

            return $endpoints;
        },
        100
    );

    // Keep the client-side request successful without allowing its remote feed lookup.
    add_filter(
        'rest_request_before_callbacks',
        static function ($response, $handler, \WP_REST_Request $request) {
            if ('/elementor-one/v1/top-bar/notifications' === $request->get_route()) {
                return new \WP_REST_Response([], 200);
            }

            return $response;
        },
        100,
        3
    );

    // Prevent activation from redirecting into Elementor setup screens.
    add_filter(
        'pre_transient_elementor_activation_redirect',
        '__return_false'
    );

    // Remove Elementor's remote Pro widget list before promotion code consumes it.
    add_action(
        'plugins_loaded',
        static function (): void {
            if (! defined('ELEMENTOR_VERSION')) {
                return;
            }

            // Return quiet remote-info data for the Elementor version-specific transient.
            add_filter(
                'pre_transient_elementor_remote_info_api_data_' . ELEMENTOR_VERSION,
                static function (): array {
                    return ['pro_widgets' => []];
                },
                100
            );
        },
        1
    );

    // Keep Elementor's functional React home screen but remove its remote promotions.
    add_filter(
        'elementor/core/admin/homescreen',
        static function (array $data): array {
            // React renders external links unconditionally and expects an array.
            $data['external_links'] = [];
            $data['add_ons'] = [];

            unset(
                $data['sidebar_promotion_variants'],
                $data['site_builder']
            );

            if (isset($data['top_with_licences']) && is_array($data['top_with_licences'])) {
                foreach ($data['top_with_licences'] as &$top_section) {
                    if (! is_array($top_section)) {
                        continue;
                    }

                    unset(
                        $top_section['button_watch_title'],
                        $top_section['button_watch_url'],
                        $top_section['youtube_embed_id']
                    );
                }
                unset($top_section);
            }

            if (isset($data['get_started']) && is_array($data['get_started'])) {
                foreach ($data['get_started'] as &$section) {
                    if (empty($section['repeater']) || ! is_array($section['repeater'])) {
                        continue;
                    }

                    $section['repeater'] = array_values(
                        array_filter(
                            $section['repeater'],
                            static function ($item): bool {
                                if (! is_array($item)) {
                                    return false;
                                }

                                $url = (string) ($item['url'] ?? '');

                                return ! preg_match(
                                    '/(?:elementor-app|popup_templates|elementor_custom_(?:icons|fonts))/',
                                    $url
                                );
                            }
                        )
                    );
                }
                unset($section);
            }

            return $data;
        },
        100
    );

    // Strip promotions injected directly into the editor configuration.
    add_filter(
        'elementor/editor/localize_settings',
        static function (array $settings): array {
            if (isset($settings['integrationWidgets']) && is_array($settings['integrationWidgets'])) {
                $settings['integrationWidgets'] = array_values(
                    array_filter(
                        $settings['integrationWidgets'],
                        static function ($widget): bool {
                            return ! is_array($widget)
                                || 'ally-accessibility' !== ($widget['name'] ?? '');
                        }
                    )
                );
            }

            $settings['promotionWidgets'] = [];
            $settings['promotion'] = [];
            $settings['promotions'] = [];

            return $settings;
        },
        100,
        1
    );

    // Block obsolete or promotional Elementor One screens before they redirect externally.
    add_action(
        'admin_init',
        static function (): void {
            $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

            if ('elementor-one-upgrade' === $page || 'elementor-home' === $page) {
                wp_safe_redirect(admin_url('admin.php?page=elementor'));
                exit;
            }
        },
        1
    );

    // Hide Elementor One's upgrade submenu after its late registration.
    add_action(
        'plugins_loaded',
        static function (): void {
            add_action(
                'admin_menu',
                static function (): void {
                    remove_submenu_page('elementor-home', 'elementor-one-upgrade');
                },
                PHP_INT_MAX
            );
        },
        1
    );

    // Replace Elementor's JavaScript-only Editor flyout with regular WP submenus.
    add_action(
        'elementor/init',
        static function (): void {
            if (! class_exists(\Elementor\Plugin::class)
                || ! class_exists(\Elementor\Modules\EditorOne\Classes\Menu_Data_Provider::class)
            ) {
                return;
            }

            $editor_one = \Elementor\Plugin::instance()->modules_manager->get_modules('editor-one');

            if (! $editor_one || ! method_exists($editor_one, 'get_component')) {
                return;
            }

            // Disable Elementor One's top bar at its source. Its bundled feedback
            // dialog queries ipapi.co for a country code before submitting feedback.
            $top_bar_handler = $editor_one->get_component('top-bar-handler');

            if ($top_bar_handler) {
                remove_action(
                    'admin_enqueue_scripts',
                    [$top_bar_handler, 'enqueue_assets']
                );
                remove_action(
                    'in_admin_header',
                    [$top_bar_handler, 'render_top_bar_container']
                );
            }

            $menu_manager = $editor_one->get_component('editor-one-menu-manager');

            if (! $menu_manager) {
                return;
            }

            // Elementor registers the real screens first, then removes them before
            // rendering its JavaScript flyout. Keep those screens registered.
            remove_action(
                'admin_head',
                [$menu_manager, 'hide_flyout_items_from_wp_menu']
            );

            add_action(
                'admin_enqueue_scripts',
                static function (): void {
                    wp_dequeue_script('editor-one-menu');
                    wp_deregister_script('editor-one-menu');
                    wp_dequeue_script('editor-one-top-bar');
                    wp_deregister_script('editor-one-top-bar');
                    wp_dequeue_style('elementor-one-top-bar');
                    wp_deregister_style('elementor-one-top-bar');
                },
                PHP_INT_MAX
            );

            add_action(
                'admin_menu',
                static function (): void {
                    global $pagenow, $submenu, $_wp_real_parent_file;

                    $parent_slug = 'elementor-home';

                    if (empty($submenu[$parent_slug])) {
                        return;
                    }

                    $provider = \Elementor\Modules\EditorOne\Classes\Menu_Data_Provider::instance();
                    $flyout = $provider->get_third_level_data(
                        \Elementor\Modules\EditorOne\Classes\Menu_Data_Provider::THIRD_LEVEL_FLYOUT_MENU
                    );

                    if (empty($flyout['items']) || ! is_array($flyout['items'])) {
                        return;
                    }

                    $regular_items = [];

                    // Preserve Elementor's native Home and Editor entries, including
                    // their capabilities and registered page callbacks.
                    foreach ($submenu[$parent_slug] as $submenu_item) {
                        $slug = $submenu_item[2] ?? '';

                        if ('elementor-home' === $slug
                            || false !== strpos($slug, 'page=elementor-home')
                            || 'elementor' === $slug
                        ) {
                            $regular_items[] = $submenu_item;
                        }
                    }

                    $capabilities = [];
                    $provider_item_levels = [
                        $provider->get_level3_items(),
                        $provider->get_level4_items(),
                    ];

                    foreach ($provider_item_levels as $provider_items) {
                        foreach ($provider_items as $group_items) {
                            foreach ($group_items as $slug => $item) {
                                if (is_object($item) && method_exists($item, 'get_capability')) {
                                    $capabilities[$slug] = $item->get_capability();
                                }
                            }
                        }
                    }

                    $current_submenu_file = null;

                    foreach ($flyout['items'] as $item) {
                        if (! is_array($item) || empty($item['slug']) || empty($item['url'])) {
                            continue;
                        }

                        $item_url = (string) $item['url'];
                        $item_path = basename((string) wp_parse_url($item_url, PHP_URL_PATH));
                        $item_query = [];
                        parse_str((string) wp_parse_url($item_url, PHP_URL_QUERY), $item_query);
                        unset($item_query['return_to'], $item_query['ver']);

                        $menu_slug = $item_url;

                        if ('admin.php' === $item_path
                            && ! empty($item_query['page'])
                            && 'elementor-theme-builder' !== $item['slug']
                        ) {
                            $menu_slug = (string) $item_query['page'];
                        } elseif ('edit.php' === $item_path && ! empty($item_query['post_type'])) {
                            $menu_slug = add_query_arg(
                                'post_type',
                                (string) $item_query['post_type'],
                                'edit.php'
                            );
                        }

                        $is_current_item = $pagenow === $item_path;

                        foreach ($item_query as $query_key => $query_value) {
                            if (! isset($_GET[$query_key])
                                || is_array($_GET[$query_key])
                                || (string) $query_value !== (string) wp_unslash($_GET[$query_key])
                            ) {
                                $is_current_item = false;
                                break;
                            }
                        }

                        if ($is_current_item) {
                            $current_submenu_file = 'elementor' === $item['slug']
                                ? 'elementor'
                                : $menu_slug;
                        }

                        // The existing Editor submenu already opens Quick Start.
                        if ('elementor' === $item['slug']) {
                            continue;
                        }

                        $label = isset($item['label']) ? (string) $item['label'] : '';

                        if ('' === $label) {
                            continue;
                        }

                        $regular_items[] = [
                            $label,
                            $capabilities[$item['slug']] ?? 'edit_posts',
                            $menu_slug,
                            $label,
                        ];
                    }

                    $submenu[$parent_slug] = $regular_items;

                    // Elementor keeps legacy top-level parents registered and hides
                    // them with CSS. Point those screens at the visible parent so
                    // WordPress can calculate its normal open/current menu classes.
                    $_wp_real_parent_file['elementor'] = $parent_slug;
                    $_wp_real_parent_file['edit.php?post_type=elementor_library'] = $parent_slug;

                    // The legacy Elementor parent is already hidden by Elementor CSS,
                    // but leaving it registered makes WordPress select it for Editor.
                    remove_menu_page('elementor');

                    if (null !== $current_submenu_file) {
                        add_filter(
                            'parent_file',
                            static function () use ($parent_slug): string {
                                return $parent_slug;
                            },
                            PHP_INT_MAX
                        );

                        add_filter(
                            'submenu_file',
                            static function () use ($current_submenu_file, $parent_slug): string {
                                global $parent_file;

                                // Some Elementor screens mutate the global parent during
                                // submenu resolution, after WordPress's parent_file filter.
                                $parent_file = $parent_slug;

                                return $current_submenu_file;
                            },
                            PHP_INT_MAX
                        );
                    }
                },
                PHP_INT_MAX
            );
        },
        PHP_INT_MAX
    );

    // Hide any remaining Elementor One or AI chrome if the modules are still partially present.
    add_action(
        'admin_head',
        static function (): void {
            echo '<style id="elementor-quiet-mode-ai">'
                . implode(
                    ',',
                    [
                        '#editor-one-top-bar',
                        '#editor-one-sidebar-navigation',
                        '#elementor-ai-admin',
                        '.e-ai-layout-button',
                        '#e-image-ai-media-library',
                        '#e-image-ai-attachment-details',
                        '#elementor-panel-get-pro-elements-sticky',
                        '#elementor-navigator__footer__promotion',
                        '#elementor-panel-category-pro-elements',
                        '#elementor-panel-category-helloplus',
                        '#elementor-panel-category-theme-elements',
                        '#elementor-panel-category-theme-elements-single',
                        '#elementor-panel-category-woocommerce-elements',
                        '#elementor-panel-category-atomic-form',
                        '#elementor-panel-category-custom-widgets',
                        '#tmpl-elementor-dynamic-tags-promo',
                        '#tmpl-elementor-template-library-upgrade-plan-button',
                        '#e-admin-top-bar-root',
                        '#footer-upgrade',
                        '#e-conversion-banner',
                        '#e-notice-bar',
                        '#elementor-manage-dashboard',
                        '[data-test="whats-new-button"]',
                        'button[aria-label="Angie"]',
                        'a[href*="page=elementor-one-upgrade"]',
                        '.elementor-plugins-gopro',
                        '.elementor-element--promotion',
                        '.elementor-element--integration',
                    ]
                )
                . '{display:none!important;}</style>';
        },
        PHP_INT_MAX
    );

    // Hide the Atomic Form upgrade badge inside the Elementor editor shell.
    add_action(
        'elementor/editor/after_enqueue_styles',
        static function (): void {
            echo '<style id="elementor-quiet-mode-elements">'
                . implode(
                    ',',
                    [
                        '#elementor-panel-categories .elementor-panel-heading-promotion',
                        '#elementor-panel-get-pro-elements-sticky',
                        '#elementor-navigator__footer__promotion',
                        '#elementor-panel-category-pro-elements',
                        '#elementor-panel-category-helloplus',
                        '#elementor-panel-category-theme-elements',
                        '#elementor-panel-category-theme-elements-single',
                        '#elementor-panel-category-woocommerce-elements',
                        '#elementor-panel-category-atomic-form',
                        '#elementor-panel-category-custom-widgets',
                        '#tmpl-elementor-dynamic-tags-promo',
                        '#tmpl-elementor-template-library-upgrade-plan-button',
                        '#e-notice-bar',
                        '[data-test="whats-new-button"]',
                        'button[aria-label="Angie"]',
                        '.elementor-element--promotion',
                        '.elementor-element--integration',
                    ]
                )
                . '{display:none!important;}</style>';
        },
        PHP_INT_MAX
    );

    // Remove Elementor promotional dashboard widgets.
    add_action(
        'wp_dashboard_setup',
        static function (): void {
            remove_meta_box('e-dashboard-overview', 'dashboard', 'normal');
            remove_meta_box('e-dashboard-overview', 'dashboard', 'side');
            remove_meta_box('e-dashboard-ally', 'dashboard', 'normal');
            remove_meta_box('e-dashboard-ally', 'dashboard', 'side');
            remove_meta_box('elementor-manage-dashboard', 'dashboard', 'normal');
            remove_meta_box('elementor-manage-dashboard', 'dashboard', 'side');
            remove_meta_box('elementor-manage-dashboard', 'dashboard', 'column3');
        },
        1000
    );

    // Suppress Elementor's injected admin footer rating prompt on Elementor screens.
    add_filter(
        'admin_footer_text',
        static function (string $footer_text): string {
            $screen = get_current_screen();

            if (! $screen instanceof \WP_Screen || false === strpos($screen->id, 'elementor')) {
                return $footer_text;
            }

            return '';
        },
        1000
    );

    // Hide the fixed Pro upsell banner in the editor elements panel.
    add_filter(
        'elementor/editor/panel/get_pro_details',
        static function (array $details): array {
            $details['show_banner'] = false;

            return $details;
        },
        100
    );

    // Remove the AI promo action from the Elementor dashboard overview widget.
    add_filter(
        'elementor/admin/dashboard_overview_widget/footer_actions',
        static function (array $actions): array {
            unset($actions['ai']);

            return $actions;
        },
        100
    );

    // Prevent promoted Pro placeholder widgets from being registered.
    add_filter(
        'elementor/widgets/is_widget_enabled',
        static function (bool $should_register, $widget): bool {
            if ($widget instanceof \Elementor\Modules\Promotions\Widgets\Pro_Widget_Promotion) {
                return false;
            }

            return $should_register;
        },
        100,
        2
    );

    // Unregister promoted placeholder widgets that Elementor added after boot.
    add_action(
        'elementor/widgets/register',
        static function (\Elementor\Widgets_Manager $widgets_manager): void {
            foreach ($widgets_manager->get_widget_types() as $widget_name => $widget) {
                if ($widget instanceof \Elementor\Modules\Promotions\Widgets\Pro_Widget_Promotion) {
                    $widgets_manager->unregister($widget_name);
                }
            }
        },
        PHP_INT_MAX
    );

    // Remove Elementor's plugin-list Go Pro action link.
    add_filter(
        'plugin_action_links_elementor/elementor.php',
        static function (array $links): array {
            foreach ($links as $key => $link) {
                if ('go_pro' === $key
                    || false !== stripos((string) $link, 'elementor-plugins-gopro')
                    || false !== stripos((string) $link, 'Get Elementor Pro')
                    || false !== stripos((string) $link, 'go-pro-wp-plugins')
                ) {
                    unset($links[$key]);
                }
            }

            return $links;
        },
        100
    );

    // Add a direct Elementor changelog link to the plugin row metadata.
    add_filter(
        'plugin_row_meta',
        static function (array $meta, string $file): array {
            if ('elementor/elementor.php' !== $file) {
                return $meta;
            }

            $meta['changelog'] = sprintf(
                '<a href="%s" target="_blank" rel="noreferrer">%s</a>',
                esc_url('https://go.elementor.com/full-changelog/'),
                esc_html__('Changelog', 'elementor')
            );

            return $meta;
        },
        100,
        2
    );
}

namespace Elementor\Core\Admin {
    class Feedback
    {
        public function get_settings(): array
        {
            return [];
        }
    }

    class Admin_Notices
    {
        public function get_settings(): array
        {
            return [];
        }

        public function print_admin_notice(
            array $options,
            $excludePages = []
        ): void {
        }

        public static function add_plg_campaign_data($url, $campaign_data)
        {
            return $url;
        }
    }
}

namespace Elementor\Modules\Ai {
    class Module
    {
        public static function get_experimental_data(): array
        {
            return [];
        }

        public static function is_active(): bool
        {
            return false;
        }
    }
}

namespace Elementor\Modules\AdminTopBar {
    class Module
    {
        public static function get_experimental_data(): array
        {
            return [];
        }

        public static function is_active(): bool
        {
            return false;
        }

        public function get_name(): string
        {
            return 'admin-top-bar';
        }
    }
}

namespace Elementor\Modules\Apps {
    class Module
    {
        public const PAGE_ID = 'elementor-apps';

        public static function get_experimental_data(): array
        {
            return [];
        }

        public static function is_active(): bool
        {
            return false;
        }

        public function get_name(): string
        {
            return 'apps';
        }
    }
}

namespace Elementor\Modules\Announcements {
    class Module
    {
        public static function get_experimental_data(): array
        {
            return [];
        }

        public static function is_active(): bool
        {
            return false;
        }
    }
}

namespace Elementor\Modules\Checklist {
    class Module
    {
        public const VISIBILITY_SWITCH_ID = 'show_launchpad_checklist';

        public static function get_experimental_data(): array
        {
            return [];
        }

        public static function is_active(): bool
        {
            return false;
        }

        public static function should_display_checklist_toggle_control(): bool
        {
            return false;
        }
    }
}

namespace Elementor\Modules\ProInstall {
    class Module
    {
        public static function get_experimental_data(): array
        {
            return [];
        }

        public static function is_active(): bool
        {
            return false;
        }

        public function get_name(): string
        {
            return 'pro-install';
        }
    }
}

namespace Elementor\Modules\CloudKitLibrary {
    class Module
    {
        public static function get_experimental_data(): array
        {
            return [];
        }

        public static function is_active(): bool
        {
            return false;
        }

        public static function get_app(): object
        {
            return new class {
                public function check_eligibility(): array
                {
                    return ['is_eligible' => false];
                }

                public function is_connected(): bool
                {
                    return false;
                }

                public function get_quota(): array
                {
                    return [];
                }
            };
        }
    }
}

namespace Elementor\Modules\Promotions {
    class Module
    {
        public static function get_experimental_data(): array
        {
            return [];
        }

        public static function is_active(): bool
        {
            return false;
        }

        public function get_name(): string
        {
            return 'promotions';
        }

        public static function get_ally_external_scanner_url(): string
        {
            return '';
        }
    }
}

namespace Elementor\Modules\CloudLibrary {
    class Module
    {
        public static function get_experimental_data(): array
        {
            return [];
        }

        public static function is_active(): bool
        {
            return false;
        }
    }
}

namespace Elementor\Modules\Feedback {
    class Module
    {
        public static function get_experimental_data(): array
        {
            return [];
        }

        public static function is_active(): bool
        {
            return false;
        }
    }
}

namespace Elementor\Modules\Notifications {
    class Module
    {
        public static function get_experimental_data(): array
        {
            return [];
        }

        public static function is_active(): bool
        {
            return false;
        }
    }
}

namespace Elementor\Modules\ProFreeTrialPopup {
    class Module
    {
        public static function get_experimental_data(): array
        {
            return [];
        }

        public static function is_active(): bool
        {
            return false;
        }
    }
}

namespace Elementor\Modules\SiteNavigation {
    class Module
    {
        public static function get_experimental_data(): array
        {
            return [];
        }

        public static function is_active(): bool
        {
            return false;
        }

        public function get_name(): string
        {
            return 'site-navigation';
        }
    }
}

namespace Elementor\Modules\WidgetCreation {
    class Module
    {
        public const MODULE_NAME = 'widget-creation';
        public const EXPERIMENT_NAME = 'e_widget_creation';

        public static function get_experimental_data(): array
        {
            return [];
        }

        public static function is_active(): bool
        {
            return false;
        }
    }
}
