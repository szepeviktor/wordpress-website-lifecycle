<?php

/*
 * Plugin Name: Elementor Cleanup
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 * Description: Disables Elementor onboarding, promotional notices, tracking prompts, and related admin noise.
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

    // Make Elementor One appear disconnected everywhere.
    add_filter(
        'pre_option_elementor_one_access_token',
        '__return_empty_string'
    );

    // Force Elementor AI off for all users, regardless of saved profile preferences.
    add_filter(
        'pre_user_option_elementor_enable_ai',
        static function (): string {
            return '0';
        }
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

    // Hide Elementor home and upgrade menu entries.
    add_action(
        'admin_menu',
        static function (): void {
            global $menu, $submenu;

            foreach ($menu as $index => $menu_item) {
                if (isset($menu_item[2]) && 'elementor-home' === $menu_item[2]) {
                    unset($menu[$index]);
                    break;
                }
            }

            remove_submenu_page('elementor-home', 'admin.php?page=elementor-home');
            if (isset($submenu['elementor-home'])) {
                unset($submenu['elementor-home']);
            }
        },
        PHP_INT_MAX
    );

    // Hide any remaining Elementor One or AI chrome if the modules are still partially present.
    add_action(
        'admin_head',
        static function (): void {
            echo '<style id="elementor-quiet-mode-ai">#editor-one-top-bar,#editor-one-sidebar-navigation,#elementor-ai-admin,.e-ai-layout-button,#e-image-ai-media-library,#e-image-ai-attachment-details,#elementor-panel-get-pro-elements-sticky,#elementor-navigator__footer__promotion,#elementor-panel-category-pro-elements,#elementor-panel-category-helloplus,#elementor-panel-category-theme-elements,#elementor-panel-category-theme-elements-single,#elementor-panel-category-woocommerce-elements,#tmpl-elementor-dynamic-tags-promo,#tmpl-elementor-template-library-upgrade-plan-button,#e-admin-top-bar-root,#footer-upgrade{display:none!important;}</style>';
        },
        PHP_INT_MAX
    );

    // Hide the Atomic Form upgrade badge inside the Elementor editor shell.
    add_action(
        'elementor/editor/after_enqueue_styles',
        static function (): void {
            echo '<style id="elementor-quiet-mode-elements">#elementor-panel-categories .elementor-panel-heading-promotion,#elementor-panel-get-pro-elements-sticky,#elementor-navigator__footer__promotion,#elementor-panel-category-pro-elements,#elementor-panel-category-helloplus,#elementor-panel-category-theme-elements,#elementor-panel-category-theme-elements-single,#elementor-panel-category-woocommerce-elements,#tmpl-elementor-dynamic-tags-promo,#tmpl-elementor-template-library-upgrade-plan-button{display:none!important;}</style>';
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
            unset($links['go_pro']);

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

namespace Elementor\Modules\EditorOne {
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
