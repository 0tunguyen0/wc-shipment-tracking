/**
 * Admin JavaScript for WooCommerce Shipment Tracking
 */

/* global wc_shipment_tracking_params, jQuery */
jQuery( function( $ ) {
	'use strict';

	if ( typeof wc_shipment_tracking_params === 'undefined' ) {
		return;
	}

	var $wrapper = $( '.wc-shipment-tracking-wrapper' );
	if ( ! $wrapper.length ) {
		return;
	}

	// Initialize jQuery UI datepicker if available.
	if ( $.fn.datepicker ) {
		$wrapper.find( '.date-picker' ).datepicker( {
			dateFormat: 'yy-mm-dd',
			firstDay: 1,
			showAnim: 'fadeIn'
		} );
	}

	// Handle AJAX save tracking button.
	$wrapper.on( 'click', '.wc-shipment-tracking-save', function( e ) {
		e.preventDefault();

		var $button = $( this );
		var $status = $wrapper.find( '.wc-shipment-tracking-status' );

		if ( $button.prop( 'disabled' ) ) {
			return;
		}

		var trackingData = {
			action: 'wc_shipment_tracking_save',
			order_id: wc_shipment_tracking_params.order_id,
			nonce: wc_shipment_tracking_params.nonce,
			tracking_provider: $wrapper.find( '#tracking_provider' ).val(),
			tracking_number: $.trim( $wrapper.find( '#tracking_number' ).val() ),
			tracking_link: $.trim( $wrapper.find( '#tracking_link' ).val() ),
			date_shipped: $.trim( $wrapper.find( '#date_shipped' ).val() ),
			shipping_cost: $.trim( $wrapper.find( '#shipping_cost' ).val() )
		};

		// If order_id was 0 (e.g. newly created post), try to get from hidden form fields.
		if ( ! trackingData.order_id || trackingData.order_id === '0' ) {
			var postInput = $( '#post_ID' );
			if ( postInput.length && postInput.val() ) {
				trackingData.order_id = parseInt( postInput.val(), 10 );
			}
		}

		$button.prop( 'disabled', true );
		$status
			.removeClass( 'success error' )
			.addClass( 'saving' )
			.text( wc_shipment_tracking_params.i18n.saving );

		$.ajax( {
			url: wc_shipment_tracking_params.ajax_url,
			type: 'POST',
			dataType: 'json',
			data: trackingData
		} )
		.done( function( response ) {
			if ( response && response.success ) {
				var msg = ( response.data && response.data.message ) ? response.data.message : wc_shipment_tracking_params.i18n.saved;
				$status
					.removeClass( 'saving error' )
					.addClass( 'success' )
					.text( msg );

				// Update or add test tracking link if provided in response.
				if ( response.data && response.data.tracking_data && response.data.tracking_data.tracking_link ) {
					var liveUrl = response.data.tracking_data.tracking_link;
					var $liveLink = $wrapper.find( '.wc-shipment-tracking-live-link' );

					if ( $liveLink.length ) {
						$liveLink.find( 'a' ).attr( 'href', liveUrl );
					} else {
						$wrapper.find( '#tracking_link' ).siblings( '.description' ).after(
							'<span class="wc-shipment-tracking-live-link"><a href="' + liveUrl + '" target="_blank" rel="noopener noreferrer">Test current tracking link &rarr;</a></span>'
						);
					}
				}

				setTimeout( function() {
					$status.fadeOut( 300, function() {
						$( this ).removeClass( 'success' ).text( '' ).show();
					} );
				}, 3500 );
			} else {
				var errMsg = ( response && response.data && response.data.message ) ? response.data.message : wc_shipment_tracking_params.i18n.error;
				$status
					.removeClass( 'saving success' )
					.addClass( 'error' )
					.text( errMsg );
			}
		} )
		.fail( function() {
			$status
				.removeClass( 'saving success' )
				.addClass( 'error' )
				.text( wc_shipment_tracking_params.i18n.error );
		} )
		.always( function() {
			$button.prop( 'disabled', false );
		} );
	} );
} );