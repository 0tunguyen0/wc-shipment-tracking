<?php
/**
 * Email Template: Tracking Info (HTML)
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<h2><?php esc_html_e('Shipment Tracking Information', 'wc-shipment-tracking'); ?></h2>

<div style="margin-bottom: 40px;">
    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; margin-bottom: 20px; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <tbody>
            <tr>
                <th class="td" scope="row" style="text-align: left;"><?php esc_html_e('Shipping Provider', 'wc-shipment-tracking'); ?>:</th>
                <td class="td" style="text-align: left;"><?php echo esc_html($provider_name); ?></td>
            </tr>
            <tr>
                <th class="td" scope="row" style="text-align: left;"><?php esc_html_e('Tracking Number', 'wc-shipment-tracking'); ?>:</th>
                <td class="td" style="text-align: left;">
                    <?php if (!empty($tracking_link)) : ?>
                        <a href="<?php echo esc_url($tracking_link); ?>" target="_blank"><?php echo esc_html($tracking_number); ?></a>
                    <?php else : ?>
                        <?php echo esc_html($tracking_number); ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if (!empty($date_shipped_formatted)) : ?>
            <tr>
                <th class="td" scope="row" style="text-align: left;"><?php esc_html_e('Date Shipped', 'wc-shipment-tracking'); ?>:</th>
                <td class="td" style="text-align: left;"><?php echo esc_html($date_shipped_formatted); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if (!empty($tracking_link)) : ?>
    <p>
        <a href="<?php echo esc_url($tracking_link); ?>" class="button button-primary" target="_blank"><?php esc_html_e('Track Your Shipment', 'wc-shipment-tracking'); ?></a>
    </p>
    <?php endif; ?>
</div>