<?php
/**
 * WooCommerce Shipment Tracking Uninstall
 *
 * Triggered when the plugin is deleted via the WordPress admin.
 *
 * @package WooCommerceShipmentTracking
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Tracking metadata is preserved on orders by default to prevent accidental data loss.
// If a store owner explicitly wishes to purge metadata, define 'WC_SHIPMENT_TRACKING_PURGE_ON_UNINSTALL' as true.
if ( defined( 'WC_SHIPMENT_TRACKING_PURGE_ON_UNINSTALL' ) && WC_SHIPMENT_TRACKING_PURGE_ON_UNINSTALL ) {
	global $wpdb;

	$meta_keys = array(
		'_tracking_provider',
		'_tracking_number',
		'_tracking_link',
		'_date_shipped',
		'_shipping_cost',
	);

	$placeholders = implode( ', ', array_fill( 0, count( $meta_keys ), '%s' ) );

	// Clean post meta (classic CPT).
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ($placeholders)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$meta_keys
		)
	);

	// Clean HPOS order meta if table exists.
	$orders_meta_table = $wpdb->prefix . 'wc_orders_meta';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $orders_meta_table ) ) === $orders_meta_table ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$orders_meta_table} WHERE meta_key IN ($placeholders)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$meta_keys
			)
		);
	}
}
