<?php
/**
 * Email Template: Tracking Info (Plain Text)
 *
 * @package WooCommerceShipmentTracking
 *
 * @var \WC_Order $order
 * @var string    $provider_name
 * @var string    $tracking_provider
 * @var string    $tracking_number
 * @var string    $tracking_link
 * @var string    $date_shipped_formatted
 * @var array     $tracking_data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo "========================================\n\n";
echo esc_attr( strtoupper( __( 'Shipment Tracking Information', 'wc-shipment-tracking' ) ) ) . "\n\n";

if ( ! empty( $provider_name ) ) {
	echo esc_attr( __( 'Shipping Provider', 'wc-shipment-tracking' ) ) . ': ' . esc_attr( $provider_name ) . "\n";
}

if ( ! empty( $tracking_number ) ) {
	echo esc_attr( __( 'Tracking Number', 'wc-shipment-tracking' ) ) . ': ' . esc_attr( $tracking_number ) . "\n";
}

if ( ! empty( $date_shipped_formatted ) ) {
	echo esc_attr( __( 'Date Shipped', 'wc-shipment-tracking' ) ) . ': ' . esc_attr( $date_shipped_formatted ) . "\n";
}

if ( ! empty( $tracking_link ) ) {
	echo "\n" . esc_attr( __( 'Track your shipment at', 'wc-shipment-tracking' ) ) . ":\n" . esc_url( $tracking_link ) . "\n";
}

echo "\n========================================\n\n";