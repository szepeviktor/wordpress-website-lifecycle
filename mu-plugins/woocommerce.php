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
            // Remote logging at public-api.wordpress.com
            // https://developer.woocommerce.com/2024/09/23/recent-updates-to-error-handling-and-optional-remote-error-logging/
            'remote_logging',
            // Coming soon mode and admin bar badge
            // https://woocommerce.com/document/configuring-woocommerce-settings/coming-soon-mode/
            'site_visibility_badge',
            // From includes/react-admin/feature-config.php
            // A toolbar-like area
            // https://woocommerce.com/document/home-screen/#section-6
            'activity-panels',
            // Analytics and Sales Reports
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
            // Onboarding setup for merchants
            'core-profiler',
            // Design the look and feel of your online store without code
            // https://woocommerce.com/document/woocommerce-customize-your-store/
            'customize-store',
            'customer-effort-score-tracks',
            'import-products-task',
            'experimental-fashion-sample-products',
            'shipping-smart-defaults',
            'shipping-setting-tour',
            'homescreen', // Marketplace depends on this
            'marketing',
            'minified-js',
            'mobile-app-banner',
            'onboarding',
            'onboarding-tasks',
            'pattern-toolkit-full-composability',
            'product-pre-publish-modal',
            'product-custom-fields',
            'remote-inbox-notifications',
            'remote-free-extensions',
            'payment-gateway-suggestions',
            'printful', // Show printful.com banner
            'settings', // React Settings page
            'shipping-label-banner',
            'subscriptions',
            'store-alerts',
            'transient-notices',
            'woo-mobile-welcome',
            'wc-pay-promotion',
            'wc-pay-welcome-page',
            'async-product-editor-category-field',
            'launch-your-store',
            'product-editor-template-system',
            'blueprint',
            'reactify-classic-payments-settings',
            'use-wp-horizon',
            'add-to-cart-with-options-stepper-layout',
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
