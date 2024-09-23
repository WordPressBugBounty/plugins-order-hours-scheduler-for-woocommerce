/* global wc_cart_fragments_params */
jQuery( function( $ ) {

    // wc_cart_fragments_params is required to continue, ensure the object exists
    if ( typeof wc_cart_fragments_params === 'undefined' ) {
        return false;
    }

    var $fragment_refresh = {
        url: wc_cart_fragments_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'get_refreshed_fragments' ),
        type: 'POST',
        success: function( data ) {
            if ( data && data.fragments ) {

                $.each( data.fragments, function( key, value ) {
                    $( key ).replaceWith( value );
                });

                $( document.body ).trigger( 'wc_fragments_refreshed' );
            }
        }
    };

    /* Named callback for refreshing cart fragment */
    function refresh_cart_fragment() {
        $.ajax( $fragment_refresh );
    }

    /* Cart Handling */
    refresh_cart_fragment();

    /* Cart Hiding */
    if ( $.cookie( 'woocommerce_items_in_cart' ) > 0 ) {
        $( '.hide_cart_widget_if_empty' ).closest( '.widget_shopping_cart' ).show();
    } else {
        $( '.hide_cart_widget_if_empty' ).closest( '.widget_shopping_cart' ).hide();
    }

    $( document.body ).bind( 'adding_to_cart', function() {
        $( '.hide_cart_widget_if_empty' ).closest( '.widget_shopping_cart' ).show();
    });
});