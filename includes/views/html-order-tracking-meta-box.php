<?php
/**
 * Admin View: Order Tracking Meta Box
 *
 * @package WooCommerceShipmentTracking
 *
 * @var array  $providers
 * @var string $tracking_provider
 * @var string $tracking_number
 * @var string $tracking_link
 * @var string $date_shipped
 * @var string $shipping_cost
 * @var string $effective_link
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wc-shipment-tracking-wrapper">
	<p class="form-field">
		<label for="tracking_provider"><?php esc_html_e( 'Shipping Provider:', 'wc-shipment-tracking' ); ?></label>
		<select id="tracking_provider" name="tracking_provider" class="select widefat">
			<option value=""><?php esc_html_e( 'Select a provider', 'wc-shipment-tracking' ); ?></option>
			<?php foreach ( $providers as $provider_code => $provider_name ) : ?>
				<option value="<?php echo esc_attr( $provider_code ); ?>" <?php selected( $tracking_provider, $provider_code ); ?>>
					<?php echo esc_html( $provider_name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>

	<p class="form-field">
		<label for="tracking_number"><?php esc_html_e( 'Tracking Number:', 'wc-shipment-tracking' ); ?></label>
		<input type="text" id="tracking_number" name="tracking_number" class="widefat" value="<?php echo esc_attr( $tracking_number ); ?>" placeholder="<?php esc_attr_e( 'e.g. 1Z9999999999999999', 'wc-shipment-tracking' ); ?>" autocomplete="off" />
	</p>

	<p class="form-field">
		<label for="tracking_link"><?php esc_html_e( 'Tracking Link (Optional):', 'wc-shipment-tracking' ); ?></label>
		<input type="url" id="tracking_link" name="tracking_link" class="widefat" value="<?php echo esc_url( $tracking_link ); ?>" placeholder="https://" />
		<span class="description"><?php esc_html_e( 'Auto-generated for standard carriers if left empty.', 'wc-shipment-tracking' ); ?></span>
		<?php if ( ! empty( $effective_link ) ) : ?>
			<span class="wc-shipment-tracking-live-link">
				<a href="<?php echo esc_url( $effective_link ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Test current tracking link &rarr;', 'wc-shipment-tracking' ); ?>
				</a>
			</span>
		<?php endif; ?>
	</p>

	<p class="form-field">
		<label for="date_shipped"><?php esc_html_e( 'Date Shipped:', 'wc-shipment-tracking' ); ?></label>
		<input type="text" class="date-picker widefat" id="date_shipped" name="date_shipped" value="<?php echo esc_attr( $date_shipped ); ?>" placeholder="YYYY-MM-DD" maxlength="10" />
	</p>

	<p class="form-field">
		<label for="shipping_cost"><?php esc_html_e( 'Shipping Cost (Optional):', 'wc-shipment-tracking' ); ?></label>
		<input type="number" step="0.01" min="0" id="shipping_cost" name="shipping_cost" class="widefat" value="<?php echo esc_attr( $shipping_cost ); ?>" placeholder="0.00" />
		<span class="description"><?php esc_html_e( 'Internal record only — not displayed to customer.', 'wc-shipment-tracking' ); ?></span>
	</p>

	<?php wp_nonce_field( WC_Shipment_Tracking_Admin::NONCE_ACTION, WC_Shipment_Tracking_Admin::NONCE_NAME ); ?>

	<div class="wc-shipment-tracking-actions">
		<button type="button" class="button button-primary wc-shipment-tracking-save"><?php esc_html_e( 'Save Tracking', 'wc-shipment-tracking' ); ?></button>
		<span class="wc-shipment-tracking-status" aria-live="polite"></span>
	</div>
</div>