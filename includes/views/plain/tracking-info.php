<?php
/**
 * Email Template: Tracking Info (Plain Text)
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

echo "==========\n\n";

echo esc_html__('SHIPMENT TRACKING INFORMATION', 'wc-shipment-tracking') . "\n\n";

echo esc_html__('Shipping Provider', 'wc-shipment-tracking') . ": " . esc_html($provider_name) . "\n";
echo esc_html__('Tracking Number', 'wc-shipment-tracking') . ": " . esc_html($tracking_number) . "\n";

if (!empty($date_shipped_formatted)) {
    echo esc_html__('Date Shipped', 'wc-shipment-tracking') . ": " . esc_html($date_shipped_formatted) . "\n";
}

if (!empty($tracking_link)) {
    echo "\n" . esc_html__('Track your shipment at', 'wc-shipment-tracking') . ": " . esc_url($tracking_link) . "\n";
}

echo "\n==========\n";