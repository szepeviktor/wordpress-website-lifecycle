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
            'activity-panels',
            'analytics',
            'product-block-editor',
            'coupons',
            'core-profiler',
            'customize-store',
            'customer-effort-score-tracks',
            'import-products-task',
            'experimental-fashion-sample-products',
            'shipping-smart-defaults',
            'shipping-setting-tour',
            'homescreen',
            'marketing',
            'mobile-app-banner',
            'navigation',
            'onboarding',
            'onboarding-tasks',
            'product-custom-fields',
            'remote-inbox-notifications',
            'remote-free-extensions',
            'payment-gateway-suggestions',
            'shipping-label-banner',
            'subscriptions',
            'store-alerts',
            'transient-notices',
            'woo-mobile-welcome',
            'wc-pay-promotion',
            'wc-pay-welcome-page',
            'launch-your-store',
        ];
        return array_diff($features, $disabled_features);
    },
    0,
    1
);

// Disable password change notification email
add_filter(
    'woocommerce_disable_password_change_notification',
    '__return_true',
    10,
    0
);
