jQuery(function($) {
    'use strict';
    
    // Initialize datepicker
    $('.date-picker').datepicker({
        dateFormat: 'yy-mm-dd',
        dayNamesMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
        monthNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
        firstDay: 1
    });
    
    // Save tracking information
    $('.wc-shipment-tracking-save').on('click', function() {
        var $button = $(this);
        var $status = $('.wc-shipment-tracking-status');
        var $form = $button.closest('.wc-shipment-tracking-wrapper');
        
        // Get form data
        var data = {
            action: 'wc_shipment_tracking_save',
            order_id: wc_shipment_tracking_params.order_id,
            nonce: wc_shipment_tracking_params.nonce,
            tracking_provider: $form.find('#tracking_provider').val(),
            tracking_number: $form.find('#tracking_number').val(),
            tracking_link: $form.find('#tracking_link').val(),
            date_shipped: $form.find('#date_shipped').val(),
            shipping_cost: $form.find('#shipping_cost').val()
        };
        
        // Disable button and show saving message
        $button.prop('disabled', true);
        $status.removeClass('success error').text(wc_shipment_tracking_params.i18n.saving);
        
        // Send AJAX request
        $.ajax({
            url: wc_shipment_tracking_params.ajax_url,
            data: data,
            type: 'POST',
            success: function(response) {
                if (response.success) {
                    $status.addClass('success').text(wc_shipment_tracking_params.i18n.saved);
                    
                    // Hide success message after 3 seconds
                    setTimeout(function() {
                        $status.removeClass('success').text('');
                    }, 3000);
                } else {
                    $status.addClass('error').text(wc_shipment_tracking_params.i18n.error);
                }
            },
            error: function() {
                $status.addClass('error').text(wc_shipment_tracking_params.i18n.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
});