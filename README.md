# WooCommerce Shipment Tracking

[![WordPress Plugin](https://img.shields.io/badge/WordPress-5.6+-blue.svg)](https://wordpress.org)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-4.0.0+-96588a.svg)](https://woocommerce.com)
[![HPOS Compatible](https://img.shields.io/badge/HPOS-Compatible-success.svg)](https://woocommerce.com)
[![PHP Version](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net)
[![Version](https://img.shields.io/badge/Version-1.1.0-brightgreen.svg)](https://github.com/0tunguyen0/wc-shipment-tracking)
[![License](https://img.shields.io/badge/License-GPLv2+-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

**WooCommerce Shipment Tracking** is a lightweight, highly-optimized extension for WooCommerce that allows store owners to attach shipment tracking information directly to customer orders. Customers receive their carrier details, tracking number, shipment date, and clickable tracking link right inside their order completion notification emails and on their customer account order view pages.

---

## 🚀 Key Highlights

* **Optimized Conditional Loading**: Separate, isolated modules for Admin, Frontend, and Emails. Admin assets and AJAX handlers never execute or burden frontend page loads.
* **HPOS Ready (High-Performance Order Storage)**: Declared and fully compatible with WooCommerce Custom Order Tables as well as traditional WordPress post types.
* **Instant AJAX Saving**: Save tracking information on the order edit screen with a single click via AJAX without having to reload the entire order.
* **Smart Auto-Carrier Links**: Automatically generates tracking links for major carriers (UPS, FedEx, USPS, DHL) if a manual link is left blank, with an instant test link preview in admin.
* **Automated Customer Email Notifications**: Dynamically adds cleanly styled tracking summaries and direct tracking action buttons to standard customer completed order emails (HTML & Plain Text), with theme override support (`wc_get_template`).
* **Customer Account Integration**: Displays shipment tracking details on customer order summary pages (`My Account > Orders > View Order` and Order Received page).
* **Multi-Carrier & Custom Support**: Out-of-the-box support for major parcel carriers (UPS, FedEx, USPS, DHL) and custom providers with direct tracking URLs.
* **Internal Shipping Cost Logging**: Record actual shipping costs incurred per order for internal administrative tracking.
* **Secure & Standardized**: Strict nonce verification, capability authorization (`edit_shop_orders`), input sanitization, and carrier validation.

---

## 📦 Supported Shipping Carriers

The plugin comes pre-configured with top carriers and auto-formats tracking links:

| Carrier | Code | Auto-Tracking URL Pattern |
| :--- | :--- | :--- |
| **UPS** | `ups` | `https://www.ups.com/track?tracknum={number}` |
| **FedEx** | `fedex` | `https://www.fedex.com/fedextrack/?trknbr={number}` |
| **USPS** | `usps` | `https://tools.usps.com/go/TrackConfirmAction?tLabels={number}` |
| **DHL** | `dhl` | `https://www.dhl.com/en/express/tracking.html?AWB={number}` |
| **Other** | `other` | Custom direct tracking URL provided by store admin |

---

## 💻 Developer Hooks & Customization

### Adding Custom Carriers

Register additional carriers using the `wc_shipment_tracking_providers` filter:

```php
add_filter( 'wc_shipment_tracking_providers', function( $providers ) {
    $providers['royal_mail'] = __( 'Royal Mail', 'my-theme' );
    $providers['auspost']    = __( 'Australia Post', 'my-theme' );
    $providers['dpd']        = __( 'DPD', 'my-theme' );
    return $providers;
} );
```

### Custom Carrier URL Templates

Register URL auto-generation templates for custom carriers:

```php
add_filter( 'wc_shipment_tracking_carrier_url_templates', function( $templates ) {
    $templates['royal_mail'] = 'https://www.royalmail.com/track-your-item#/tracking-results/%s';
    return $templates;
} );
```

### Changing Eligible Emails

By default, tracking is appended to `customer_completed_order`. You can add more email types:

```php
add_filter( 'wc_shipment_tracking_eligible_emails', function( $emails ) {
    $emails[] = 'customer_invoice';
    return $emails;
} );
```

### Toggle Frontend Order Details Display

Enable or disable tracking on customer `View Order` / `Order Received` pages:

```php
add_filter( 'wc_shipment_tracking_show_in_order_details', '__return_false' );
```

### Action Hook on Tracking Saved

Listen to tracking updates to trigger custom webhooks or third-party syncs:

```php
add_action( 'wc_shipment_tracking_saved', function( $order_id, $data ) {
    // $data contains: tracking_provider, tracking_number, tracking_link, date_shipped, shipping_cost
}, 10, 2 );
```

### Overriding Templates in Your Theme

Templates can be customized in your theme:
- HTML: `yourtheme/woocommerce/shipment-tracking/html/tracking-info.php`
- Plain: `yourtheme/woocommerce/shipment-tracking/plain/tracking-info.php`

---

## 🛠️ Installation & Setup

1. **Download / Clone**:
   Copy into your WordPress plugins directory:
   ```bash
   wp-content/plugins/wc-shipment-tracking
   ```
2. **Activate Plugin**:
   Navigate to **Plugins > Installed Plugins** in the WordPress Admin and activate **WooCommerce Shipment Tracking**.
3. **Usage**:
   - Open any order from **WooCommerce > Orders**.
   - Locate the **Shipment Tracking** meta box in the sidebar.
   - Select your shipping provider, enter the tracking number, optional direct URL, and shipment date.
   - Click **Save Tracking** (saves instantly via AJAX).
   - The tracking details are automatically included in the customer completed order email and on the customer order view screen.

---

## 📋 Requirements

* **WordPress**: 5.6 or higher
* **WooCommerce**: 4.0.0 or higher
* **PHP**: 7.4 or higher

---

## 📄 License

This plugin is licensed under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).
