<?php
/**
 * Plugin Name: WooCommerce Shipment Tracking
 * Description: Add shipment tracking information to your WooCommerce orders and display it in the order completion emails.
 * Version: 1.0.1
 * Author: Tu Nguyen
 * Text Domain: wc-shipment-tracking
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 3.0.0
 * WC tested up to: 8.0.0
 * Requires WooCommerce: 3.0.0
 *
 * This plugin is compatible with WooCommerce High-Performance Order Storage (HPOS)
 * @package WooCommerce
 * @category Shipping
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants (guarded to prevent fatal on double-include)
if (!defined('WC_SHIPMENT_TRACKING_VERSION')) {
    define('WC_SHIPMENT_TRACKING_VERSION', '1.0.1');
}
if (!defined('WC_SHIPMENT_TRACKING_PATH')) {
    define('WC_SHIPMENT_TRACKING_PATH', plugin_dir_path(__FILE__));
}
if (!defined('WC_SHIPMENT_TRACKING_URL')) {
    define('WC_SHIPMENT_TRACKING_URL', plugin_dir_url(__FILE__));
}

// Declare HPOS compatibility
add_action('before_woocommerce_init', function() {
    if (class_exists('\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

// Check if WooCommerce is active
function wc_shipment_tracking_is_woocommerce_active() {
    $active_plugins = (array) get_option('active_plugins', array());
    
    if (is_multisite()) {
        $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
    }
    
    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
}

// Main plugin class (singleton)
class WC_Shipment_Tracking {
    
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
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor — private to enforce singleton.
     */
    private function __construct() {
        // Check if WooCommerce is active
        if (!wc_shipment_tracking_is_woocommerce_active()) {
            add_action('admin_notices', array($this, 'woocommerce_not_active_notice'));
            return;
        }
        
        // Load plugin text domain
        add_action('init', array($this, 'load_plugin_textdomain'));
        
        // Admin hooks
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        
        // Add meta box to order edit page
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        
        // Save tracking info
        add_action('woocommerce_process_shop_order_meta', array($this, 'save_tracking_meta_box'), 10, 2);
        add_action('wp_ajax_wc_shipment_tracking_save', array($this, 'save_ajax_tracking_data'));
        
        // Add tracking info to emails
        add_action('woocommerce_email_after_order_table', array($this, 'add_tracking_info_to_emails'), 10, 4);
    }

    /**
     * Prevent cloning.
     */
    private function __clone() {}

    /**
     * Prevent unserialization.
     */
    public function __wakeup() {
        throw new \Exception('Cannot unserialize singleton');
    }

    /**
     * WooCommerce not active notice
     */
    public function woocommerce_not_active_notice() {
        $message = sprintf(
            /* translators: %s: WooCommerce download link */
            esc_html__('WooCommerce Shipment Tracking requires WooCommerce to be installed and active. You can download %s here.', 'wc-shipment-tracking'),
            '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>'
        );
        echo '<div class="error"><p>' . wp_kses_post($message) . '</p></div>';
    }
    
    /**
     * Load plugin text domain
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain('wc-shipment-tracking', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Enqueue admin scripts
     */
    public function admin_scripts($hook) {
        $screen = get_current_screen();
        
        // Determine if we're on an order edit screen (classic or HPOS)
        $is_order_screen = ($hook === 'post.php' && isset($screen->post_type) && $screen->post_type === 'shop_order');

        if (!$is_order_screen && function_exists('wc_get_page_screen_id')) {
            $is_order_screen = ($hook === wc_get_page_screen_id('shop-order'));
        }

        if (!$is_order_screen) {
            return;
        }

        wp_enqueue_style('wc-shipment-tracking-admin', WC_SHIPMENT_TRACKING_URL . 'assets/css/admin.css', array(), WC_SHIPMENT_TRACKING_VERSION);
        wp_enqueue_script('wc-shipment-tracking-admin', WC_SHIPMENT_TRACKING_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-datepicker'), WC_SHIPMENT_TRACKING_VERSION, true);
        
        // Get order ID - works with both classic and HPOS
        $order_id = isset($_GET['id']) ? absint($_GET['id']) : get_the_ID();
        
        wp_localize_script('wc-shipment-tracking-admin', 'wc_shipment_tracking_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'order_id' => $order_id,
            'nonce' => wp_create_nonce('wc-shipment-tracking'),
            'i18n' => array(
                'save_tracking' => __('Save Tracking', 'wc-shipment-tracking'),
                'saving' => __('Saving...', 'wc-shipment-tracking'),
                'saved' => __('Tracking information saved', 'wc-shipment-tracking'),
                'error' => __('Error saving tracking information', 'wc-shipment-tracking'),
            )
        ));
    }
    
    /**
     * Add meta box to order edit page.
     *
     * Registers for the correct screen only (classic post type or HPOS screen).
     */
    public function add_meta_box() {
        $screen = 'shop_order'; // default: classic CPT screen

        if (class_exists('\\Automattic\\WooCommerce\\Utilities\\OrderUtil') &&
            \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
            $screen = wc_get_page_screen_id('shop-order');
        }

        add_meta_box(
            'wc-shipment-tracking',
            __('Shipment Tracking', 'wc-shipment-tracking'),
            array($this, 'output_meta_box'),
            $screen,
            'side',
            'default'
        );
    }
    
    /**
     * Output the meta box content
     */
    public function output_meta_box($post_or_order_object) {
        // Get order object - works with both post objects and order objects
        $order = ( $post_or_order_object instanceof WC_Order ) ? $post_or_order_object : wc_get_order( $post_or_order_object->ID );
        
        if (!$order) {
            return;
        }
        
        // Get tracking data
        $tracking_provider = $order->get_meta('_tracking_provider', true);
        $tracking_number = $order->get_meta('_tracking_number', true);
        $tracking_link = $order->get_meta('_tracking_link', true);
        $date_shipped = $order->get_meta('_date_shipped', true);
        $shipping_cost = $order->get_meta('_shipping_cost', true);
        
        // Get shipping providers
        $providers = $this->get_shipping_providers();
        
        // Output meta box HTML
        include WC_SHIPMENT_TRACKING_PATH . 'includes/views/html-order-tracking-meta-box.php';
    }
    
    /**
     * Get shipping providers
     */
    public function get_shipping_providers() {
        return apply_filters('wc_shipment_tracking_providers', array(
            'ups' => __('UPS', 'wc-shipment-tracking'),
            'fedex' => __('FedEx', 'wc-shipment-tracking'),
            'usps' => __('USPS', 'wc-shipment-tracking'),
            'dhl' => __('DHL', 'wc-shipment-tracking'),
            'other' => __('Other', 'wc-shipment-tracking'),
        ));
    }
    
    /**
     * Save tracking meta box data
     */
    public function save_tracking_meta_box($post_id, $post) {
        // Check nonce
        if (!isset($_POST['wc_shipment_tracking_nonce']) || !wp_verify_nonce($_POST['wc_shipment_tracking_nonce'], 'wc_shipment_tracking_save')) {
            return;
        }
        
        // Capability check — matches the AJAX handler
        if (!current_user_can('edit_shop_orders')) {
            return;
        }
        
        // Save tracking data
        $this->save_tracking_data($post_id, $_POST);
    }
    
    /**
     * Save tracking data via AJAX
     */
    public function save_ajax_tracking_data() {
        check_ajax_referer('wc-shipment-tracking', 'nonce');
        
        if (!current_user_can('edit_shop_orders')) {
            wp_die(-1);
        }
        
        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        
        if ($order_id <= 0) {
            wp_die(-1);
        }
        
        $this->save_tracking_data($order_id, $_POST);
        
        wp_send_json_success(array(
            'message' => __('Tracking information saved', 'wc-shipment-tracking'),
        ));
    }
    
    /**
     * Save tracking data
     */
    private function save_tracking_data($order_id, $data) {
        $tracking_provider = isset($data['tracking_provider']) ? wc_clean($data['tracking_provider']) : '';
        $tracking_number   = isset($data['tracking_number']) ? wc_clean($data['tracking_number']) : '';
        $tracking_link     = isset($data['tracking_link']) ? esc_url_raw($data['tracking_link']) : '';
        $date_shipped      = isset($data['date_shipped']) ? wc_clean($data['date_shipped']) : '';
        $shipping_cost     = isset($data['shipping_cost']) ? wc_format_decimal($data['shipping_cost']) : '';

        // Validate tracking_provider against the known allowlist
        $valid_providers = array_keys($this->get_shipping_providers());
        if (!empty($tracking_provider) && !in_array($tracking_provider, $valid_providers, true)) {
            $tracking_provider = '';
        }
        
        // Get order object
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        // Save tracking data using WC order meta methods
        $order->update_meta_data('_tracking_provider', $tracking_provider);
        $order->update_meta_data('_tracking_number', $tracking_number);
        $order->update_meta_data('_tracking_link', $tracking_link);
        $order->update_meta_data('_date_shipped', $date_shipped);
        $order->update_meta_data('_shipping_cost', $shipping_cost);
        $order->save();
    }
    
    /**
     * Add tracking info to emails
     */
    public function add_tracking_info_to_emails($order, $sent_to_admin, $plain_text, $email) {
        // Only add to completed order emails
        if ($email->id !== 'customer_completed_order') {
            return;
        }
        
        // Get tracking data using WC order meta methods
        $tracking_provider = $order->get_meta('_tracking_provider', true);
        $tracking_number = $order->get_meta('_tracking_number', true);
        $tracking_link = $order->get_meta('_tracking_link', true);
        $date_shipped = $order->get_meta('_date_shipped', true);
        
        // If no tracking info, return
        if (empty($tracking_number)) {
            return;
        }
        
        // Get providers
        $providers = $this->get_shipping_providers();
        $provider_name = isset($providers[$tracking_provider]) ? $providers[$tracking_provider] : $tracking_provider;
        
        // Format date
        $date_shipped_formatted = !empty($date_shipped) ? date_i18n(get_option('date_format'), strtotime($date_shipped)) : '';
        
        // Output tracking info
        if ($plain_text) {
            include WC_SHIPMENT_TRACKING_PATH . 'includes/views/plain/tracking-info.php';
        } else {
            include WC_SHIPMENT_TRACKING_PATH . 'includes/views/html/tracking-info.php';
        }
    }
}

// Initialize the plugin (singleton)
function wc_shipment_tracking_init() {
    return WC_Shipment_Tracking::instance();
}
add_action('plugins_loaded', 'wc_shipment_tracking_init');