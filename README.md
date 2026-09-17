# WooCommerce Shipment Tracking

[![WordPress Plugin](https://img.shields.io/badge/WordPress-5.0+-blue.svg)](https://wordpress.org)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-3.0.0+-96588a.svg)](https://woocommerce.com)
[![HPOS Compatible](https://img.shields.io/badge/HPOS-Compatible-success.svg)](https://woocommerce.com)
[![PHP Version](https://img.shields.io/badge/PHP-7.2+-purple.svg)](https://php.net)
[![Version](https://img.shields.io/badge/Version-1.0.1-brightgreen.svg)](https://github.com/0tunguyen0/wc-shipment-tracking)
[![License](https://img.shields.io/badge/License-GPLv2+-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

**WooCommerce Shipment Tracking** is a lightweight, reliable extension for WooCommerce that allows store owners to attach shipment tracking information directly to customer orders. Customers receive their carrier details, tracking number, shipment date, and clickable tracking link right inside their order completion notification emails.

---

## 🚀 Key Highlights

* **HPOS Ready (High-Performance Order Storage)**: Fully declared and compatible with WooCommerce Custom Order Tables as well as traditional WordPress custom post types.
* **Instant AJAX Saving**: Save tracking information on the order edit screen with a single click via AJAX without having to update or reload the entire order.
* **Automated Customer Email Notifications**: Dynamically adds cleanly styled tracking summaries and direct tracking action buttons to the standard customer completed order emails (HTML & Plain Text).
* **Multi-Carrier & Custom Support**: Out of the box support for major parcel carriers (UPS, FedEx, USPS, DHL) and custom providers with direct tracking URLs.
* **Internal Shipping Cost Logging**: Record actual shipping costs incurred per order for internal administrative tracking.
* **Secure & Sanitized**: Strict nonce verification, capability authorization (`edit_shop_orders`), input sanitization, and carrier validation.

---

## 📦 Supported Shipping Carriers

The plugin comes pre-configured with top carriers and allows custom external links:

| Carrier | Code | Description |
| :--- | :--- | :--- |
| **UPS** | `ups` | United Parcel Service |
| **FedEx** | `fedex` | Federal Express |
| **USPS** | `usps` | United States Postal Service |
| **DHL** | `dhl` | DHL Express |
| **Other** | `other` | Any regional or custom carrier with custom link |

---

## 💻 Developer Hooks & Customization

### Adding Custom Carriers

You can register additional carriers or postal services using the `wc_shipment_tracking_providers` filter in your theme's `functions.php` or custom plugin:

```php
add_filter('wc_shipment_tracking_providers', function($providers) {
    $providers['royal_mail'] = __('Royal Mail', 'my-theme');
    $providers['auspost']    = __('Australia Post', 'my-theme');
    $providers['dpd']        = __('DPD', 'my-theme');
    return $providers;
});
```

---

## 🛠️ Installation & Setup

1. **Download / Clone**:
   Clone or copy the repository into your WordPress plugin directory:
   ```bash
   wp-content/plugins/wc-shipment-tracking
   ```
2. **Activate Plugin**:
   Navigate to **Plugins > Installed Plugins** in the WordPress Admin and activate **WooCommerce Shipment Tracking**.
3. **Usage**:
   - Open any order from **WooCommerce > Orders**.
   - Locate the **Shipment Tracking** meta box in the sidebar.
   - Select your shipping provider, enter the tracking number, direct tracking URL, and shipment date.
   - Click **Save Tracking** (saves instantly via AJAX).
   - When the order status changes to **Completed**, the tracking details are automatically included in the customer email.

---

## 📋 Requirements

* **WordPress**: 5.0 or higher
* **WooCommerce**: 3.0.0 or higher
* **PHP**: 7.2 or higher

---

## 📄 License

This plugin is licensed under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).
