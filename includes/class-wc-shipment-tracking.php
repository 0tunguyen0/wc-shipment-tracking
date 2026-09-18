<?php
/**
 * Main plugin orchestrator for WooCommerce Shipment Tracking.
 *
 * @package WooCommerceShipmentTracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_Shipment_Tracking
 *
 * Coordinates conditional loading of admin, frontend, and email components.
 */
final class WC_Shipment_Tracking {

	/**
	 * Single instance of the class.
	 *
	 * @var WC_Shipment_Tracking|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return WC_Shipment_Tracking
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
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required files and conditionally instantiate components.
	 */
	private function includes() {
		// Data helper always needed for data access and email rendering.
		require_once WC_SHIPMENT_TRACKING_PATH . 'includes/class-wc-shipment-tracking-data.php';

		// Emails can fire in admin, frontend, webhook, or background tasks.
		require_once WC_SHIPMENT_TRACKING_PATH . 'includes/emails/class-wc-shipment-tracking-email.php';
		WC_Shipment_Tracking_Email::instance();

		// Admin & AJAX context.
		if ( is_admin() ) {
			require_once WC_SHIPMENT_TRACKING_PATH . 'includes/admin/class-wc-shipment-tracking-admin.php';
			WC_Shipment_Tracking_Admin::instance();
		}

		// Frontend order details context.
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			require_once WC_SHIPMENT_TRACKING_PATH . 'includes/frontend/class-wc-shipment-tracking-frontend.php';
			WC_Shipment_Tracking_Frontend::instance();
		}
	}

	/**
	 * Register general WordPress hooks.
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Load plugin localization textdomain.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'wc-shipment-tracking',
			false,
			dirname( WC_SHIPMENT_TRACKING_BASENAME ) . '/languages'
		);
	}

	/**
	 * Backward compatibility helper for getting shipping providers.
	 *
	 * @return array
	 */
	public function get_shipping_providers() {
		return WC_Shipment_Tracking_Data::get_providers();
	}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization.
	 *
	 * @throws \Exception When attempting to unserialize singleton.
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton' );
	}
}
