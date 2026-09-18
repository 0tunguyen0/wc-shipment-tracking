<?php
/**
 * Shipment Tracking Data & Carrier Utility.
 *
 * @package WooCommerceShipmentTracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_Shipment_Tracking_Data
 *
 * Centralizes carrier definitions, URL formatting, and order metadata operations.
 */
class WC_Shipment_Tracking_Data {

	/**
	 * Default shipping providers supported out of the box.
	 *
	 * @return array
	 */
	public static function get_providers() {
		$providers = array(
			'ups'   => __( 'UPS', 'wc-shipment-tracking' ),
			'fedex' => __( 'FedEx', 'wc-shipment-tracking' ),
			'usps'  => __( 'USPS', 'wc-shipment-tracking' ),
			'dhl'   => __( 'DHL', 'wc-shipment-tracking' ),
			'other' => __( 'Other', 'wc-shipment-tracking' ),
		);

		return apply_filters( 'wc_shipment_tracking_providers', $providers );
	}

	/**
	 * Get human-readable provider name from provider code.
	 *
	 * @param string $provider_code Provider identifier.
	 * @return string
	 */
	public static function get_provider_name( $provider_code ) {
		$providers = self::get_providers();

		if ( isset( $providers[ $provider_code ] ) ) {
			return $providers[ $provider_code ];
		}

		return ! empty( $provider_code ) ? ucfirst( $provider_code ) : '';
	}

	/**
	 * Get carrier tracking URL templates.
	 *
	 * @return array
	 */
	public static function get_carrier_url_templates() {
		$templates = array(
			'ups'   => 'https://www.ups.com/track?tracknum=%s',
			'fedex' => 'https://www.fedex.com/fedextrack/?trknbr=%s',
			'usps'  => 'https://tools.usps.com/go/TrackConfirmAction?tLabels=%s',
			'dhl'   => 'https://www.dhl.com/en/express/tracking.html?AWB=%s',
		);

		return apply_filters( 'wc_shipment_tracking_carrier_url_templates', $templates );
	}

	/**
	 * Build tracking URL for carrier and tracking number.
	 * Falls back to auto-generated carrier URL if custom link is empty.
	 *
	 * @param string $provider Carrier code.
	 * @param string $tracking_number Tracking identifier.
	 * @param string $custom_link Optional custom URL provided by admin.
	 * @return string
	 */
	public static function build_tracking_url( $provider, $tracking_number, $custom_link = '' ) {
		if ( ! empty( $custom_link ) ) {
			return esc_url_raw( $custom_link );
		}

		if ( empty( $tracking_number ) ) {
			return '';
		}

		$templates = self::get_carrier_url_templates();

		if ( isset( $templates[ $provider ] ) ) {
			$url = sprintf( $templates[ $provider ], rawurlencode( trim( $tracking_number ) ) );
			return apply_filters( 'wc_shipment_tracking_tracking_url', $url, $provider, $tracking_number );
		}

		return '';
	}

