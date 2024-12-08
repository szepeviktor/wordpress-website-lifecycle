<?php

/*
 * Plugin Name: WooCommerce resend order completed notification
 * Plugin URI: https://github.com/szepeviktor/wordpress-website-lifecycle
 */

add_filter(
    'woocommerce_order_actions',
    static function ($actions) {
        $actions['resend_order_completed'] = 'Resend order completed notification';
        return $actions;
    },
    11,
    1
);

add_action(
    'woocommerce_order_action_resend_order_completed',
    static function (\WC_Order $order) {
        WC()->payment_gateways();
        WC()->shipping();
        WC()->mailer()->emails['WC_Email_Customer_Completed_Order']->trigger($order->get_id(), $order);
        $order->add_order_note('Order completed notification resent to customer.', false, true);
    },
    10,
    1
);
