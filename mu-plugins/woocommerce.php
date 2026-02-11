<?php

/*
 * Plugin Name: Remove WooCommerce features
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'woocommerce_admin_features',
    static function ($features) {
        $disabled_features = [
            // Cost of Goods Sold
            // https://developer.woocommerce.com/2024/12/04/cogs-in-core/
            'cost_of_goods_sold',
            // New email template placeholders
            // https://developer.woocommerce.com/2025/01/20/woocommerce-9-6-fresh-new-tools-and-modernizing-classics/#h-email-improvements-beta
            'email_improvements',
            // Extensions, connect to your WooCommerce.com account
            // https://woocommerce.com/products/
            // 'marketplace',
            // Customer data collection by sourcebuster.js
            // https://woocommerce.com/document/order-attribution-tracking/
            'order_attribution',
            // Store API rate limiting
            // https://github.com/woocommerce/woocommerce/blob/trunk/plugins/woocommerce/src/StoreApi/docs/rate-limiting.md
            'rate_limit_checkout',
            // Remote logging to public-api.wordpress.com
            // https://developer.woocommerce.com/2024/09/23/recent-updates-to-error-handling-and-optional-remote-error-logging/
            'remote_logging',
            // Coming soon mode and admin bar badge
            // https://woocommerce.com/document/configuring-woocommerce-settings/coming-soon-mode/
            'site_visibility_badge',

            // From includes/react-admin/feature-config.php
            // A toolbar-like area
            // https://woocommerce.com/document/home-screen/#section-6
            'activity-panels',
            // Analytics and sales reports
            // https://woocommerce.com/document/woocommerce-analytics/
            'analytics',
            // Gutenberg blocks
            // https://woocommerce.com/document/woocommerce-store-editing/blocks/#woocommerce-blocks
            'product-block-editor',
            // Loads assets related to the product block editor
            'product-data-views',
            // New and in-development Gutenberg blocks
            'experimental-blocks',
            // Coming soon template with newsletter signup
            'coming-soon-newsletter-template',
            // Coupons
            // https://woocommerce.com/document/coupon-management/
            'coupons',
            // Onboarding setup
            'core-profiler',
            // Design the look and feel of your online store without code
            // https://woocommerce.com/document/woocommerce-customize-your-store/
            'customize-store',
            // Measure user satisfaction
            'customer-effort-score-tracks',
            // Background product import
            'import-products-task',
            // Import fashion products from experimental_fashion_sample_9_products.csv
            'experimental-fashion-sample-products',
            // Automated shipping settings
            'shipping-smart-defaults',
            // Interactive shipping settings
            'shipping-setting-tour',
            // Centralized dashboard - Marketplace depends on this
            'homescreen',
            // Marketing menu
            'marketing',
            // Core does not have .min.asset.php files
            'minified-js',
            // Promote mobile application
            'mobile-app-banner',
            // Deprecated, see core-profiler
            'onboarding',
            // Setup Checklist
            'onboarding-tasks',
            // Pattern Toolkit (PTK)
            // https://woocommerce.com/document/woocommerce-store-editing/patterns-template-parts/
            'pattern-toolkit-full-composability',
            // Ready to publish?
            'product-pre-publish-modal',
            // Custom Fields on Products
            // https://woocommerce.com/document/custom-product-fields/
            'product-custom-fields',
            // Notifications from WooCommerce.com
            'remote-inbox-notifications',
            // Promote free extensions
            'remote-free-extensions',
            // Promote payment gateways
            // https://github.com/woocommerce/woocommerce/blob/trunk/plugins/woocommerce/client/admin/docs/features/payment-gateway-suggestions.md
            'payment-gateway-suggestions',
            // Promote printful.com
            'printful',
            // React Settings page
            'settings',
            // Promote shipping labels
            'shipping-label-banner',
            // Subscriptions core support
            'subscriptions',
            // activity-panels? remote-inbox-notifications?
            'store-alerts',
            // Snackbar notices
            // https://github.com/woocommerce/woocommerce/blob/trunk/plugins/woocommerce/client/admin/docs/features/transient-notices.md
            'transient-notices',
            // Welcome experience on mobile
            'woo-mobile-welcome',
            // Promote WooCommerce Payments
            'wc-pay-promotion',
            // Introduction to WooCommerce Payments
            'wc-pay-welcome-page',
            // Async loading of category selection field
            'async-product-editor-category-field',
            // Launch Your Store task
            // https://developer.woocommerce.com/roadmap/launch-your-store-task/
            'launch-your-store',
            // Product form post type
            'product-editor-template-system',
            // WordPress Blueprint compatible JSON formats
            'blueprint',
            // Modernizing PHP-based payment gateway settings pages
            'reactify-classic-payments-settings',
            // Use horizon as calypso_env
            'use-wp-horizon',
            // Plus (+) and minus (-) buttons for the number of items
            'add-to-cart-with-options-stepper-layout',
            // Gutenberg Add to Cart block
            'blockified-add-to-cart',
        ];
        return array_diff($features, $disabled_features);
    },
    11,
    1
);

// Disable password change notification email
add_filter(
    'woocommerce_disable_password_change_notification',
    '__return_true',
    10,
    0
);

// Disable Marketplace promotions
add_filter(
    'woocommerce_marketplace_suppress_promotions',
    '__return_true',
    10,
    0
);

// Disable Marketplace suggestions
add_filter(
    'woocommerce_allow_marketplace_suggestions',
    '__return_false',
    PHP_INT_MAX,
    0
);
add_action(
    'init',
    static function () {
        remove_action(
            'woocommerce_update_marketplace_suggestions',
            [WC_Marketplace_Updater::class, 'update_marketplace_suggestions']
        );
    },
    20,
    0
);

// Disable usage tracking - https://woocommerce.com/usage-tracking/
add_filter(
    'pre_option_woocommerce_allow_tracking',
    static function () {
        return 'no';
    },
    PHP_INT_MAX,
    0
);

// Disable suggestions and incentives
add_action(
    'plugins_loaded',
    static function () {
        if (class_exists(WooCommerce::class, false)) {
            require __DIR__.'/woocommerce/AdminSuggestionsServiceProvider.php';
        }
    },
    0,
    0
);

// Mark onboarding as skipped
add_filter(
    'pre_option_woocommerce_onboarding_profile',
    static function () {
        return ['skipped' => true];
    },
    PHP_INT_MAX,
    0
);

// Hide footer text
add_action(
    'woocommerce_display_admin_footer_text',
    '__return_false',
    10,
    0
);

// Disable order step logging
add_filter(
    'woocommerce_logger_log_message',
    static function ($message, $level, $context, $handler) {
        if ($level === WC_Log_Levels::DEBUG) {
            $source = $context['source'] ?? '';
            if (is_string($source) && strpos($source, 'place-order') !== false) {
                return null;
            }
        }
        return $message;
    },
    10,
    4
);

// Hide Status page for users
add_action(
    'admin_init',
    static function () {
        if (get_current_user_id() === 1) {
            return;
        }
        remove_submenu_page('woocommerce', 'wc-status');
    },
    10,
    0
);

// Disable AS async runner
add_filter(
    'action_scheduler_allow_async_request_runner',
    '__return_false',
    10,
    0
);

// Log failed AS actions
add_action(
    'action_scheduler_failed_execution',
    static function ($action_id, $e, $context) {
        error_log(sprintf(
            '[AS FAILED] action_id=%d context=%s error=%s',
            $action_id,
            (string) $context,
            $e instanceof \Exception ? $e->getMessage() : 'unknown'
        ));
    },
    10,
    3
);
