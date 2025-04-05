<?php

/*
 * Plugin Name: Remove WooCommerce features
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'woocommerce_admin_features',
    static function ($features) {
        // From includes/react-admin/feature-config.php
        $disabled_features = [
            // 'marketplace', // Marketplace (extensions)
            'cost_of_goods_sold', // COGS
            'email_improvements', // New placeholders
            'order_attribution', // Data collection by sourcebuster.js
            'product_block_editor', // Gutenberg blocks
            'rate_limit_checkout',
            'remote_logging', // Remote logging
            'site_visibility_badge', // Launch Your Store
            'activity-panels',
            'analytics',
            'product-block-editor',
            'product-data-views',
            'experimental-blocks',
            'coming-soon-newsletter-template',
            'coupons',
            'core-profiler',
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
