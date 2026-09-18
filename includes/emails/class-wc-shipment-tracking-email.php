<?php
/**
 * Email template integration for WooCommerce Shipment Tracking.
 *
 * @package WooCommerceShipmentTracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_Shipment_Tracking_Email
 *
 * Appends shipment tracking details to customer completed order notification emails.
 */
final class WC_Shipment_Tracking_Email {

	/**
	 * Singleton instance.
	 *
	 * @var WC_Shipment_Tracking_Email|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return WC_Shipment_Tracking_Email
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
		add_action( 'woocommerce_email_after_order_table', array( $this, 'attach_tracking_info' ), 10, 4 );
	}

	/**
	 * Check if email ID should include tracking info.
	 *
	 * Defaults to 'customer_completed_order'.
	 *
	 * @param string $email_id WooCommerce email identifier.
	 * @return bool
	 */
	public function is_eligible_email( $email_id ) {
		$eligible_emails = apply_filters(
			'wc_shipment_tracking_eligible_emails',
			array( 'customer_completed_order' )
		);

		return in_array( $email_id, $eligible_emails, true );
	}

	/**
	 * Attach tracking information to order emails.
	 *
	 * @param \WC_Order   $order Order instance.
	 * @param bool        $sent_to_admin Whether email is sent to admin.
	 * @param bool        $plain_text Whether email is plain text.
	 * @param \WC_Email   $email Email object.
	 */
	public function attach_tracking_info( $order, $sent_to_admin, $plain_text, $email ) {
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		// Don't send tracking to admin if configured only for customers.
		if ( $sent_to_admin && ! apply_filters( 'wc_shipment_tracking_send_to_admin_email', false ) ) {
			return;
		}

		// Validate email ID.
		if ( ! is_object( $email ) || ! isset( $email->id ) || ! $this->is_eligible_email( $email->id ) ) {
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
		);

		$template_file = $plain_text ? 'plain/tracking-info.php' : 'html/tracking-info.php';
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