	/**
	 * Retrieve tracking data array for a given order.
	 *
	 * @param WC_Order|int $order_or_id Order instance or order ID.
	 * @return array Tracking metadata.
	 */
	public static function get_tracking_data( $order_or_id ) {
		$order = is_a( $order_or_id, 'WC_Order' ) ? $order_or_id : wc_get_order( $order_or_id );

		$defaults = array(
			'tracking_provider'      => '',
			'provider_name'          => '',
			'tracking_number'        => '',
			'tracking_link'          => '',
			'custom_tracking_link'   => '',
			'date_shipped'           => '',
			'date_shipped_formatted' => '',
			'shipping_cost'          => '',
			'has_tracking'           => false,
		);

		if ( ! $order ) {
			return $defaults;
		}

		$to_string = static function( $value ) {
			if ( is_array( $value ) ) {
				$first = reset( $value );
				return is_scalar( $first ) ? trim( (string) $first ) : '';
			}
			return is_scalar( $value ) ? trim( (string) $value ) : '';
		};

		$provider             = $to_string( $order->get_meta( '_tracking_provider', true ) );
		$number               = $to_string( $order->get_meta( '_tracking_number', true ) );
		$custom_link          = $to_string( $order->get_meta( '_tracking_link', true ) );
		$date_shipped         = $to_string( $order->get_meta( '_date_shipped', true ) );
		$shipping_cost        = $to_string( $order->get_meta( '_shipping_cost', true ) );
		$effective_link       = self::build_tracking_url( $provider, $number, $custom_link );
		$date_formatted       = '';

		if ( ! empty( $date_shipped ) ) {
			$timestamp = strtotime( $date_shipped );
			if ( $timestamp ) {
				$date_formatted = function_exists( 'wp_date' )
					? wp_date( get_option( 'date_format' ), $timestamp )
					: date_i18n( get_option( 'date_format' ), $timestamp );
			}
		}

		return array(
			'tracking_provider'      => $provider,
			'provider_name'          => self::get_provider_name( $provider ),
			'tracking_number'        => $number,
			'tracking_link'          => $effective_link,
			'custom_tracking_link'   => $custom_link,
			'date_shipped'           => $date_shipped,
			'date_shipped_formatted' => $date_formatted,
			'shipping_cost'          => $shipping_cost,
			'has_tracking'           => ! empty( $number ),
		);
	}

	/**
	 * Save tracking metadata to an order.
	 *
	 * @param int   $order_id Order ID.
	 * @param array $data Form/AJAX data.
	 * @return bool True if saved successfully, false otherwise.
	 */
	public static function save_tracking_data( $order_id, array $data ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		$tracking_provider = isset( $data['tracking_provider'] ) && is_scalar( $data['tracking_provider'] ) ? wc_clean( wp_unslash( $data['tracking_provider'] ) ) : '';
		$tracking_number   = isset( $data['tracking_number'] ) && is_scalar( $data['tracking_number'] ) ? wc_clean( wp_unslash( $data['tracking_number'] ) ) : '';
		$tracking_link     = isset( $data['tracking_link'] ) && is_scalar( $data['tracking_link'] ) ? esc_url_raw( wp_unslash( $data['tracking_link'] ) ) : '';
		$date_shipped      = isset( $data['date_shipped'] ) && is_scalar( $data['date_shipped'] ) ? wc_clean( wp_unslash( $data['date_shipped'] ) ) : '';
		$shipping_cost     = isset( $data['shipping_cost'] ) && is_scalar( $data['shipping_cost'] ) ? wc_format_decimal( wp_unslash( $data['shipping_cost'] ) ) : '';

		// Validate provider against allowlist.
		$valid_providers = array_keys( self::get_providers() );
		if ( ! empty( $tracking_provider ) && ! in_array( $tracking_provider, $valid_providers, true ) ) {
			$tracking_provider = '';
		}

		// Validate date_shipped format if supplied (standard Y-m-d).
		if ( ! empty( $date_shipped ) ) {
			$parsed_time = strtotime( $date_shipped );
			if ( false !== $parsed_time ) {
				$date_shipped = date( 'Y-m-d', $parsed_time );
			} else {
				$date_shipped = '';
			}
		}

		$order->update_meta_data( '_tracking_provider', $tracking_provider );
		$order->update_meta_data( '_tracking_number', $tracking_number );
		$order->update_meta_data( '_tracking_link', $tracking_link );
		$order->update_meta_data( '_date_shipped', $date_shipped );
		$order->update_meta_data( '_shipping_cost', $shipping_cost );

		$order->save();

		do_action( 'wc_shipment_tracking_saved', $order->get_id(), array(
			'tracking_provider' => $tracking_provider,
			'tracking_number'   => $tracking_number,
			'tracking_link'     => $tracking_link,
			'date_shipped'      => $date_shipped,
			'shipping_cost'     => $shipping_cost,
		) );

		return true;
	}
}
