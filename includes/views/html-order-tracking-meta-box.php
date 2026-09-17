<?php
/**
 * Admin View: Order Tracking Meta Box
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wc-shipment-tracking-wrapper">
    <p>
        <label for="tracking_provider"><?php esc_html_e('Shipping Provider:', 'wc-shipment-tracking'); ?></label>
        <select id="tracking_provider" name="tracking_provider" class="select">
            <option value=""><?php esc_html_e('Select a provider', 'wc-shipment-tracking'); ?></option>
            <?php foreach ($providers as $provider_code => $provider_name) : ?>
                <option value="<?php echo esc_attr($provider_code); ?>" <?php selected($tracking_provider, $provider_code); ?>>
                    <?php echo esc_html($provider_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    
    <p>
        <label for="tracking_number"><?php esc_html_e('Tracking Number:', 'wc-shipment-tracking'); ?></label>
        <input type="text" id="tracking_number" name="tracking_number" value="<?php echo esc_attr($tracking_number); ?>" />
    </p>
    
    <p>
        <label for="tracking_link"><?php esc_html_e('Tracking Link:', 'wc-shipment-tracking'); ?></label>
        <input type="url" id="tracking_link" name="tracking_link" value="<?php echo esc_url($tracking_link); ?>" placeholder="https://" />
    </p>
    
    <p>
        <label for="date_shipped"><?php esc_html_e('Date Shipped:', 'wc-shipment-tracking'); ?></label>
        <input type="text" class="date-picker" id="date_shipped" name="date_shipped" value="<?php echo esc_attr($date_shipped); ?>" placeholder="<?php echo esc_attr(date_i18n(get_option('date_format'))); ?>" />
    </p>
    
    <p>
        <label for="shipping_cost"><?php esc_html_e('Shipping Cost (Optional):', 'wc-shipment-tracking'); ?></label>
        <input type="number" step="0.01" min="0" id="shipping_cost" name="shipping_cost" value="<?php echo esc_attr($shipping_cost); ?>" placeholder="0.00" />
        <span class="description"><?php esc_html_e('For internal use only', 'wc-shipment-tracking'); ?></span>
    </p>
    
    <?php wp_nonce_field('wc_shipment_tracking_save', 'wc_shipment_tracking_nonce'); ?>
    
    <p>
        <button type="button" class="button button-primary wc-shipment-tracking-save"><?php esc_html_e('Save Tracking', 'wc-shipment-tracking'); ?></button>
        <span class="wc-shipment-tracking-status"></span>
    </p>
</div>