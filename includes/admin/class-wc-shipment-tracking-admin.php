<?php
/**
 * Admin handler for WooCommerce Shipment Tracking.
 *
 * @package WooCommerceShipmentTracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_Shipment_Tracking_Admin
 *
 * Handles order edit screen meta boxes, admin assets, and order saving (POST & AJAX).
 */
final class WC_Shipment_Tracking_Admin {

	/**
	 * Nonce action string for security verification.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'wc_shipment_tracking_action';

	/**
	 * Nonce request field name.
	 *
	 * @var string
	 */
	const NONCE_NAME = 'wc_shipment_tracking_nonce';

	/**
	 * Singleton instance.
	 *
	 * @var WC_Shipment_Tracking_Admin|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return WC_Shipment_Tracking_Admin
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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_meta_box' ), 10, 2 );
		add_action( 'wp_ajax_wc_shipment_tracking_save', array( $this, 'ajax_save_tracking' ) );
	}

	/**
	 * Check whether the current admin request is for an order edit screen.
	 *
	 * Supports both classic Custom Post Type (shop_order) and HPOS order screens.
	 *
	 * @param string $hook Admin page hook suffix.
	 * @return bool
	 */
	public function is_order_edit_screen( $hook = '' ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) {
			return false;
		}

		// Classic CPT order screen.
		if ( in_array( $screen->id, array( 'shop_order', 'edit-shop_order' ), true ) ) {
			return true;
		}

		// HPOS order screen.
		if ( function_exists( 'wc_get_page_screen_id' ) ) {
			$hpos_screen_id = wc_get_page_screen_id( 'shop-order' );
			if ( $screen->id === $hpos_screen_id ) {
				return true;
			}
		}

		// Fallback check on post type and hook.
		if ( isset( $screen->post_type ) && 'shop_order' === $screen->post_type ) {
			return true;
		}

		return false;
	}

	/**
	 * Get current order ID on admin order screens.
	 *
	 * @return int
	 */
	private function get_current_order_id() {
		// HPOS order ID.
		if ( isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return absint( $_GET['id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// Classic CPT order ID.
		if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$post_id = get_the_ID();
		return $post_id ? absint( $post_id ) : 0;
	}

	/**
	 * Enqueue admin scripts and styles on order edit screens only.
	 *
	 * @param string $hook Page hook suffix.
	 */
	public function enqueue_scripts( $hook ) {
		if ( ! $this->is_order_edit_screen( $hook ) ) {
			return;
		}

		wp_enqueue_style(
			'wc-shipment-tracking-admin',
			WC_SHIPMENT_TRACKING_URL . 'assets/css/admin.css',
			array(),
			WC_SHIPMENT_TRACKING_VERSION
		);

		wp_enqueue_script(
			'wc-shipment-tracking-admin',
			WC_SHIPMENT_TRACKING_URL . 'assets/js/admin.js',
			array( 'jquery', 'jquery-ui-datepicker' ),
			WC_SHIPMENT_TRACKING_VERSION,
			true
		);

		$order_id = $this->get_current_order_id();

		wp_localize_script(
			'wc-shipment-tracking-admin',
			'wc_shipment_tracking_params',
			array(
				'ajax_url'          => admin_url( 'admin-ajax.php' ),
				'order_id'          => $order_id,
				'nonce'             => wp_create_nonce( self::NONCE_ACTION ),
				'carrier_templates' => WC_Shipment_Tracking_Data::get_carrier_url_templates(),
				'i18n'              => array(
					'save_tracking' => __( 'Save Tracking', 'wc-shipment-tracking' ),
					'saving'        => __( 'Saving...', 'wc-shipment-tracking' ),
					'saved'         => __( 'Tracking information saved', 'wc-shipment-tracking' ),
					'error'         => __( 'Error saving tracking information', 'wc-shipment-tracking' ),
				),
			)
		);
	}

	/**
	 * Add tracking meta box to order edit screens (HPOS and Classic CPT).
	 */
	public function add_meta_box() {
		$screen = 'shop_order';

		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) &&
			\Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$screen = function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'woocommerce_page_wc-orders';
		}

		add_meta_box(
			'wc-shipment-tracking',
			__( 'Shipment Tracking', 'wc-shipment-tracking' ),
			array( $this, 'render_meta_box' ),
			$screen,
			'side',
			'default'
		);
	}

	/**
	 * Render the tracking meta box content.
	 *
	 * @param \WP_Post|\WC_Order $post_or_order WP_Post or WC_Order object.
	 */
	public function render_meta_box( $post_or_order ) {
		$order = is_a( $post_or_order, 'WC_Order' ) ? $post_or_order : wc_get_order( $post_or_order );

		if ( ! $order ) {
			return;
		}

		$tracking_data = WC_Shipment_Tracking_Data::get_tracking_data( $order );
		$providers     = WC_Shipment_Tracking_Data::get_providers();

		// Variables extracted for the template view.
		$tracking_provider = $tracking_data['tracking_provider'];
		$tracking_number   = $tracking_data['tracking_number'];
		$tracking_link     = $tracking_data['custom_tracking_link'];
		$date_shipped      = $tracking_data['date_shipped'];
		$shipping_cost     = $tracking_data['shipping_cost'];
		$effective_link    = $tracking_data['tracking_link'];

		$view_path = WC_SHIPMENT_TRACKING_PATH . 'includes/views/html-order-tracking-meta-box.php';
		if ( file_exists( $view_path ) ) {
			include $view_path;
		}
	}

	/**
	 * Save tracking metadata when order is saved via traditional admin form submit.
	 *
	 * @param int      $post_id Order/Post ID.
	 * @param \WP_Post $post Post object.
	 */
	public function save_meta_box( $post_id, $post = null ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			return;
		}

		WC_Shipment_Tracking_Data::save_tracking_data( $post_id, $_POST );
	}

	/**
	 * AJAX endpoint to save tracking information without full order submit.
	 */
	public function ajax_save_tracking() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'wc-shipment-tracking' ) ), 403 );
		}

		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		if ( $order_id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid order ID.', 'wc-shipment-tracking' ) ), 400 );
		}

		$saved = WC_Shipment_Tracking_Data::save_tracking_data( $order_id, $_POST );

		if ( $saved ) {
			$tracking_data = WC_Shipment_Tracking_Data::get_tracking_data( $order_id );
			wp_send_json_success( array(
				'message'       => __( 'Tracking information saved', 'wc-shipment-tracking' ),
				'tracking_data' => $tracking_data,
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to save tracking information.', 'wc-shipment-tracking' ) ), 500 );
		}
	}
}
