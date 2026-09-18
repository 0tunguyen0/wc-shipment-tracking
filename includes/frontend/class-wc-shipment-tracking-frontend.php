<?php
/**
 * Frontend customer order display for WooCommerce Shipment Tracking.
 *
 * @package WooCommerceShipmentTracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_Shipment_Tracking_Frontend
 *
 * Displays shipment tracking info on customer account order details and order received pages.
 */
final class WC_Shipment_Tracking_Frontend {

	/**
	 * Singleton instance.
	 *
	 * @var WC_Shipment_Tracking_Frontend|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return WC_Shipment_Tracking_Frontend
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor.
	 */
	private function __construct() {
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'display_tracking_info' ), 20, 1 );
	}

	/**
	 * Output tracking details on order view pages.
	 *
	 * @param \WC_Order $order Order instance.
	 */
	public function display_tracking_info( $order ) {
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		if ( ! apply_filters( 'wc_shipment_tracking_show_in_order_details', true, $order ) ) {
			return;
		}

		$tracking_data = WC_Shipment_Tracking_Data::get_tracking_data( $order );

		if ( ! $tracking_data['has_tracking'] ) {
			return;
		}

		$template_args = array(
			'order'                  => $order,
			'provider_name'          => $tracking_data['provider_name'],
			'tracking_provider'      => $tracking_data['tracking_provider'],
			'tracking_number'        => $tracking_data['tracking_number'],
			'tracking_link'          => $tracking_data['tracking_link'],
			'date_shipped_formatted' => $tracking_data['date_shipped_formatted'],
			'tracking_data'          => $tracking_data,
			'is_frontend'            => true,
		);

		$template_file = 'html/tracking-info.php';
		$default_path  = WC_SHIPMENT_TRACKING_PATH . 'includes/views/';

		if ( function_exists( 'wc_get_template' ) ) {
			wc_get_template(
				$template_file,
				$template_args,
				'woocommerce/shipment-tracking/',
				$default_path
			);
		} else {
			$fallback_file = $default_path . $template_file;
			if ( file_exists( $fallback_file ) ) {
				extract( $template_args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
				include $fallback_file;
			}
		}
	}
}
