# WooCommerce Shipment Tracking

[![WordPress Plugin](https://img.shields.io/badge/WordPress-5.6%2B-blue.svg)](https://wordpress.org)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-4.0.0%2B-purple.svg)](https://woocommerce.com)
[![HPOS Compatible](https://img.shields.io/badge/WooCommerce-HPOS%20Compatible-brightgreen.svg)](https://woocommerce.com)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)](https://php.net)
[![Version](https://img.shields.io/badge/Version-1.1.0-brightgreen.svg)](https://github.com/0tunguyen0/wc-shipment-tracking)
[![License](https://img.shields.io/badge/License-GPLv2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

**WooCommerce Shipment Tracking** is a lightweight, high-performance WordPress extension that allows store owners to attach order tracking numbers, carrier details, and shipment dates directly to customer orders. 

Automatically generate tracking links for major shipping providers (UPS, FedEx, USPS, DHL) or custom carriers, and display them in customer completed order emails and "My Account" order views.

---

## ⚡ Key Features

* 🚀 **Optimized & Lightweight Execution:** Isolated conditional loading for Admin, Frontend, and Email modules so admin AJAX scripts never slow down store frontend pages.
* 📦 **WooCommerce HPOS Compatible:** Native support for High-Performance Order Storage (Custom Order Tables) alongside traditional WordPress post meta.
* ⚡ **Instant AJAX Order Saving:** Attach and update tracking info directly from the order edit screen with single-click AJAX without reloads.
* 🔗 **Smart Auto-Carrier Links:** Automatically constructs official tracking URLs for top carriers if a manual URL is left blank, featuring live preview links in admin.
* ✉️ **Automated Email Notifications:** Appends responsive, clean tracking summaries and direct tracking action buttons to `Customer Completed Order` emails (HTML & Plain Text).
* 👤 **Customer Account Integration:** Displays clickable tracking info on `My Account > Orders > View Order` and the Checkout Order Received screen.
* 🚚 **Multi-Carrier & Custom Support:** Built-in templates for UPS, FedEx, USPS, and DHL, with seamless extensibility for local or custom parcel providers.
* 📊 **Shipping Cost Logging:** Record actual internal shipping costs per order for administrative auditing and margin calculations.
* 🔒 **Secure Standards:** Strict nonce checks, capability authorization (`edit_shop_orders`), input sanitization, and carrier validation.

---

## 📦 Supported Shipping Carriers

Out-of-the-box auto-formatting tracking link templates:

| Carrier | Provider Code | Auto-Tracking Link Pattern |
| :--- | :--- | :--- |
| **UPS** | `ups` | `https://www.ups.com/track?tracknum={number}` |
| **FedEx** | `fedex` | `https://www.fedex.com/fedextrack/?trknbr={number}` |
| **USPS** | `usps` | `https://tools.usps.com/go/TrackConfirmAction?tLabels={number}` |
| **DHL** | `dhl` | `https://www.dhl.com/en/express/tracking.html?AWB={number}` |
| **Custom Carrier** | `other` | Custom direct tracking URL supplied by store admin |

---

## 💻 Developer Hooks & Customization

### 1. Register Custom Carriers
Add new carriers to the admin carrier dropdown:

```php
add_filter( 'wc_shipment_tracking_providers', function( $providers ) {
    $providers['royal_mail'] = __( 'Royal Mail', 'my-theme' );
    $providers['auspost']    = __( 'Australia Post', 'my-theme' );
    $providers['dpd']        = __( 'DPD', 'my-theme' );
    return $providers;
} );
```

### 2. Define Custom URL Auto-Generation Templates

Map custom carrier keys to automated tracking link templates:

```php
add_filter( 'wc_shipment_tracking_carrier_url_templates', function( $templates ) {
    $templates['royal_mail'] = 'https://www.royalmail.com/track-your-item#/tracking-results/%s';
    return $templates;
} );
```

### 3. Expand Eligible WooCommerce Notification Emails

By default, tracking appends to `customer_completed_order`. Enable tracking details on additional transactional emails:

```php
add_filter( 'wc_shipment_tracking_eligible_emails', function( $emails ) {
    $emails[] = 'customer_invoice';
    return $emails;
} );
```

### 4. Toggle Account Order Details View

Enable or disable shipment display on the customer `View Order` / `Order Received` pages:

```php
add_filter( 'wc_shipment_tracking_show_in_order_details', '__return_false' );
```

### 5. Tracking Save Action Hook

Listen to tracking metadata updates to trigger webhooks, fulfillment APIs, or CRM updates:

```php
add_action( 'wc_shipment_tracking_saved', function( $order_id, $data ) {
    // $data contains: tracking_provider, tracking_number, tracking_link, date_shipped, shipping_cost
}, 10, 2 );
```

### 5. Template Overrides in Theme

Templates can be customized in your theme:
- HTML: `yourtheme/woocommerce/shipment-tracking/html/tracking-info.php`
- Plain: `yourtheme/woocommerce/shipment-tracking/plain/tracking-info.php`

---

## 🛠️ Installation & Setup

1. **Download / Clone**:
   Copy the plugin into your WordPress plugins directory:
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

Distributed under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).
