<?php
/**
 * Plugin Name:       WooCommerce Shipment Tracking
 * Plugin URI:        https://github.com/0tunguyen0/wc-shipment-tracking
 * Description:       Add shipment tracking information to your WooCommerce orders, displayed in customer completion emails and account order details.
 * Version:           1.1.0
 * Author:            Tu Nguyen
 * Author URI:        https://github.com/0tunguyen0
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wc-shipment-tracking
 * Domain Path:       /languages
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * WC requires at least: 4.0.0
 * WC tested up to:   9.5.0
 *
 * @package WooCommerceShipmentTracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'WC_SHIPMENT_TRACKING_VERSION', '1.1.0' );
define( 'WC_SHIPMENT_TRACKING_FILE', __FILE__ );
define( 'WC_SHIPMENT_TRACKING_BASENAME', plugin_basename( __FILE__ ) );
define( 'WC_SHIPMENT_TRACKING_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_SHIPMENT_TRACKING_URL', plugin_dir_url( __FILE__ ) );

/**
 * Declare HPOS (High-Performance Order Storage / Custom Order Tables) compatibility.
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			WC_SHIPMENT_TRACKING_FILE,
			true
		);
	}
} );

/**
 * Bootstrap the plugin once all plugins are loaded.
 */
function wc_shipment_tracking_init() {
	// Guard: Ensure WooCommerce is active.
	if ( ! class_exists( 'WooCommerce' ) ) {
		if ( is_admin() ) {
			add_action( 'admin_notices', 'wc_shipment_tracking_missing_wc_notice' );
		}
		return;
	}

	// Load core orchestrator.
	require_once WC_SHIPMENT_TRACKING_PATH . 'includes/class-wc-shipment-tracking.php';
	WC_Shipment_Tracking::instance();
}
add_action( 'plugins_loaded', 'wc_shipment_tracking_init', 20 );

/**
 * Display admin notice when WooCommerce is missing or inactive.
 */
function wc_shipment_tracking_missing_wc_notice() {
	$message = sprintf(
		/* translators: %s: WooCommerce official link */
		esc_html__( 'WooCommerce Shipment Tracking requires %s to be installed and active.', 'wc-shipment-tracking' ),
		'<a href="https://woocommerce.com/" target="_blank" rel="noopener noreferrer">WooCommerce</a>'
	);
	echo '<div class="notice notice-error"><p>' . wp_kses_post( $message ) . '</p></div>';
}

/**
 * Global accessor function for the plugin instance.
 *
 * @return WC_Shipment_Tracking|null
 */
function wc_shipment_tracking() {
	if ( class_exists( 'WC_Shipment_Tracking' ) ) {
		return WC_Shipment_Tracking::instance();
	}
	return null;
}