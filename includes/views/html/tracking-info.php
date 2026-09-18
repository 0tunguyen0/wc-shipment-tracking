<?php
/**
 * Template: Tracking Info (HTML)
 *
 * Used for customer completed order notification emails and customer order details page.
 *
 * @package WooCommerceShipmentTracking
 *
 * @var \WC_Order $order
 * @var string    $provider_name
 * @var string    $tracking_provider
 * @var string    $tracking_number
 * @var string    $tracking_link
 * @var string    $date_shipped_formatted
 * @var array     $tracking_data
 * @var bool      $is_frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_frontend_view = ! empty( $is_frontend );
?>
<div class="woocommerce-shipment-tracking-info" style="margin-bottom: 40px;">
	<h2><?php esc_html_e( 'Shipment Tracking Information', 'wc-shipment-tracking' ); ?></h2>

	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; margin-bottom: 20px; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border-collapse: collapse;" border="1">
		<tbody>
			<?php if ( ! empty( $provider_name ) ) : ?>
			<tr>
				<th class="td" scope="row" style="text-align: left; width: 35%;"><?php esc_html_e( 'Shipping Provider', 'wc-shipment-tracking' ); ?>:</th>
				<td class="td" style="text-align: left;"><?php echo esc_html( $provider_name ); ?></td>
			</tr>
			<?php endif; ?>

			<?php if ( ! empty( $tracking_number ) ) : ?>
			<tr>
				<th class="td" scope="row" style="text-align: left; width: 35%;"><?php esc_html_e( 'Tracking Number', 'wc-shipment-tracking' ); ?>:</th>
				<td class="td" style="text-align: left;">
					<?php if ( ! empty( $tracking_link ) ) : ?>
						<a href="<?php echo esc_url( $tracking_link ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( $tracking_number ); ?>
						</a>
					<?php else : ?>
						<?php echo esc_html( $tracking_number ); ?>
					<?php endif; ?>
				</td>
			</tr>
			<?php endif; ?>

			<?php if ( ! empty( $date_shipped_formatted ) ) : ?>
			<tr>
				<th class="td" scope="row" style="text-align: left; width: 35%;"><?php esc_html_e( 'Date Shipped', 'wc-shipment-tracking' ); ?>:</th>
				<td class="td" style="text-align: left;"><?php echo esc_html( $date_shipped_formatted ); ?></td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<?php if ( ! empty( $tracking_link ) ) : ?>
		<p style="margin-top: 15px;">
			<a href="<?php echo esc_url( $tracking_link ); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer" style="display: inline-block; padding: 10px 18px; text-decoration: none; border-radius: 4px;">
				<?php esc_html_e( 'Track Your Shipment &rarr;', 'wc-shipment-tracking' ); ?>
			</a>
		</p>
	<?php endif; ?>
</div>